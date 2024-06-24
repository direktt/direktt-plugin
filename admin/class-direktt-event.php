<?php

class Direktt_Event
{
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.3.4
	 *
	 * @var string The ID of this plugin.
	 */
	private string $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.3.4
	 *
	 * @var string The current version of this plugin.
	 */
	private string $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.3.4
	 */
	public function __construct(string $plugin_name, string $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	static function create_database_table()
	{
		global $wpdb;

		$table_name = $wpdb->prefix . 'direktt_events';

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
  			ID bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  			direktt_user_id varchar(256) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  			event_target varchar(256) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  			direktt_campaign_id bigint(20) unsigned DEFAULT NULL,
  			event_type varchar(256) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  			event_data json DEFAULT NULL,
  			event_time timestamp NOT NULL,
  			PRIMARY KEY  (ID),
			KEY direktt_campaign_id (direktt_campaign_id),
  			KEY event_type (event_type),
  			KEY direktt_user_id (direktt_user_id),
  			KEY event_time (event_time)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta($sql);

		$the_default_timestamp_query = "ALTER TABLE $table_name MODIFY COLUMN event_time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP;";

		$wpdb->query( $the_default_timestamp_query );
	}

	static function insert_event( $event )
	{
		global $wpdb;

		$table_name = $wpdb->prefix . 'direktt_events';

		$wpdb->insert(
			$table_name,
			$event
		);
	}
}
