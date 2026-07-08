<?php

namespace GS_TECA;

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Elementor integration bootstrap.
 */
class Integration_Elementor {

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
	 * Register Elementor hooks.
	 */
	public function __construct() {
		add_action( 'elementor/widgets/register', [ $this, 'register_elementor_widget' ] );
		add_action( 'elementor/elements/categories_registered', [ $this, 'add_elementor_widget_category' ] );

		add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'enqueue_elementor_editor_scripts' ] );
		add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'enqueue_elementor_editor_styles' ] );

		add_action( 'elementor/preview/enqueue_styles', [ $this, 'enqueue_elementor_preview_styles' ] );
		add_action( 'elementor/preview/enqueue_scripts', [ $this, 'enqueue_elementor_preview_scripts' ] );
	}

	/**
	 * Register the TECA Events widget.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Widgets manager.
	 * @return void
	 */
	public function register_elementor_widget( $widgets_manager ) {
		require_once GS_TECA_PLUGIN_DIR . 'includes/integration/elementor/widgets/class-teca-elementor-events-widget.php';

		if ( ! class_exists( __NAMESPACE__ . '\\Teca_Elementor_Events_Widget' ) ) {
			return;
		}

		$widgets_manager->register( new Teca_Elementor_Events_Widget() );
	}

	/**
	 * Register GS Plugins Elementor category when missing.
	 *
	 * @param \Elementor\Elements_Manager $elements_manager Elements manager.
	 * @return void
	 */
	public function add_elementor_widget_category( $elements_manager ) {
		$categories = $elements_manager->get_categories();

		if ( isset( $categories['gs-plugins'] ) ) {
			return;
		}

		$elements_manager->add_category(
			'gs-plugins',
			[
				'title' => esc_html__( 'GS Plugins', 'the-events-calendar-addon' ),
				'icon'  => 'fa fa-plug',
			]
		);
	}

	/**
	 * Enqueue Elementor panel editor scripts.
	 *
	 * @return void
	 */
	public function enqueue_elementor_editor_scripts() {
		wp_enqueue_script(
			'gs-teca-elementor-editor',
			GS_TECA_PLUGIN_URI . 'includes/integration/elementor/assets/js/teca-elementor-editor.js',
			[ 'jquery', 'elementor-editor' ],
			GS_TECA_VERSION,
			true
		);

		wp_localize_script(
			'gs-teca-elementor-editor',
			'gs_teca_elementor',
			[
				'editShortcodeBase' => admin_url( 'admin.php?page=gs-the-events-calendar-addon#/shortcode/' ),
			]
		);
	}

	/**
	 * Enqueue Elementor panel editor styles.
	 *
	 * @return void
	 */
	public function enqueue_elementor_editor_styles() {
		wp_enqueue_style(
			'gs-teca-elementor-editor',
			GS_TECA_PLUGIN_URI . 'includes/integration/elementor/assets/css/teca-elementor-editor.css',
			[],
			GS_TECA_VERSION
		);

		$icon = GS_TECA_PLUGIN_URI . 'assets/img/events.svg';

		wp_add_inline_style(
			'gs-teca-elementor-editor',
			sprintf(
				'body #elementor-panel-elements-wrapper .icon .teca-events{background:url(%s) no-repeat center center;background-size:contain;height:29px;display:block;}',
				esc_url_raw( $icon )
			)
		);
	}

	/**
	 * Enqueue TECA public styles for Elementor preview.
	 *
	 * @return void
	 */
	public function enqueue_elementor_preview_styles() {
		if ( ! function_exists( __NAMESPACE__ . '\\plugin' ) ) {
			return;
		}

		plugin()->scripts->wp_enqueue_style_all( 'public', [ 'gs-teca-divi-public' ] );

		if ( function_exists( __NAMESPACE__ . '\\gsTecaAssetGenerator' ) ) {
			gsTecaAssetGenerator()->enqueue_prefs_custom_css();
			gsTecaAssetGenerator()->enqueue_localize_script();
		}
	}

	/**
	 * Enqueue TECA public scripts for Elementor preview.
	 *
	 * @return void
	 */
	public function enqueue_elementor_preview_scripts() {
		if ( ! function_exists( __NAMESPACE__ . '\\plugin' ) ) {
			return;
		}

		plugin()->scripts->wp_enqueue_script_all( 'public' );

		wp_enqueue_script(
			'gs-teca-elementor-preview',
			GS_TECA_PLUGIN_URI . 'includes/integration/elementor/assets/js/teca-elementor-preview.js',
			[ 'jquery', 'elementor-frontend' ],
			GS_TECA_VERSION,
			true
		);
	}
}
