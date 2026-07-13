<?php
/**
 * Isolated shortcode storage for The Events Calendar Addon 2.
 *
 * @package GS_TECA
 */

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally kept for backward compatibility.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'GS_TECA_PLUGIN_SLUG' ) ) {
	define( 'GS_TECA_PLUGIN_SLUG', 'the-events-calendar-addon2' );
}

if ( ! defined( 'GS_TECA_SHORTCODE_TABLE' ) ) {
	define( 'GS_TECA_SHORTCODE_TABLE', 'gs_teca_shortcodes' );
}

if ( ! defined( 'GS_TECA_SHORTCODE_DB_VERSION' ) ) {
	define( 'GS_TECA_SHORTCODE_DB_VERSION', '1.0' );
}

if ( ! defined( 'GS_TECA_SHORTCODE_DB_VERSION_OPTION' ) ) {
	define( 'GS_TECA_SHORTCODE_DB_VERSION_OPTION', 'gsteca_shortcodes_db_version' );
}

if ( ! defined( 'GS_TECA_SHORTCODE_MIGRATION_OPTION' ) ) {
	define( 'GS_TECA_SHORTCODE_MIGRATION_OPTION', 'gsteca_shortcodes_migrated_from_legacy' );
}

if ( ! defined( 'GS_TECA_SHORTCODE_PREFS_OPTION' ) ) {
	define( 'GS_TECA_SHORTCODE_PREFS_OPTION', 'gsteca_addon2_shortcode_prefs' );
}

if ( ! defined( 'GS_TECA_SHORTCODE_LAYOUT_OPTION' ) ) {
	define( 'GS_TECA_SHORTCODE_LAYOUT_OPTION', 'gsteca_addon2_shortcode_layout' );
}

if ( ! defined( 'GS_TECA_VISIBILITY_SETTINGS_OPTION' ) ) {
	define( 'GS_TECA_VISIBILITY_SETTINGS_OPTION', 'gsteca_addon2_visibility_settings' );
}

if ( ! defined( 'GS_TECA_VISIBILITY_ORDER_OPTION' ) ) {
	define( 'GS_TECA_VISIBILITY_ORDER_OPTION', 'gsteca_addon2_visibility_order' );
}

if ( ! defined( 'GS_TECA_SHORTCODE_CACHE_GROUP' ) ) {
	define( 'GS_TECA_SHORTCODE_CACHE_GROUP', 'gsteca_addon2_shortcodes' );
}

/**
 * Get the plugin-specific shortcode table name.
 *
 * @return string
 */
function teca_get_shortcode_table_name() {
	global $wpdb;

	return preg_replace( '/[^A-Za-z0-9_]/', '', $wpdb->prefix . GS_TECA_SHORTCODE_TABLE );
}

/**
 * Get the legacy shared shortcode table name used by older GS plugins.
 *
 * @return string
 */
function teca_get_legacy_shortcode_table_name() {
	global $wpdb;

	return preg_replace( '/[^A-Za-z0-9_]/', '', $wpdb->prefix . 'gs_teca' );
}

/**
 * Build a plugin-scoped object cache key.
 *
 * @param string $suffix Optional cache suffix.
 * @return string
 */
function teca_shortcode_cache_key( $suffix = '' ) {
	$key = 'gsteca_addon2_shortcodes';

	if ( '' !== (string) $suffix ) {
		$key .= '_' . $suffix;
	}

	return $key;
}

/**
 * Determine whether a legacy shared-table row belongs to TECA.
 *
 * @param mixed $settings_json Raw or decoded shortcode settings.
 * @return bool
 */
function teca_is_teca_shortcode_row( $settings_json ) {
	$settings = is_string( $settings_json ) ? json_decode( $settings_json, true ) : $settings_json;

	if ( ! is_array( $settings ) ) {
		return false;
	}

	// Responsive Slider and other GS slider rows.
	if (
		isset( $settings['gs_rs_loop'] )
		|| isset( $settings['sourceType'] )
		|| isset( $settings['source_type'] )
		|| isset( $settings['gs_rs_autoplay'] )
		|| isset( $settings['gs_rs_wpcp_layout'] )
	) {
		return false;
	}

	$teca_keys = array(
		'view_type',
		'gs_teca_template',
		'gs_teca_link_type',
		'event_layout',
		'calendar_layout',
		'venue_template',
		'organizer_template',
		'gsteca-demo-data',
	);

	foreach ( $teca_keys as $key ) {
		if ( array_key_exists( $key, $settings ) ) {
			return true;
		}
	}

	if ( ! empty( $settings['gs_teca_template'] ) && false !== strpos( (string) $settings['gs_teca_template'], 'gs-teca' ) ) {
		return true;
	}

	return false;
}

/**
 * Copy legacy option values into addon2-specific keys once.
 *
 * @return void
 */
function teca_maybe_migrate_shortcode_options() {
	$map = array(
		'gs_teca_shortcode_prefs'       => GS_TECA_SHORTCODE_PREFS_OPTION,
		'gs_teca_shortcode_layout'      => GS_TECA_SHORTCODE_LAYOUT_OPTION,
		'gs_teca_visibility_settings'   => GS_TECA_VISIBILITY_SETTINGS_OPTION,
		'gs_teca_visibility_order'      => GS_TECA_VISIBILITY_ORDER_OPTION,
	);

	foreach ( $map as $legacy_option => $new_option ) {
		$new_value = get_option( $new_option, null );

		if ( null !== $new_value ) {
			continue;
		}

		$legacy_value = get_option( $legacy_option, null );

		if ( null === $legacy_value ) {
			continue;
		}

		update_option( $new_option, $legacy_value, 'yes' );
	}
}

/**
 * Migrate TECA rows from the legacy shared table into isolated storage.
 *
 * @return void
 */
function teca_migrate_legacy_shortcode_rows() {
	if ( get_option( GS_TECA_SHORTCODE_MIGRATION_OPTION ) ) {
		return;
	}

	global $wpdb;

	$legacy_table = teca_get_legacy_shortcode_table_name();
	$new_table    = teca_get_shortcode_table_name();

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- One-time migration check against legacy table name.
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $legacy_table ) ) !== $legacy_table ) {
		update_option( GS_TECA_SHORTCODE_MIGRATION_OPTION, 1, false );
		return;
	}

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- One-time migration read from legacy table; table name is generated internally and sanitized.
	$rows = $wpdb->get_results( "SELECT * FROM {$legacy_table}", ARRAY_A );

	if ( empty( $rows ) ) {
		update_option( GS_TECA_SHORTCODE_MIGRATION_OPTION, 1, false );
		return;
	}

	$max_id = 0;

	foreach ( $rows as $row ) {
		if ( empty( $row['shortcode_settings'] ) || ! teca_is_teca_shortcode_row( $row['shortcode_settings'] ) ) {
			continue;
		}

		$row_id = absint( $row['id'] ?? 0 );

		if ( ! $row_id ) {
			continue;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- One-time migration existence check; table name is generated internally and values are prepared.
		$exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$new_table} WHERE plugin_slug = %s AND id = %d LIMIT 1",
				GS_TECA_PLUGIN_SLUG,
				$row_id
			)
		);

		if ( $exists ) {
			$max_id = max( $max_id, $row_id );
			continue;
		}

		$data = array(
			'id'                 => $row_id,
			'plugin_slug'        => GS_TECA_PLUGIN_SLUG,
			'shortcode_name'     => $row['shortcode_name'],
			'shortcode_settings' => $row['shortcode_settings'],
			'created_at'         => $row['created_at'] ?? current_time( 'mysql' ),
			'updated_at'         => $row['updated_at'] ?? current_time( 'mysql' ),
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- One-time migration insert into isolated table.
		$wpdb->insert(
			$new_table,
			$data,
			array( '%d', '%s', '%s', '%s', '%s', '%s' )
		);

		$max_id = max( $max_id, $row_id );
	}

	if ( $max_id > 0 ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- One-time migration adjusts AUTO_INCREMENT on isolated table; table name is generated internally and max id is cast to int.
		$wpdb->query( 'ALTER TABLE `' . esc_sql( $new_table ) . '` AUTO_INCREMENT = ' . ( $max_id + 1 ) );
	}

	update_option( GS_TECA_SHORTCODE_MIGRATION_OPTION, 1, false );
}

/**
 * Create or upgrade the isolated shortcode table and run one-time migrations.
 *
 * @return void
 */
function teca_maybe_create_shortcode_storage() {
	global $wpdb;

	$saved_db_version = get_option( GS_TECA_SHORTCODE_DB_VERSION_OPTION );

	if ( GS_TECA_SHORTCODE_DB_VERSION === $saved_db_version ) {
		if ( ! get_option( GS_TECA_SHORTCODE_MIGRATION_OPTION ) ) {
			teca_maybe_migrate_shortcode_options();
			teca_migrate_legacy_shortcode_rows();
		}
		return;
	}

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$table_name = teca_get_shortcode_table_name();

	$sql = "CREATE TABLE {$table_name} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		plugin_slug varchar(100) NOT NULL DEFAULT 'the-events-calendar-addon2',
		shortcode_name text NOT NULL,
		shortcode_settings longtext NOT NULL,
		created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		updated_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		PRIMARY KEY  (id),
		KEY plugin_slug (plugin_slug)
	) " . $wpdb->get_charset_collate() . ';';

	dbDelta( $sql );

	update_option( GS_TECA_SHORTCODE_DB_VERSION_OPTION, GS_TECA_SHORTCODE_DB_VERSION, false );

	teca_maybe_migrate_shortcode_options();
	teca_migrate_legacy_shortcode_rows();

	if ( false === $saved_db_version ) {
		update_option( 'gsteca_install_demo_shortcodes_initially', true, false );
	}
}
