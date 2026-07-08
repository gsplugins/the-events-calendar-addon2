<?php

namespace GS_TECA;

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPBakery Page Builder integration bootstrap.
 */
class Integration_WPBakery {

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
	 * Register WPBakery hooks.
	 */
	public function __construct() {
		add_action( 'vc_before_init', [ $this, 'register_element' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_editor_assets' ] );
		add_action( 'vc_frontend_editor_enqueue_js_css', [ $this, 'enqueue_frontend_editor_assets' ] );
	}

	/**
	 * Whether WPBakery is available.
	 *
	 * @return bool
	 */
	public function is_wpbakery_available() {
		return function_exists( 'vc_map' );
	}

	/**
	 * Register the TECA Events WPBakery element.
	 *
	 * @return void
	 */
	public function register_element() {
		if ( ! $this->is_wpbakery_available() ) {
			return;
		}

		require_once GS_TECA_PLUGIN_DIR . 'includes/integration/wpbakery/elements/class-teca-wpbakery-events-element.php';

		Teca_WPBakery_Events_Element::register();
	}

	/**
	 * Enqueue WPBakery backend editor assets.
	 *
	 * @return void
	 */
	public function enqueue_editor_assets() {
		if ( ! $this->is_wpbakery_available() || ! is_admin() ) {
			return;
		}

		$this->enqueue_shared_editor_assets();
	}

	/**
	 * Enqueue WPBakery frontend editor assets.
	 *
	 * @return void
	 */
	public function enqueue_frontend_editor_assets() {
		if ( ! $this->is_wpbakery_available() ) {
			return;
		}

		if ( function_exists( __NAMESPACE__ . '\\plugin' ) ) {
			plugin()->scripts->wp_enqueue_style_all( 'public' );
			plugin()->scripts->wp_enqueue_script_all( 'public' );
		}

		if ( function_exists( __NAMESPACE__ . '\\gsTecaAssetGenerator' ) ) {
			gsTecaAssetGenerator()->enqueue_prefs_custom_css();
			gsTecaAssetGenerator()->enqueue_localize_script();
		}

		$this->enqueue_shared_editor_assets();
	}

	/**
	 * Enqueue shared WPBakery editor CSS/JS.
	 *
	 * @return void
	 */
	protected function enqueue_shared_editor_assets() {
		wp_enqueue_style(
			'gs-teca-wpbakery-editor',
			GS_TECA_PLUGIN_URI . 'includes/integration/wpbakery/assets/css/teca-wpbakery-editor.css',
			[],
			GS_TECA_VERSION
		);

		wp_enqueue_script(
			'gs-teca-wpbakery-editor',
			GS_TECA_PLUGIN_URI . 'includes/integration/wpbakery/assets/js/teca-wpbakery-editor.js',
			[ 'jquery' ],
			GS_TECA_VERSION,
			true
		);

		wp_localize_script(
			'gs-teca-wpbakery-editor',
			'gs_teca_wpbakery',
			[
				'elementBase'       => 'teca_wpbakery_events',
				'editShortcodeBase' => admin_url( 'admin.php?page=gs-the-events-calendar-addon#/shortcode/' ),
			]
		);

		$icon = GS_TECA_PLUGIN_URI . 'assets/img/events.svg';

		wp_add_inline_style(
			'gs-teca-wpbakery-editor',
			sprintf(
				'.vc_element-icon.icon-teca-wpbakery-events{background-image:url(%s);}',
				esc_url_raw( $icon )
			)
		);
	}
}
