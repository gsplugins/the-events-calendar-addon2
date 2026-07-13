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
 * tagDiv Composer integration bootstrap.
 */
class Integration_Tagdiv {

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
	 * Register tagDiv hooks.
	 */
	public function __construct() {
		add_action( 'td_global_after', [ $this, 'register_block' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_composer_assets' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_editor_styles' ] );
	}

	/**
	 * Whether tagDiv Composer APIs are available.
	 *
	 * @return bool
	 */
	public function is_tagdiv_available() {
		return class_exists( 'td_api_block' ) && class_exists( 'td_block' );
	}

	/**
	 * Register the TECA Events tagDiv block.
	 *
	 * @return void
	 */
	public function register_block() {
		if ( ! $this->is_tagdiv_available() ) {
			return;
		}

		$block_file = GS_TECA_PLUGIN_DIR . 'includes/integration/tagdiv/elements/class-teca-tagdiv-events-element.php';

		if ( ! file_exists( $block_file ) ) {
			return;
		}

		\td_api_block::add(
			'td_teca_tagdiv_events',
			[
				'map_in_visual_composer' => false,
				'map_in_td_composer'     => true,
				'name'                   => esc_html__( 'TECA Events', 'the-events-calendar-addon' ),
				'base'                   => 'td_teca_tagdiv_events',
				'class'                  => 'td_teca_tagdiv_events',
				'controls'               => 'full',
				'category'               => 'Content',
				'tdc_category'           => 'Blocks',
				'file'                   => $block_file,
				'params'                 => [
					[
						'param_name' => 'shortcode_id',
						'type'       => 'dropdown',
						'value'      => $this->get_shortcode_dropdown_values(),
						'heading'    => esc_html__( 'Select TECA Shortcode', 'the-events-calendar-addon' ),
					],
				],
			]
		);
	}

	/**
	 * Enqueue TECA assets inside tagDiv Composer iframe.
	 *
	 * @return void
	 */
	public function enqueue_composer_assets() {
		global $load_in_composer_iframe;

		if ( empty( $load_in_composer_iframe ) ) {
			return;
		}

		if ( ! function_exists( __NAMESPACE__ . '\\plugin' ) ) {
			return;
		}

		plugin()->scripts->wp_enqueue_style_all( 'public', [ 'gs-teca-divi-public' ] );
		plugin()->scripts->wp_enqueue_script_all( 'public' );

		if ( function_exists( __NAMESPACE__ . '\\gsTecaAssetGenerator' ) ) {
			gsTecaAssetGenerator()->enqueue_prefs_custom_css();
			gsTecaAssetGenerator()->enqueue_localize_script();
		}

		wp_enqueue_script(
			'gs-teca-tagdiv-editor',
			GS_TECA_PLUGIN_URI . 'includes/integration/tagdiv/assets/js/teca-tagdiv-editor.js',
			[ 'jquery' ],
			GS_TECA_VERSION,
			true
		);
	}

	/**
	 * Enqueue tagDiv Composer panel icon styles.
	 *
	 * @return void
	 */
	public function enqueue_editor_styles() {
		if ( ! $this->is_tagdiv_available() || ! is_admin() ) {
			return;
		}

		wp_enqueue_style(
			'gs-teca-tagdiv-editor',
			GS_TECA_PLUGIN_URI . 'includes/integration/tagdiv/assets/css/teca-tagdiv-editor.css',
			[],
			GS_TECA_VERSION
		);

		$icon = GS_TECA_PLUGIN_URI . 'assets/img/events.svg';

		wp_add_inline_style(
			'gs-teca-tagdiv-editor',
			sprintf(
				".tdc-element-ico.tdc-ico-td_teca_tagdiv_events{background-image:url('%s');background-position:center center;background-size:contain;}",
				esc_url_raw( $icon )
			)
		);

		if ( wp_style_is( 'td_composer_edit', 'registered' ) || wp_style_is( 'td_composer_edit', 'enqueued' ) ) {
			wp_add_inline_style(
				'td_composer_edit',
				sprintf(
					".tdc-element-ico.tdc-ico-td_teca_tagdiv_events{background-image:url('%s');background-position:center center;background-size:contain;}",
					esc_url_raw( $icon )
				)
			);
		}
	}

	/**
	 * Dropdown values for tagDiv block settings.
	 *
	 * @return array<string|int, string|int>
	 */
	protected function get_shortcode_dropdown_values() {
		$values = teca_get_saved_shortcodes_for_tagdiv();

		if ( ! empty( $values ) ) {
			return $values;
		}

		return [
			esc_html__( 'No TECA shortcode found', 'the-events-calendar-addon' ) => '',
		];
	}
}
