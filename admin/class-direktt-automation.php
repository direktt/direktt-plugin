<?php

defined('ABSPATH') || exit;

class Direktt_Automation
{
	public static function run_and_queue($automation_key, $subscription_id, $msg_obj, $action, $time_in_seconds, $initial_state = null)
	{
		$runs  = new Direktt_Automation_RunRepository();
		$queue = new Direktt_Automation_QueueRepository();

		$run_id = $runs->create($automation_key, $subscription_id, $msg_obj, $initial_state);

		// Schedule after 5 seconds.
		$queue->enqueue($run_id, $subscription_id, $action, $msg_obj, time() + (int) $time_in_seconds, 0);

		return $run_id;
	}
}

class Direktt_Automation_DB
{
	public static function table_runs()
	{
		global $wpdb;
		return $wpdb->prefix . 'direktt_auto_runs';
	}

	public static function table_queue()
	{
		global $wpdb;
		return $wpdb->prefix . 'direktt_auto_queue';
	}

	public static function table_messages()
	{
		global $wpdb;
		return $wpdb->prefix . 'direktt_auto_messages_log';
	}

	public static function install()
	{
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$charset_collate = $wpdb->get_charset_collate();

		$runs = "CREATE TABLE " . self::table_runs() . " (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                automation_key VARCHAR(64) NOT NULL,
                direktt_user_id varchar(256) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
                current_step VARCHAR(64) NULL,
                status ENUM('active','paused','completed','canceled') NOT NULL DEFAULT 'active',
                state LONGTEXT NULL,
                started_at DATETIME NOT NULL,
                last_step_at DATETIME NULL,
                updated_at DATETIME NOT NULL,
                PRIMARY KEY (id),
                KEY contact_status (direktt_user_id, status),
                KEY automation_status (automation_key, status),
                KEY updated_at (updated_at)
            ) $charset_collate;";

		$queue = "CREATE TABLE " . self::table_queue() . " (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                run_id BIGINT UNSIGNED NOT NULL,
                direktt_user_id varchar(256) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
                action_type VARCHAR(64) NOT NULL,
                payload LONGTEXT NULL,
                scheduled_at DATETIME NOT NULL,
                priority TINYINT NOT NULL DEFAULT 0,
                status ENUM('pending','locked','done','failed') NOT NULL DEFAULT 'pending',
                attempts INT NOT NULL DEFAULT 0,
                locked_at DATETIME NULL,
                worker_id VARCHAR(64) NULL,
                error_message TEXT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                PRIMARY KEY (id),
                KEY status_sched (status, scheduled_at, priority),
                KEY run_idx (run_id),
                KEY contact_status (direktt_user_id, status)
            ) $charset_collate;";

		$messages = "CREATE TABLE " . self::table_messages() . " (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                run_id BIGINT UNSIGNED NOT NULL,
                direktt_user_id varchar(256) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
                step_id VARCHAR(64) NULL,
                channel VARCHAR(32) NOT NULL DEFAULT 'direktt_message',
                template_id VARCHAR(128) NULL,
                provider_message_id VARCHAR(191) NULL,
                status ENUM('queued','sent','delivered','bounced','failed') NOT NULL DEFAULT 'queued',
                scheduled_at DATETIME NULL,
                sent_at DATETIME NULL,
                error_message TEXT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                PRIMARY KEY (id),
                KEY contact_status (direktt_user_id, status, sent_at),
                KEY provider_idx (provider_message_id)
            ) $charset_collate;";

		dbDelta($runs);
		dbDelta($queue);
		dbDelta($messages);
	}
}

class Direktt_Automation_Time
{
	public static function now_utc()
	{
		return gmdate('Y-m-d H:i:s');
	}

	public static function ts_to_mysql($ts)
	{
		return gmdate('Y-m-d H:i:s', (int) $ts);
	}
}

class Direktt_Automation_RunRepository
{
	public function create($automation_key, $direktt_user_id, array $state = [], $current_step = null)
	{
		global $wpdb;

		$table = Direktt_Automation_DB::table_runs();
		$now   = Direktt_Automation_Time::now_utc();

		$data = [
			'automation_key' => $automation_key,
			'direktt_user_id'     => $direktt_user_id,
			'current_step'   => $current_step,
			'status'         => 'active',
			'state'          => wp_json_encode($state),
			'started_at'     => $now,
			'updated_at'     => $now,
		];

		$formats = ['%s', '%s', '%s', '%s', '%s', '%s', '%s'];

		$wpdb->insert($table, $data, $formats);
		return (int) $wpdb->insert_id;
	}

	public function get($id)
	{
		global $wpdb;
		$table = Direktt_Automation_DB::table_runs();

		$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id), ARRAY_A);
		if ($row && !empty($row['state'])) {
			$row['state'] = json_decode($row['state'], true);
		}
		return $row;
	}

	public function update_state($id, array $state)
	{
		global $wpdb;
		$table = Direktt_Automation_DB::table_runs();

		return (bool) $wpdb->update(
			$table,
			[
				'state'      => wp_json_encode($state),
				'updated_at' => Direktt_Automation_Time::now_utc(),
			],
			['id' => (int) $id],
			['%s', '%s'],
			['%d']
		);
	}

	public function set_step($id, $step, $touch_last_step = true)
	{
		global $wpdb;
		$table = Direktt_Automation_DB::table_runs();

		$data = [
			'current_step' => $step,
			'updated_at'   => Direktt_Automation_Time::now_utc(),
		];

		$formats = ['%s', '%s'];

		if ($touch_last_step) {
			$data['last_step_at'] = Direktt_Automation_Time::now_utc();
			$formats[]            = '%s';
		}

		return (bool) $wpdb->update($table, $data, ['id' => (int) $id], $formats, ['%d']);
	}

	public function set_status($id, $status)
	{
		global $wpdb;
		$table = Direktt_Automation_DB::table_runs();

		return (bool) $wpdb->update(
			$table,
			[
				'status'     => $status,
				'updated_at' => Direktt_Automation_Time::now_utc(),
			],
			['id' => (int) $id],
			['%s', '%s'],
			['%d']
		);
	}

	public function set_step_if_current($id, $expected_step, $new_step, $touch_last_step = true)
	{
		global $wpdb;
		$table = Direktt_Automation_DB::table_runs();
		$now   = Direktt_Automation_Time::now_utc();

		if ($touch_last_step) {
			$sql = $wpdb->prepare(
				"UPDATE $table
                 SET current_step = %s,
                     last_step_at = %s,
                     updated_at = %s
                 WHERE id = %d
                   AND (current_step <=> %s)",
				$new_step,
				$now,
				$now,
				(int)$id,
				$expected_step
			);
		} else {
			$sql = $wpdb->prepare(
				"UPDATE $table
                 SET current_step = %s,
                     updated_at = %s
                 WHERE id = %d
                   AND (current_step <=> %s)",
				$new_step,
				$now,
				(int)$id,
				$expected_step
			);
		}

		$rows = $wpdb->query($sql);
		return $rows === 1; // true if we actually advanced
	}
}

class Direktt_Automation_QueueRepository
{

	const AS_GROUP   = 'direktt_automation';

	protected function as_available()
	{
		return function_exists('as_schedule_single_action');
	}

	public function enqueue($run_id, $direktt_user_id, $action_type, array $payload, $scheduled_ts, $priority = 0)
	{
		global $wpdb;

		$table        = Direktt_Automation_DB::table_queue();
		$now          = Direktt_Automation_Time::now_utc();
		$scheduled_at = is_numeric($scheduled_ts) ? Direktt_Automation_Time::ts_to_mysql($scheduled_ts) : $scheduled_ts;

		$wpdb->insert(
			$table,
			[
				'run_id'       => (int) $run_id,
				'direktt_user_id'   => $direktt_user_id,
				'action_type'  => $action_type,
				'payload'      => wp_json_encode($payload),
				'scheduled_at' => $scheduled_at,
				'priority'     => (int) $priority,
				'status'       => 'pending',
				'attempts'     => 0,
				'locked_at'    => null,
				'worker_id'    => null,
				'error_message' => null,
				'created_at'   => $now,
				'updated_at'   => $now,
			],
			['%d', '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%s', '%s', '%s', '%s', '%s']
		);

		$queue_id = (int) $wpdb->insert_id;

		// Schedule processing with Action Scheduler or WP-Cron fallback
		$this->schedule_processing($queue_id, strtotime($scheduled_at));

		return $queue_id;
	}

	protected function schedule_processing($queue_id, $timestamp)
	{
		if ($this->as_available()) {
			as_schedule_single_action($timestamp, 'direktt_automation_process_queue_item', ['queue_id' => $queue_id], Direktt_Automation_QueueRepository::AS_GROUP);
		} else {
			wp_schedule_single_event($timestamp, 'direktt_automation_fallback_process_queue_item', ['queue_id' => $queue_id]);
		}
	}

	public function get($id)
	{
		global $wpdb;
		$table = Direktt_Automation_DB::table_queue();

		$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id), ARRAY_A);
		if ($row && !empty($row['payload'])) {
			$row['payload'] = json_decode($row['payload'], true);
		}
		return $row;
	}

	// Attempt to claim a row for processing. Returns the claimed row or false.
	public function claim($id)
	{
		global $wpdb;
		$table     = Direktt_Automation_DB::table_queue();
		$worker_id = substr(uniqid('w_', true), 0, 63);

		// Lock only if pending and due
		$updated = $wpdb->query($wpdb->prepare(
			"UPDATE $table
                 SET status = 'locked',
                     locked_at = %s,
                     worker_id = %s,
                     attempts = attempts + 1,
                     updated_at = %s
                 WHERE id = %d
                   AND status = 'pending'
                   AND scheduled_at <= %s",
			Direktt_Automation_Time::now_utc(),
			$worker_id,
			Direktt_Automation_Time::now_utc(),
			(int) $id,
			Direktt_Automation_Time::now_utc()
		));

		if ($updated === 1) {
			$row = $this->get($id);
			if ($row && $row['worker_id'] === $worker_id && $row['status'] === 'locked') {
				return $row;
			}
		}
		return false;
	}

	public function mark_done($id)
	{
		global $wpdb;
		$table = Direktt_Automation_DB::table_queue();

		return (bool) $wpdb->update(
			$table,
			[
				'status'     => 'done',
				'updated_at' => Direktt_Automation_Time::now_utc(),
			],
			['id' => (int) $id],
			['%s', '%s'],
			['%d']
		);
	}

	public function mark_failed($id, $error_message)
	{
		global $wpdb;
		$table = Direktt_Automation_DB::table_queue();

		return (bool) $wpdb->update(
			$table,
			[
				'status'        => 'failed',
				'error_message' => $error_message,
				'updated_at'    => Direktt_Automation_Time::now_utc(),
			],
			['id' => (int) $id],
			['%s', '%s', '%s'],
			['%d']
		);
	}

	public function retry_later($id, $delay_seconds = 60, $max_attempts = 5)
	{
		global $wpdb;
		$table = Direktt_Automation_DB::table_queue();

		$row = $this->get($id);
		if (!$row) {
			return false;
		}

		if ((int) $row['attempts'] >= $max_attempts) {
			return $this->mark_failed($id, 'Max attempts exceeded');
		}

		$scheduled_ts = time() + (int) $delay_seconds;
		$scheduled_at = Direktt_Automation_Time::ts_to_mysql($scheduled_ts);

		$ok = (bool) $wpdb->update(
			$table,
			[
				'status'       => 'pending',
				'locked_at'    => null,
				'worker_id'    => null,
				'updated_at'   => Direktt_Automation_Time::now_utc(),
				'scheduled_at' => $scheduled_at,
			],
			['id' => (int) $id],
			['%s', '%s', '%s', '%s', '%s'],
			['%d']
		);

		if ($ok) {
			$this->schedule_processing($id, $scheduled_ts);
		}

		return $ok;
	}
}

class Direktt_Automation_MessagesLogRepository
{
	public function log_queued($run_id, $direktt_user_id, $step_id, $channel = 'direktt_message', $template_id = null, $scheduled_at = null)
	{
		global $wpdb;
		$table = Direktt_Automation_DB::table_messages();
		$now   = Direktt_Automation_Time::now_utc();

		$wpdb->insert(
			$table,
			[
				'run_id'              => (int) $run_id,
				'direktt_user_id'          => $direktt_user_id,
				'step_id'             => $step_id,
				'channel'             => $channel,
				'template_id'         => $template_id,
				'provider_message_id' => null,
				'status'              => 'queued',
				'scheduled_at'        => $scheduled_at,
				'sent_at'             => null,
				'error_message'       => null,
				'created_at'          => $now,
				'updated_at'          => $now,
			],
			['%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
		);

		return (int) $wpdb->insert_id;
	}

	public function mark_sent($id, $provider_message_id = null)
	{
		global $wpdb;
		$table = Direktt_Automation_DB::table_messages();

		return (bool) $wpdb->update(
			$table,
			[
				'status'              => 'sent',
				'provider_message_id' => $provider_message_id,
				'sent_at'             => Direktt_Automation_Time::now_utc(),
				'updated_at'          => Direktt_Automation_Time::now_utc(),
			],
			['id' => (int) $id],
			['%s', '%s', '%s', '%s'],
			['%d']
		);
	}

	public function mark_failed($id, $error_message)
	{
		global $wpdb;
		$table = Direktt_Automation_DB::table_messages();

		return (bool) $wpdb->update(
			$table,
			[
				'status'        => 'failed',
				'error_message' => $error_message,
				'updated_at'    => Direktt_Automation_Time::now_utc(),
			],
			['id' => (int) $id],
			['%s', '%s', '%s'],
			['%d']
		);
	}
}

class Direktt_Automation_ProcessorRegistry
{
	private static $map = [];

	public static function register($action_type, callable $processor)
	{
		self::$map[$action_type] = $processor;
	}

	public static function get($action_type)
	{
		return isset(self::$map[$action_type]) ? self::$map[$action_type] : null;
	}
}

class Direktt_Automation_Worker
{
	public static function process_queue_item($args)
	{
		// AS passes args as parameters; WP-Cron passes as the first param too.
		// Expect $args to be either ['queue_id' => X] or just queue_id.
		$queue_id = is_array($args) && isset($args['queue_id']) ? (int) $args['queue_id'] : (int) $args;

		$queueRepo = new Direktt_Automation_QueueRepository();
		$runRepo   = new Direktt_Automation_RunRepository();

		$claimed = $queueRepo->claim($queue_id);
		if (!$claimed) {
			return; // Already processed, not due yet, or could not claim.
		}

		$queue_item = $claimed;
		$run        = $runRepo->get((int) $queue_item['run_id']);

		if (!$run || $run['status'] !== 'active') {
			$queueRepo->mark_failed($queue_item['id'], 'Run not active or missing');
			return;
		}

		$processor = Direktt_Automation_ProcessorRegistry::get($queue_item['action_type']);
		if (!$processor) {
			$queueRepo->mark_failed($queue_item['id'], 'No processor for action_type: ' . $queue_item['action_type']);
			return;
		}

		try {
			// Processor should throw on failure.
			call_user_func($processor, $queue_item, $run);

			// Mark done on success.
			$queueRepo->mark_done($queue_item['id']);
		} catch (\Throwable $e) {
			// Retry with exponential backoff, cap attempts.
			$attempts = (int) $queue_item['attempts'];
			$delay    = min(3600, pow(2, $attempts) * 60); // 1m,2m,4m,8m... up to 1h
			$queueRepo->retry_later($queue_item['id'], $delay, 6);
			error_log('[MKT] Queue item failed: ' . $e->getMessage());
		}
	}
}
