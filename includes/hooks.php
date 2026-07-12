<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally kept for backward compatibility.
namespace GS_TECA;

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class Hooks {

    public function __construct() {
        add_action( 'admin_init', [ $this, 'maybe_redirect' ] );
        add_action( 'plugins_loaded', [ $this, 'plugin_loaded' ] );
        add_action( 'in_admin_header', [ $this, 'disable_admin_notices' ], 200 );
    }

    public function maybe_redirect() {

        if ( get_option('gs_teca_activation_redirect', false) ) {

            delete_option('gs_teca_activation_redirect');

            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Core multi-plugin activation sets this query arg without a nonce.
            if ( ! isset( $_GET['activate-multi'] ) ) {
                wp_safe_redirect( esc_url( "admin.php?page=the-events-calendar-addon-help" ) );
            }
        }
    }

    public function plugin_loaded() {
        gs_update_plugin_version();
        gs_teca_maybe_purge_assets_cache();
        plugin()->builder->maybe_create_shortcodes_table();
    }

    public function disable_admin_notices() {
        global $parent_file;
        if ( $parent_file != 'the-events-calendar-addon2' ) return;

		remove_all_actions( 'network_admin_notices' );
		remove_all_actions( 'user_admin_notices' );
		remove_all_actions( 'admin_notices' );
		remove_all_actions( 'all_admin_notices' );		
    
    }

}