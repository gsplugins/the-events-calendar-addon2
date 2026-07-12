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
 * Divi Builder integration bootstrap.
 */
class Integration_Divi {

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static $_instance = null;

	/**
	 * Script handle prefix.
	 *
	 * @var string
	 */
	private $name = 'gs-teca-divi';

	/**
	 * Divi assets base URL.
	 *
	 * @var string
	 */
	private $assets_url = '';

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
	 * Register Divi hooks.
	 */
	public function __construct() {
		add_action( 'divi_extensions_init', [ $this, 'init' ] );
	}

	/**
	 * Initialize Divi integration after Divi extensions load.
	 *
	 * @return void
	 */
	public function init() {
		if ( ! class_exists( 'ET_Builder_Module' ) && ! function_exists( 'et_builder_init' ) ) {
			return;
		}

		$this->assets_url = GS_TECA_PLUGIN_URI . 'includes/integration/divi/assets';

		add_action( 'et_builder_modules_loaded', [ $this, 'register_modules' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_divi_builder_assets' ] );
		add_action( 'wp_head', [ $this, 'print_divi_editor_styles' ] );
	}

	/**
	 * Register the TECA Events Divi module.
	 *
	 * @return void
	 */
	public function register_modules() {
		if ( ! class_exists( 'ET_Builder_Module' ) ) {
			return;
		}

		require_once GS_TECA_PLUGIN_DIR . 'includes/integration/divi/modules/class-teca-divi-events-module.php';

		if ( class_exists( __NAMESPACE__ . '\\Teca_Divi_Events_Module' ) ) {
			new Teca_Divi_Events_Module();
		}
	}

	/**
	 * Enqueue TECA assets and Divi Visual Builder scripts.
	 *
	 * @return void
	 */
	public function enqueue_divi_builder_assets() {
		if ( ! function_exists( 'et_core_is_fb_enabled' ) || ! et_core_is_fb_enabled() ) {
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

		$script_path = GS_TECA_PLUGIN_DIR . 'includes/integration/divi/assets/js/teca-divi-editor.min.js';
		$script_url  = file_exists( $script_path )
			? $this->assets_url . '/js/teca-divi-editor.min.js'
			: $this->assets_url . '/js/teca-divi-editor.js';

		wp_enqueue_script(
			$this->name . '-builder',
			$script_url,
			[ 'react-dom' ],
			GS_TECA_VERSION,
			true
		);
	}

	/**
	 * Print Divi module list icon styles in Visual Builder.
	 *
	 * @return void
	 */
	public function print_divi_editor_styles() {
		if ( ! function_exists( 'et_core_is_fb_enabled' ) || ! et_core_is_fb_enabled() ) {
			return;
		}

		$icon = GS_TECA_PLUGIN_URI . 'assets/img/events.svg';

		wp_enqueue_style(
			$this->name . '-editor',
			GS_TECA_PLUGIN_URI . 'includes/integration/divi/assets/css/teca-divi-editor.css',
			[],
			GS_TECA_VERSION
		);

		wp_add_inline_style(
			$this->name . '-editor',
			sprintf(
				'.et-db #et-boc .et-l .et-fb-modules-list ul > li.gs_teca_events:before{background:url(%s) no-repeat center center;background-size:contain;content:"";height:28px;}',
				esc_url_raw( $icon )
			)
		);
	}
}
