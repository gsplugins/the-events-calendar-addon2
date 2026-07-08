<?php

namespace GS_TECA;

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Integrations {

	/**
	 * Boot page builder integrations.
	 */
	public function __construct() {
		if ( apply_filters( 'gs_teca_integration_gutenberg', true ) ) {
			$this->integration_with_gutenberg();
		}

		if ( apply_filters( 'gs_teca_integration_elementor', true ) ) {
			$this->integration_with_elementor();
		}

		if ( apply_filters( 'gs_teca_integration_divi', true ) ) {
			$this->integration_with_divi();
		}

		if ( apply_filters( 'gs_teca_integration_wpbakery', true ) ) {
			$this->integration_with_wpbakery();
		}

		if ( apply_filters( 'gs_teca_integration_tagdiv', true ) ) {
			$this->integration_with_tagdiv();
		}

		if ( apply_filters( 'gs_teca_integration_oxygen', true ) ) {
			$this->integration_with_oxygen();
		}

		if ( apply_filters( 'gs_teca_integration_ux_builder', true ) ) {
			$this->integration_with_ux_builder();
		}

		if ( apply_filters( 'gs_teca_integration_beaver', true ) ) {
			$this->integration_with_beaver();
		}
	}

	/**
	 * Load Gutenberg block support.
	 *
	 * @return void
	 */
	public function integration_with_gutenberg() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		require_once GS_TECA_PLUGIN_DIR . 'includes/integration/gutenberg/class-teca-gutenberg.php';
		Integration_Gutenberg::get_instance();
	}

	/**
	 * Load Elementor widget support when Elementor is available.
	 *
	 * @return void
	 */
	public function integration_with_elementor() {
		if ( did_action( 'elementor/loaded' ) ) {
			$this->boot_elementor_integration();
			return;
		}

		add_action( 'elementor/loaded', [ $this, 'boot_elementor_integration' ] );
	}

	/**
	 * Bootstrap Elementor integration after Elementor loads.
	 *
	 * @return void
	 */
	public function boot_elementor_integration() {
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return;
		}

		require_once GS_TECA_PLUGIN_DIR . 'includes/integration/elementor/class-teca-elementor.php';

		if ( class_exists( __NAMESPACE__ . '\\Integration_Elementor' ) ) {
			Integration_Elementor::get_instance();
		}
	}

	/**
	 * Load Divi module support.
	 *
	 * @return void
	 */
	public function integration_with_divi() {
		require_once GS_TECA_PLUGIN_DIR . 'includes/integration/divi/class-teca-divi.php';

		if ( class_exists( __NAMESPACE__ . '\\Integration_Divi' ) ) {
			Integration_Divi::get_instance();
		}
	}

	/**
	 * Load WPBakery element support.
	 *
	 * @return void
	 */
	public function integration_with_wpbakery() {
		require_once GS_TECA_PLUGIN_DIR . 'includes/integration/wpbakery/class-teca-wpbakery.php';

		if ( class_exists( __NAMESPACE__ . '\\Integration_WPBakery' ) ) {
			Integration_WPBakery::get_instance();
		}
	}

	/**
	 * Load tagDiv Composer block support.
	 *
	 * @return void
	 */
	public function integration_with_tagdiv() {
		require_once GS_TECA_PLUGIN_DIR . 'includes/integration/tagdiv/class-teca-tagdiv.php';

		if ( class_exists( __NAMESPACE__ . '\\Integration_Tagdiv' ) ) {
			Integration_Tagdiv::get_instance();
		}
	}

	/**
	 * Load Oxygen Builder element support.
	 *
	 * @return void
	 */
	public function integration_with_oxygen() {
		require_once GS_TECA_PLUGIN_DIR . 'includes/integration/oxygen/class-teca-oxygen.php';

		if ( class_exists( __NAMESPACE__ . '\\Integration_Oxygen' ) ) {
			Integration_Oxygen::get_instance();
		}
	}

	/**
	 * Load Flatsome UX Builder element support.
	 *
	 * @return void
	 */
	public function integration_with_ux_builder() {
		if ( ! $this->is_flatsome_ux_builder_available() ) {
			return;
		}

		require_once GS_TECA_PLUGIN_DIR . 'includes/integration/ux-builder/class-teca-ux-builder.php';

		if ( class_exists( __NAMESPACE__ . '\\Integration_UX_Builder' ) ) {
			Integration_UX_Builder::get_instance();
		}
	}

	/**
	 * Whether Flatsome UX Builder is available in the current environment.
	 *
	 * @return bool
	 */
	public function is_flatsome_ux_builder_available() {
		if ( function_exists( 'add_ux_builder_shortcode' ) ) {
			return true;
		}

		if ( defined( 'UX_BUILDER_VERSION' ) || defined( 'FLATSOME_VERSION' ) ) {
			return true;
		}

		$theme = wp_get_theme();

		return 'flatsome' === strtolower( $theme->get_template() );
	}

	/**
	 * Whether Beaver Builder is booted.
	 *
	 * @var bool
	 */
	private static $beaver_booted = false;

	/**
	 * Load Beaver Builder module support.
	 *
	 * @return void
	 */
	public function integration_with_beaver() {
		add_action( 'init', [ $this, 'boot_beaver_integration' ], 10 );
		add_action( 'fl_builder_loaded', [ $this, 'boot_beaver_integration' ] );
	}

	/**
	 * Bootstrap Beaver Builder integration after Beaver Builder loads.
	 *
	 * @return void
	 */
	public function boot_beaver_integration() {
		if ( self::$beaver_booted ) {
			return;
		}

		if ( ! class_exists( 'FLBuilder' ) || ! class_exists( 'FLBuilderModule' ) ) {
			return;
		}

		self::$beaver_booted = true;

		require_once GS_TECA_PLUGIN_DIR . 'includes/integration/beaver/class-teca-beaver.php';

		if ( class_exists( __NAMESPACE__ . '\\Integration_Beaver' ) ) {
			Integration_Beaver::get_instance();
		}
	}

	/**
	 * Whether the current request is a page builder preview context.
	 *
	 * @return bool
	 */
	public function is_builder_preview() {
		if ( ! empty( $_GET['vc_editable'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return true;
		}

		if ( ( ! empty( $_GET['action'] ) && 'elementor' === $_GET['action'] ) || ( ! empty( $_POST['action'] ) && 'elementor_ajax' === $_POST['action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing
			return true;
		}

		if ( ! empty( $_GET['context'] ) && 'edit' === $_GET['context'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return true;
		}

		if ( isset( $_GET['fl_builder_ui_iframe'] ) || ! empty( $_POST['fl_builder_data'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing
			return true;
		}

		if ( function_exists( 'et_core_is_fb_enabled' ) && et_core_is_fb_enabled() ) {
			return true;
		}

		global $load_in_composer_iframe;

		if ( ! empty( $load_in_composer_iframe ) ) {
			return true;
		}

		if ( ! empty( $_GET['ct_builder'] ) && ! empty( $_GET['oxygen_iframe'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return true;
		}

		if ( ! empty( $_GET['action'] ) && 'oxy_render_teca-oxygen-events' === $_GET['action'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return true;
		}

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
}
