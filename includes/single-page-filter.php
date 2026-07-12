<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally kept for backward compatibility.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Single_Page_Filter {

	public function __construct() {

		add_filter(
			'tribe_events_template_single-event.php',
			array( $this, 'override_single_event_template' ),
			150,
			1
		);

		add_filter(
			'tribe_events_template',
			array( $this, 'override_single_event_template_fallback' ),
			150,
			2
		);

		add_filter( 'body_class', array( $this, 'add_layout_body_class' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_single_page_assets' ), 20 );
	}

	/**
	 * Resolve the selected single page style key for the current request.
	 *
	 * @return string
	 */
	protected function resolve_style_key() {
		$settings = teca_get_single_page_settings_for_current_event();

		/**
		 * Back-compat filter for raw saved style value.
		 *
		 * @param string $raw_layout Saved style value.
		 */
		$raw_layout = isset( $settings['single_page_style'] )
			? teca_extract_single_page_setting_value( $settings['single_page_style'], 'default' )
			: 'default';

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook name is kept for backward compatibility.
		$raw_layout = apply_filters( 'gs_teca_single_page_style', $raw_layout );

		if ( is_array( $settings ) && isset( $settings['single_page_style'] ) ) {
			$settings['single_page_style'] = $raw_layout;
		} else {
			$settings = array( 'single_page_style' => $raw_layout );
		}

		return teca_get_single_page_style_key( $settings );
	}

	/**
	 * Override TEC single event template with the TECA unified single template.
	 *
	 * @param string $template Resolved template path.
	 * @return string
	 */
	public function override_single_event_template( $template ) {
		$style_key = $this->resolve_style_key();
		teca_set_active_single_page_style_key( $style_key );

		$file = GS_TECA_PLUGIN_DIR . 'templates/singles/gs-teca-single.php';

		if ( file_exists( $file ) ) {
			return $file;
		}

		$file = teca_get_single_page_template_path( $style_key );

		if ( $file && file_exists( $file ) ) {
			return $file;
		}

		return $template;
	}

	/**
	 * Fallback override when theme template hierarchy resolves before the final filter.
	 *
	 * @param string $file     Resolved template path.
	 * @param string $template Template slug.
	 * @return string
	 */
	public function override_single_event_template_fallback( $file, $template ) {
		if ( 'single-event.php' !== $template ) {
			return $file;
		}

		return $this->override_single_event_template( $file );
	}

	/**
	 * Add layout body classes for single event pages.
	 *
	 * @param array $classes Body classes.
	 * @return array
	 */
	public function add_layout_body_class( $classes ) {
		if ( ! is_singular( 'tribe_events' ) ) {
			return $classes;
		}

		$style_key = $this->resolve_style_key();
		teca_set_active_single_page_style_key( $style_key );

		$classes[] = 'gs-containeer gs-single-container';
		$classes[] = teca_get_single_page_body_class( $style_key );

		return $classes;
	}

	/**
	 * Ensure single page styles are available on event detail pages.
	 *
	 * @return void
	 */
	public function enqueue_single_page_assets() {
		if ( ! is_singular( 'tribe_events' ) ) {
			return;
		}

		wp_enqueue_style( 'gs-teca-public' );
		wp_enqueue_script( 'gs-teca-public' );
	}
}
