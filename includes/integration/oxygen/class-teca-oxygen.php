<?php

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally kept for backward compatibility.
namespace GS_TECA;

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Oxygen Builder integration bootstrap.
 */
class Integration_Oxygen {

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static $_instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return self
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Register Oxygen hooks.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', [ $this, 'plugins_loaded' ] );
		add_action( 'init', [ $this, 'register_element' ] );
		add_action( 'oxygen_vsb_enqueue_builder_scripts', [ $this, 'enqueue_builder_assets' ] );
	}

	/**
	 * Whether Oxygen Builder APIs are available.
	 *
	 * @return bool
	 */
	public function is_oxygen_available() {
		return class_exists( 'OxyEl' ) || class_exists( 'OxygenElement' );
	}

	/**
	 * Whether the current request is the Oxygen builder iframe.
	 *
	 * @return bool
	 */
	public function is_oxygen_builder_context() {
		if ( ! empty( $_GET['ct_builder'] ) && ! empty( $_GET['oxygen_iframe'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return true;
		}

		return false;
	}

	/**
	 * Prevent Oxygen template buffering from breaking TECA shortcode previews.
	 *
	 * @return void
	 */
	public function plugins_loaded() {
		if ( ! $this->is_teca_shortcode_preview_request() ) {
			return;
		}

		add_action(
			'ct_builder_start',
			static function() {
				remove_action( 'ct_builder_start', 'ct_templates_buffer_start' );
			},
			0
		);

		add_action(
			'ct_builder_end',
			static function() {
				remove_action( 'ct_builder_end', 'ct_templates_buffer_end' );
			},
			0
		);
	}

	/**
	 * Register the TECA Events Oxygen element.
	 *
	 * @return void
	 */
	public function register_element() {
		if ( ! $this->is_oxygen_available() || ! class_exists( 'OxyEl' ) ) {
			return;
		}

		require_once GS_TECA_PLUGIN_DIR . 'includes/integration/oxygen/elements/class-teca-oxygen-events-element.php';

		if ( class_exists( 'TECA_Oxygen_Events_Element' ) ) {
			new TECA_Oxygen_Events_Element();
		}
	}

	/**
	 * Enqueue Oxygen builder panel assets.
	 *
	 * @return void
	 */
	public function enqueue_builder_assets() {
		wp_enqueue_style(
			'gs-teca-oxygen-editor',
			GS_TECA_PLUGIN_URI . 'includes/integration/oxygen/assets/css/teca-oxygen-editor.css',
			[],
			GS_TECA_VERSION
		);
	}

	/**
	 * Whether the current request is a TECA shortcode preview.
	 *
	 * @return bool
	 */
	protected function is_teca_shortcode_preview_request() {
		return isset( $_REQUEST['gs_teca_shortcode_preview'] ) && ! empty( $_REQUEST['gs_teca_shortcode_preview'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}
}
