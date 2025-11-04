<?php

defined( 'ABSPATH' ) || exit;

class Direktt_Automation {

	public static function run_and_queue(
		$automation_key,
		$subscription_id,
		$msg_obj,
		$action,
		$time_in_seconds,
		$initial_state = null
	) {
		$runs  = new Direktt_Automation_RunRepository();
		$queue = new Direktt_Automation_QueueRepository();

		$run_id = $runs->create( $automation_key, $subscription_id, $msg_obj, $initial_state );

		$queue->enqueue( $run_id, $subscription_id, $action, $msg_obj, time() + (int) $time_in_seconds, 0 );

		return $run_id;
	}

	public static function run_and_queue_recurring(
		$automation_key,
		$subscription_id,
		$state_or_payload,
		$action,
		$start_in_seconds,
		$interval_seconds = null,
		$initial_step = null,
		$max_runs = null,
		$end_ts = null,
		$allow_overlap = false,
		$priority = 0,
		$cron_expression = null
	) {
		$runs = new Direktt_Automation_RunRepository();
		$rec  = new Direktt_Automation_RecurringRepository();

		// Create run; mirror your existing semantics (store state payload, set step)
		$run_id = $runs->create( $automation_key, $subscription_id, (array) $state_or_payload, $initial_step );

		// Optionally also enqueue the very first run immediately (start_in_seconds delay):
		// $queue = new Direktt_Automation_QueueRepository();
		// $queue->enqueue($run_id, $subscription_id, $action, (array)$state_or_payload, time() + (int)$start_in_seconds, (int)$priority);

		// Create recurrence definition (AS will tick and produce queue items)
		$recurrence_id = $rec->create(
			$run_id,
			$subscription_id,
			$action,
			(array) $state_or_payload,
			time() + (int) $start_in_seconds,
			$interval_seconds,
			$cron_expression,
			$priority,
			$max_runs,
			$end_ts,
			$allow_overlap
		);

		return array( $run_id, $recurrence_id );
	}
}

class Direktt_Automation_DB {

	public static function table_runs() {
		global $wpdb;
		return $wpdb->prefix . 'direktt_auto_runs';
	}

	public static function table_queue() {
		global $wpdb;
		return $wpdb->prefix . 'direktt_auto_queue';
	}

	public static function table_messages() {
		global $wpdb;
		return $wpdb->prefix . 'direktt_auto_messages_log';
	}

	public static function table_recurrences() {
		global $wpdb;
		return $wpdb->prefix . 'direktt_auto_recurrences';
	}

	public static function install() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$charset_collate = $wpdb->get_charset_collate();

		$runs = 'CREATE TABLE ' . self::table_runs() . " (
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

		$queue = 'CREATE TABLE ' . self::table_queue() . " (
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

		$recurrences = 'CREATE TABLE ' . self::table_recurrences() . " (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				run_id BIGINT UNSIGNED NOT NULL,
				direktt_user_id varchar(256) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
				action_type VARCHAR(64) NOT NULL,
				payload LONGTEXT NULL,
				interval_seconds INT NULL,
				cron_expression VARCHAR(64) NULL,
				start_at DATETIME NOT NULL,
				end_at DATETIME NULL,
				max_runs INT NULL,
				runs_count INT NOT NULL DEFAULT 0,
				allow_overlap TINYINT(1) NOT NULL DEFAULT 0,
				priority TINYINT NOT NULL DEFAULT 0,
				status ENUM('active','paused','canceled','completed') NOT NULL DEFAULT 'active',
				last_run_at DATETIME NULL,
				created_at DATETIME NOT NULL,
				updated_at DATETIME NOT NULL,
				PRIMARY KEY (id),
				KEY run_idx (run_id),
				KEY status_idx (status)
			) $charset_collate;";

		$messages = 'CREATE TABLE ' . self::table_messages() . " (
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

		dbDelta( $runs );
		dbDelta( $queue );
		dbDelta( $recurrences );
		dbDelta( $messages );
	}
}

class Direktt_Automation_Time {

	public static function now_utc() {
		return gmdate( 'Y-m-d H:i:s' );
	}

	public static function ts_to_mysql( $ts ) {
		return gmdate( 'Y-m-d H:i:s', (int) $ts );
	}
}

class Direktt_Automation_RunRepository {

	public function create( $automation_key, $direktt_user_id, array $state = array(), $current_step = null ) {
		global $wpdb;

		$table = Direktt_Automation_DB::table_runs();
		$now   = Direktt_Automation_Time::now_utc();

		$data = array(
			'automation_key'  => $automation_key,
			'direktt_user_id' => $direktt_user_id,
			'current_step'    => $current_step,
			'status'          => 'active',
			'state'           => wp_json_encode( $state ),
			'started_at'      => $now,
			'updated_at'      => $now,
		);

		$formats = array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' );

		$wpdb->insert( $table, $data, $formats ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery -- Justification: selective query on small dataset, Custom database used
		return (int) $wpdb->insert_id;
	}

	public function get( $id ) {
		global $wpdb;
		$table = Direktt_Automation_DB::table_runs();

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery -- Justification: table name is not prepared, selective query on small dataset, Custom database used
		if ( $row && ! empty( $row['state'] ) ) {
			$row['state'] = json_decode( $row['state'], true );
		}
		return $row;
	}

	public function update_state( $id, array $state ) {
		global $wpdb;
		$table = Direktt_Automation_DB::table_runs();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery -- Justification: selective query on small dataset, Custom database used
		return (bool) $wpdb->update(
			$table,
			array(
				'state'      => wp_json_encode( $state ),
				'updated_at' => Direktt_Automation_Time::now_utc(),
			),
			array( 'id' => (int) $id ),
			array( '%s', '%s' ),
			array( '%d' )
		);
	}

	public function set_step( $id, $step, $touch_last_step = true ) {
		global $wpdb;
		$table = Direktt_Automation_DB::table_runs();

		$data = array(
			'current_step' => $step,
			'updated_at'   => Direktt_Automation_Time::now_utc(),
		);

		$formats = array( '%s', '%s' );

		if ( $touch_last_step ) {
			$data['last_step_at'] = Direktt_Automation_Time::now_utc();
			$formats[]            = '%s';
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery -- Justification: selective query on small dataset, Custom database used
		return (bool) $wpdb->update( $table, $data, array( 'id' => (int) $id ), $formats, array( '%d' ) );
	}

	public function set_status( $id, $status ) {
		global $wpdb;
		$table = Direktt_Automation_DB::table_runs();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery -- Justification: selective query on small dataset, Custom database used
		return (bool) $wpdb->update(
			$table,
			array(
				'status'     => $status,
				'updated_at' => Direktt_Automation_Time::now_utc(),
			),
			array( 'id' => (int) $id ),
			array( '%s', '%s' ),
			array( '%d' )
		);
	}

	public function set_step_if_current( $id, $expected_step, $new_step, $touch_last_step = true ) {
		global $wpdb;
		$table = Direktt_Automation_DB::table_runs();
		$now   = Direktt_Automation_Time::now_utc();

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Justification: table name is not prepared

		if ( $touch_last_step ) {
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
				(int) $id,
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
				(int) $id,
				$expected_step
			);
		}

		// phpcs:enable

		$rows = $wpdb->query( $sql ); // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery -- Justification: table name is not prepared, elective query on small dataset, Custom database used
		return $rows === 1; // true if we actually advanced
	}
}

class Direktt_Automation_QueueRepository {


	const AS_GROUP = 'direktt_automation';

	protected function as_available() {
		return function_exists( 'as_schedule_single_action' );
	}

	public function enqueue( $run_id, $direktt_user_id, $action_type, array $payload, $scheduled_ts, $priority = 0 ) {
		global $wpdb;

		$table        = Direktt_Automation_DB::table_queue();
		$now          = Direktt_Automation_Time::now_utc();
		$scheduled_at = is_numeric( $scheduled_ts ) ? Direktt_Automation_Time::ts_to_mysql( $scheduled_ts ) : $scheduled_ts;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Justification: Custom database used
		$wpdb->insert(
			$table,
			array(
				'run_id'          => (int) $run_id,
				'direktt_user_id' => $direktt_user_id,
				'action_type'     => $action_type,
				'payload'         => wp_json_encode( $payload ),
				'scheduled_at'    => $scheduled_at,
				'priority'        => (int) $priority,
				'status'          => 'pending',
				'attempts'        => 0,
				'locked_at'       => null,
				'worker_id'       => null,
				'error_message'   => null,
				'created_at'      => $now,
				'updated_at'      => $now,
			),
			array( '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%s', '%s', '%s', '%s', '%s' )
		);

		$queue_id = (int) $wpdb->insert_id;

		// Schedule processing with Action Scheduler or WP-Cron fallback
		$this->schedule_processing( $queue_id, strtotime( $scheduled_at ) );

		return $queue_id;
	}

	protected function schedule_processing( $queue_id, $timestamp ) {
		if ( $this->as_available() ) {
			as_schedule_single_action( $timestamp, 'direktt_automation_process_queue_item', array( 'queue_id' => $queue_id ), self::AS_GROUP );
		} else {
			wp_schedule_single_event( $timestamp, 'direktt_automation_fallback_process_queue_item', array( 'queue_id' => $queue_id ) );
		}
	}

	public function get( $id ) {
		global $wpdb;
		$table = Direktt_Automation_DB::table_queue();

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Justification: table name is not prepared
		if ( $row && ! empty( $row['payload'] ) ) {
			$row['payload'] = json_decode( $row['payload'], true );
		}
		return $row;
	}

	// Attempt to claim a row for processing. Returns the claimed row or false.
	public function claim( $id ) {
		global $wpdb;
		$table     = Direktt_Automation_DB::table_queue();
		$worker_id = substr( uniqid( 'w_', true ), 0, 63 );

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Justification: table name is not prepared

		// Lock only if pending and due
		$updated = $wpdb->query(
			$wpdb->prepare(
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
			)
		);

		// phpcs:enable

		if ( $updated === 1 ) {
			$row = $this->get( $id );
			if ( $row && $row['worker_id'] === $worker_id && $row['status'] === 'locked' ) {
				return $row;
			}
		}
		return false;
	}

	public function mark_done( $id ) {
		global $wpdb;
		$table = Direktt_Automation_DB::table_queue();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery -- Justification: selective query on small dataset, Custom database used
		return (bool) $wpdb->update(
			$table,
			array(
				'status'     => 'done',
				'updated_at' => Direktt_Automation_Time::now_utc(),
			),
			array( 'id' => (int) $id ),
			array( '%s', '%s' ),
			array( '%d' )
		);
	}

	public function mark_failed( $id, $error_message ) {
		global $wpdb;
		$table = Direktt_Automation_DB::table_queue();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery -- Justification: selective query on small dataset, Custom database used
		return (bool) $wpdb->update(
			$table,
			array(
				'status'        => 'failed',
				'error_message' => $error_message,
				'updated_at'    => Direktt_Automation_Time::now_utc(),
			),
			array( 'id' => (int) $id ),
			array( '%s', '%s', '%s' ),
			array( '%d' )
		);
	}

	public function retry_later( $id, $delay_seconds = 60, $max_attempts = 5 ) {
		global $wpdb;
		$table = Direktt_Automation_DB::table_queue();

		$row = $this->get( $id );
		if ( ! $row ) {
			return false;
		}

		if ( (int) $row['attempts'] >= $max_attempts ) {
			return $this->mark_failed( $id, 'Max attempts exceeded' );
		}

		$scheduled_ts = time() + (int) $delay_seconds;
		$scheduled_at = Direktt_Automation_Time::ts_to_mysql( $scheduled_ts );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery -- Justification: selective query on small dataset, Custom database used
		$ok = (bool) $wpdb->update(
			$table,
			array(
				'status'       => 'pending',
				'locked_at'    => null,
				'worker_id'    => null,
				'updated_at'   => Direktt_Automation_Time::now_utc(),
				'scheduled_at' => $scheduled_at,
			),
			array( 'id' => (int) $id ),
			array( '%s', '%s', '%s', '%s', '%s' ),
			array( '%d' )
		);

		if ( $ok ) {
			$this->schedule_processing( $id, $scheduled_ts );
		}

		return $ok;
	}

	public function has_pending_for( $run_id, $action_type ) {
		global $wpdb;
		$table = Direktt_Automation_DB::table_queue();

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Justification: table name is not prepared

		$sql = $wpdb->prepare(
			"SELECT 1 FROM $table
			WHERE run_id = %d
			AND action_type = %s
			AND status IN ('pending','locked')
			LIMIT 1",
			(int) $run_id,
			$action_type
		);

		// phpcs:enable

		return (bool) $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery -- Justification: table name is not prepared, selective query on small dataset, Custom database used
	}
}

class Direktt_Automation_RecurringRepository {

	const AS_GROUP = Direktt_Automation_QueueRepository::AS_GROUP;

	protected function table() {
		global $wpdb;
		return $wpdb->prefix . 'direktt_auto_recurrences';
	}

	protected function as_available() {
		return function_exists( 'as_schedule_single_action' );
	}

	public function create( $run_id, $direktt_user_id, $action_type, array $payload, $start_ts, $interval_seconds = null, $cron_expression = null, $priority = 0, $max_runs = null, $end_ts = null, $allow_overlap = false ) {
		global $wpdb;
		$now   = Direktt_Automation_Time::now_utc();
		$start = is_numeric( $start_ts ) ? Direktt_Automation_Time::ts_to_mysql( $start_ts ) : $start_ts;
		$end   = $end_ts ? ( is_numeric( $end_ts ) ? Direktt_Automation_Time::ts_to_mysql( $end_ts ) : $end_ts ) : null;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom database used
		$wpdb->insert(
			$this->table(),
			array(
				'run_id'           => (int) $run_id,
				'direktt_user_id'  => $direktt_user_id,
				'action_type'      => $action_type,
				'payload'          => wp_json_encode( $payload ),
				'interval_seconds' => $interval_seconds ? (int) $interval_seconds : null,
				'cron_expression'  => $cron_expression,
				'start_at'         => $start,
				'end_at'           => $end,
				'max_runs'         => $max_runs,
				'runs_count'       => 0,
				'allow_overlap'    => $allow_overlap ? 1 : 0,
				'priority'         => (int) $priority,
				'status'           => 'active',
				'last_run_at'      => null,
				'created_at'       => $now,
				'updated_at'       => $now,
			),
			array( '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%s', '%s', '%s' )
		);

		$id = (int) $wpdb->insert_id;

		$this->schedule( $id );

		return $id;
	}

	public function get( $id ) {
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table()} WHERE id = %d", (int) $id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery -- Justification: table name is not prepared, selective query on small dataset, Custom database used
		if ( $row && ! empty( $row['payload'] ) ) {
			$row['payload'] = json_decode( $row['payload'], true );
		}
		return $row;
	}

	public function set_status( $id, $status ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery -- Justification: selective query on small dataset, Custom database used
		return (bool) $wpdb->update(
			$this->table(),
			array(
				'status'     => $status,
				'updated_at' => Direktt_Automation_Time::now_utc(),
			),
			array( 'id' => (int) $id ),
			array( '%s', '%s' ),
			array( '%d' )
		);
	}

	public function increment_count( $id ) {
		global $wpdb;
		$now = Direktt_Automation_Time::now_utc();

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery -- Justification: table name is not prepared, selective query on small dataset, Custom database used
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$this->table()} SET runs_count = runs_count + 1, last_run_at = %s, updated_at = %s WHERE id = %d",
				$now,
				$now,
				(int) $id
			)
		);
		// phpcs:enable
	}

	public function cancel( $id, $complete_run = false ) {
		$ok = $this->set_status( $id, 'canceled' );
		if ( $ok ) {
			$this->unschedule_async( $id, $complete_run );
		}
		return $ok;
	}

	public function pause( $id ) {
		$ok = $this->set_status( $id, 'paused' );
		if ( $ok ) {
			$this->unschedule_async( $id, false );
		}
		return $ok;
	}

	public function cancel_now( $id, $complete_run = false ) {
		$ok = $this->set_status( $id, 'canceled' );
		if ( $ok ) {
			$this->unschedule_now( $id, $complete_run );
		}
		return $ok;
	}

	public function resume( $id ) {
		$ok = $this->set_status( $id, 'active' );
		if ( $ok ) {
			$this->schedule( $id );
		}
		return $ok;
	}

	public function unschedule_now( $id, $complete_run = false ) {
		$args = array( 'recurrence_id' => (int) $id );

		if ( function_exists( 'as_unschedule_all_actions' ) ) {
			as_unschedule_all_actions( 'direktt_automation_process_recurrence', $args, self::AS_GROUP );
		}
		// WP-Cron fallback
		wp_clear_scheduled_hook( 'direktt_automation_fallback_process_recurrence', $args );

		if ( $complete_run ) {
			$rec = $this->get( $id );
			if ( $rec && ! empty( $rec['run_id'] ) ) {
				$runRepo = new Direktt_Automation_RunRepository();
				$run     = $runRepo->get( (int) $rec['run_id'] );
				if ( $run && in_array( $run['status'], array( 'active', 'paused' ), true ) ) {
					$runRepo->set_status( (int) $rec['run_id'], 'completed' );
				}
			}
		}
	}

	public function unschedule_async( $id, $complete_run = false ) {
		$args = array(
			'recurrence_id' => (int) $id,
			'complete_run'  => (bool) $complete_run,
		);

		if ( function_exists( 'as_enqueue_async_action' ) ) {
			as_enqueue_async_action( 'direktt_automation_cancel_recurrence', $args, self::AS_GROUP );
		} elseif ( function_exists( 'as_schedule_single_action' ) ) {
			as_schedule_single_action( time(), 'direktt_automation_cancel_recurrence', $args, self::AS_GROUP );
		} else {
			wp_schedule_single_event( time(), 'direktt_automation_fallback_cancel_recurrence', $args );
		}
	}

	protected function schedule( $id ) {
		$row = $this->get( $id );
		if ( ! $row || $row['status'] !== 'active' ) {
			return;
		}
		$start_ts = strtotime( $row['start_at'] );
		$args     = array( 'recurrence_id' => (int) $id );

		if ( $this->as_available() ) {
			if ( ! empty( $row['cron_expression'] ) ) {
				if ( function_exists( 'as_schedule_cron_action' ) ) {
					as_schedule_cron_action( $start_ts, $row['cron_expression'], 'direktt_automation_process_recurrence', $args, self::AS_GROUP, true );
				}
			} else {
				as_schedule_recurring_action( $start_ts, (int) $row['interval_seconds'], 'direktt_automation_process_recurrence', $args, self::AS_GROUP, true );
			}
		} else {
			// WP-Cron fallback
			$hook = 'direktt_automation_fallback_process_recurrence';
			if ( ! empty( $row['cron_expression'] ) ) {
				// No native cron expression support; schedule a minutely heartbeat, and gate inside the worker (optional).
				// Simpler: approximate with interval_seconds if you have one or skip cron_expression in fallback.
				return;
			} else {
				$slug = 'direktt_every_' . (int) $row['interval_seconds'];
				add_filter(
					'cron_schedules',
					function ( $schedules ) use ( $row, $slug ) {
						$schedules[ $slug ] = array(
							'interval' => (int) $row['interval_seconds'],
							'display'  => 'Direktt every ' . (int) $row['interval_seconds'] . 's',
						);
						return $schedules;
					}
				);
				if ( ! wp_next_scheduled( $hook, $args ) ) {
					wp_schedule_event( $start_ts, $slug, $hook, $args );
				}
			}
		}
	}

	protected function unschedule( $id ) {
		$args = array( 'recurrence_id' => (int) $id );
		if ( $this->as_available() ) {
			as_unschedule_all_actions( 'direktt_automation_process_recurrence', $args, self::AS_GROUP );
		} else {
			wp_clear_scheduled_hook( 'direktt_automation_fallback_process_recurrence', $args );
		}
	}
}

class Direktt_Automation_RecurringWorker {

	public static function process_recurrence( $args ) {
		$recurrence_id = is_array( $args ) && isset( $args['recurrence_id'] ) ? (int) $args['recurrence_id'] : (int) $args;

		$recRepo   = new Direktt_Automation_RecurringRepository();
		$queueRepo = new Direktt_Automation_QueueRepository();

		$rec = $recRepo->get( $recurrence_id );
		if ( ! $rec ) {
			return;
		}

		// If not active, nothing to do (but there might still be a pending next tick created before).
		if ( $rec['status'] !== 'active' ) {
			// Schedule async canceller to cleanup any pending next occurrences
			$recRepo->unschedule_async( $recurrence_id, false );
			return;
		}

		// Check constraints first. Remember: AS already scheduled the next tick before our handler runs.
		$now = time();

		$reached_end_time = ! empty( $rec['end_at'] ) && $now > strtotime( $rec['end_at'] );
		$reached_max_runs = ! empty( $rec['max_runs'] ) && (int) $rec['runs_count'] >= (int) $rec['max_runs'];

		if ( $reached_end_time || $reached_max_runs ) {
			// Mark recurrence as completed (or canceled) and schedule async canceller to remove the pending next.
			$recRepo->set_status( $recurrence_id, 'canceled' );

			// Optionally complete the associated run when recurrence finishes
			$complete_run = true;

			$recRepo->unschedule_async( $recurrence_id, $complete_run );

			return;
		}

		// Optional overlap guard
		if ( empty( $rec['allow_overlap'] ) && $queueRepo->has_pending_for( (int) $rec['run_id'], $rec['action_type'] ) ) {
			// Skip this tick; next tick is already pending/locked. Do not increment count.
			return;
		}

		// Enqueue one occurrence for your normal queue
		$queueRepo->enqueue(
			(int) $rec['run_id'],
			$rec['direktt_user_id'],
			$rec['action_type'],
			is_array( $rec['payload'] ) ? $rec['payload'] : (array) $rec['payload'],
			$now,
			(int) $rec['priority']
		);

		// Track count and last_run_at
		$recRepo->increment_count( $recurrence_id );
	}

	public static function cancel_recurrence_async( $args ) {
		$recurrence_id = is_array( $args ) && isset( $args['recurrence_id'] ) ? (int) $args['recurrence_id'] : (int) $args;

		// $complete_run  = is_array($args) && !empty($args['complete_run']);
		$complete_run = true;

		$recRepo = new Direktt_Automation_RecurringRepository();
		// Remove any pending future ticks for this recurrence
		$recRepo->unschedule_now( $recurrence_id, $complete_run );
	}
}

class Direktt_Automation_MessagesLogRepository {

	public function log_queued( $run_id, $direktt_user_id, $step_id, $channel = 'direktt_message', $template_id = null, $scheduled_at = null ) {
		global $wpdb;
		$table = Direktt_Automation_DB::table_messages();
		$now   = Direktt_Automation_Time::now_utc();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Justification: custom database used
		$wpdb->insert(
			$table,
			array(
				'run_id'              => (int) $run_id,
				'direktt_user_id'     => $direktt_user_id,
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
			),
			array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		return (int) $wpdb->insert_id;
	}

	public function mark_sent( $id, $provider_message_id = null ) {
		global $wpdb;
		$table = Direktt_Automation_DB::table_messages();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery -- Justification: selective query on small dataset, custom database used
		return (bool) $wpdb->update(
			$table,
			array(
				'status'              => 'sent',
				'provider_message_id' => $provider_message_id,
				'sent_at'             => Direktt_Automation_Time::now_utc(),
				'updated_at'          => Direktt_Automation_Time::now_utc(),
			),
			array( 'id' => (int) $id ),
			array( '%s', '%s', '%s', '%s' ),
			array( '%d' )
		);
	}

	public function mark_failed( $id, $error_message ) {
		global $wpdb;
		$table = Direktt_Automation_DB::table_messages();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery -- Justification: selective query on small dataset, custom database used
		return (bool) $wpdb->update(
			$table,
			array(
				'status'        => 'failed',
				'error_message' => $error_message,
				'updated_at'    => Direktt_Automation_Time::now_utc(),
			),
			array( 'id' => (int) $id ),
			array( '%s', '%s', '%s' ),
			array( '%d' )
		);
	}
}

class Direktt_Automation_ProcessorRegistry {

	private static $map = array();

	public static function register( $action_type, callable $processor ) {
		self::$map[ $action_type ] = $processor;
	}

	public static function get( $action_type ) {
		return isset( self::$map[ $action_type ] ) ? self::$map[ $action_type ] : null;
	}
}

class Direktt_Automation_Worker {

	public static function process_queue_item( $args ) {
		// AS passes args as parameters; WP-Cron passes as the first param too.
		// Expect $args to be either ['queue_id' => X] or just queue_id.
		$queue_id = is_array( $args ) && isset( $args['queue_id'] ) ? (int) $args['queue_id'] : (int) $args;

		$queueRepo = new Direktt_Automation_QueueRepository();
		$runRepo   = new Direktt_Automation_RunRepository();

		$claimed = $queueRepo->claim( $queue_id );
		if ( ! $claimed ) {
			return; // Already processed, not due yet, or could not claim.
		}

		$queue_item = $claimed;
		$run        = $runRepo->get( (int) $queue_item['run_id'] );

		if ( ! $run || $run['status'] !== 'active' ) {
			$queueRepo->mark_failed( $queue_item['id'], 'Run not active or missing' );
			return;
		}

		$processor = Direktt_Automation_ProcessorRegistry::get( $queue_item['action_type'] );
		if ( ! $processor ) {
			$queueRepo->mark_failed( $queue_item['id'], 'No processor for action_type: ' . $queue_item['action_type'] );
			return;
		}

		try {
			// Processor should throw on failure.
			call_user_func( $processor, $queue_item, $run );

			// Mark done on success.
			$queueRepo->mark_done( $queue_item['id'] );
		} catch ( \Throwable $e ) {
			// Retry with exponential backoff, cap attempts.
			$attempts = (int) $queue_item['attempts'];
			$delay    = min( 3600, pow( 2, $attempts ) * 60 ); // 1m,2m,4m,8m... up to 1h
			$queueRepo->retry_later( $queue_item['id'], $delay, 6 );
		}
	}
}
