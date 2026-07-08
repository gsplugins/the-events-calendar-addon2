<?php

namespace GS_TECA;

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Flatsome UX Builder integration bootstrap.
 */
class Integration_UX_Builder {

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
	 * Register UX Builder hooks.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_shortcode' ] );
		add_action( 'ux_builder_setup', [ $this, 'register_element' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_editor_assets' ], 20 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_editor_styles' ] );
	}

	/**
	 * Whether Flatsome UX Builder APIs are available.
	 *
	 * @return bool
	 */
	public function is_ux_builder_available() {
		if ( function_exists( 'add_ux_builder_shortcode' ) ) {
			return true;
		}

		if ( defined( 'UX_BUILDER_VERSION' ) || defined( 'FLATSOME_VERSION' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Whether the current request is a UX Builder editor context.
	 *
	 * @return bool
	 */
	public function is_ux_builder_editor_context() {
		if ( function_exists( 'ux_builder_is_active' ) && ux_builder_is_active() ) {
			return true;
		}

		if ( function_exists( 'ux_builder_is_doing_shortcode' ) && ux_builder_is_doing_shortcode() ) {
			return true;
		}

		if ( ! empty( $_GET['uxb_iframe'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return true;
		}

		if ( ! empty( $_GET['app'] ) && 'uxbuilder' === $_GET['app'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return true;
		}

		return false;
	}

	/**
	 * Register the wrapper shortcode used by UX Builder.
	 *
	 * @return void
	 */
	public function register_shortcode() {
		require_once GS_TECA_PLUGIN_DIR . 'includes/integration/ux-builder/elements/class-teca-ux-builder-events-element.php';

		Teca_UX_Builder_Events_Element::register_shortcode();
	}

	/**
	 * Register the TECA Events UX Builder element.
	 *
	 * @return void
	 */
	public function register_element() {
		if ( ! function_exists( 'add_ux_builder_shortcode' ) ) {
			return;
		}

		require_once GS_TECA_PLUGIN_DIR . 'includes/integration/ux-builder/elements/class-teca-ux-builder-events-element.php';

		Teca_UX_Builder_Events_Element::register_ux_builder_element();
	}

	/**
	 * Enqueue TECA assets inside the UX Builder iframe preview.
	 *
	 * @return void
	 */
	public function enqueue_editor_assets() {
		if ( ! $this->is_ux_builder_editor_context() ) {
			return;
		}

		if ( function_exists( __NAMESPACE__ . '\\plugin' ) ) {
			plugin()->scripts->wp_enqueue_style_all( 'public', [ 'gs-teca-divi-public' ] );
			plugin()->scripts->wp_enqueue_script_all( 'public' );
		}

		if ( function_exists( __NAMESPACE__ . '\\gsTecaAssetGenerator' ) ) {
			gsTecaAssetGenerator()->enqueue_prefs_custom_css();
			gsTecaAssetGenerator()->enqueue_localize_script();
		}

		wp_enqueue_style(
			'gs-teca-ux-builder-editor',
			GS_TECA_PLUGIN_URI . 'includes/integration/ux-builder/assets/css/teca-ux-builder-editor.css',
			[],
			GS_TECA_VERSION
		);

		wp_enqueue_script(
			'gs-teca-ux-builder-editor',
			GS_TECA_PLUGIN_URI . 'includes/integration/ux-builder/assets/js/teca-ux-builder-editor.js',
			[ 'jquery' ],
			GS_TECA_VERSION,
			true
		);
	}

	/**
	 * Enqueue UX Builder panel styles in admin.
	 *
	 * @return void
	 */
	public function enqueue_admin_editor_styles() {
		if ( ! $this->is_ux_builder_available() || ! is_admin() ) {
			return;
		}

		wp_enqueue_style(
			'gs-teca-ux-builder-editor',
			GS_TECA_PLUGIN_URI . 'includes/integration/ux-builder/assets/css/teca-ux-builder-editor.css',
			[],
			GS_TECA_VERSION
		);

		$icon = GS_TECA_PLUGIN_URI . 'assets/img/events.svg';

		wp_add_inline_style(
			'gs-teca-ux-builder-editor',
			sprintf(
				'.uxb-modules .uxb-module[data-tag="teca_ux_builder_events"] .uxb-module-icon{background-image:url(%s);background-position:center center;background-size:contain;}',
				esc_url_raw( $icon )
			)
		);
	}
}
