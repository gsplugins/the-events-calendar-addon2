<?php

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally kept for backward compatibility.
namespace GS_TECA;

use FLBuilder;

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Beaver Builder integration bootstrap.
 */
class Integration_Beaver {

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static $_instance = null;

	/**
	 * Whether the module has been registered.
	 *
	 * @var bool
	 */
	private static $module_registered = false;

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
	 * Register Beaver Builder hooks.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_module' ], 11 );
		add_action( 'fl_builder_loaded', [ $this, 'register_module' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_builder_assets' ], 20 );
	}

	/**
	 * Whether Beaver Builder APIs are available.
	 *
	 * @return bool
	 */
	public function is_beaver_available() {
		return class_exists( 'FLBuilder' ) && class_exists( 'FLBuilderModule' );
	}

	/**
	 * Whether the current request is a Beaver Builder editor context.
	 *
	 * @return bool
	 */
	public function is_beaver_builder_context() {
		if ( class_exists( 'FLBuilderModel' ) && FLBuilderModel::is_builder_active() ) {
			return true;
		}

		if ( isset( $_GET['fl_builder_ui_iframe'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return true;
		}

		if ( ! empty( $_POST['fl_builder_data'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return true;
		}

		return false;
	}

	/**
	 * Register the TECA Events Beaver Builder module.
	 *
	 * @return void
	 */
	public function register_module() {
		if ( self::$module_registered || ! $this->is_beaver_available() ) {
			return;
		}

		require_once GS_TECA_PLUGIN_DIR . 'includes/integration/beaver/modules/teca-events/teca-events.php';

		if ( ! class_exists( __NAMESPACE__ . '\\Teca_Beaver_Events_Module' ) ) {
			return;
		}

		FLBuilder::register_module(
			__NAMESPACE__ . '\\Teca_Beaver_Events_Module',
			[
				'general' => [
					'title'    => esc_html__( 'General', 'the-events-calendar-addon2' ),
					'sections' => [
						'general' => [
							'title'  => '',
							'fields' => [
								'shortcode_id' => [
									'type'    => 'select',
									'label'   => esc_html__( 'Select TECA Shortcode', 'the-events-calendar-addon2' ),
									'options' => teca_get_saved_shortcodes_for_beaver(),
									'preview' => [
										'type' => 'none',
									],
								],
							],
						],
					],
				],
			]
		);

		self::$module_registered = true;
	}

	/**
	 * Enqueue TECA assets inside the Beaver Builder editor preview.
	 *
	 * @return void
	 */
	public function enqueue_builder_assets() {
		if ( ! $this->is_beaver_builder_context() ) {
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
	}
}
