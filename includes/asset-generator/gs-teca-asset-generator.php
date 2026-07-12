<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally kept for backward compatibility.
namespace GS_TECA;
use GSPLUGINS\GS_Asset_Generator_Base;

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class GS_Teca_Asset_Generator extends GS_Asset_Generator_Base {

	private static $instance = null;

	public static function getInstance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function get_assets_key() {
		return 'gs-teca';
	}

	public function generateStyle( $selector, $selector_divi, $targets, $prop, $value ) {
		
		if ( $value === '' || $value === null ) {
			return;
		}
		
		$selectors = [];

		if ( ! empty($targets) ) {
			if ( gettype($targets) !== 'array' ) $targets = [$targets];
		}

		// if ( is_divi_active() && !empty($selector_divi) ) {
		// 	if ( empty($targets) ) {
		// 		$selectors[] = $selector_divi;
		// 	} else {
		// 		foreach ( $targets as $target ) $selectors[] = $selector_divi . $target;
		// 	}
		// }

		if ( empty($targets) ) {
			$selectors[] = $selector;
		} else {
			foreach ( $targets as $target ) $selectors[] = $selector . $target;
		}

		echo esc_html( wp_strip_all_tags( sprintf( '%s{%s:%s}', join(',', $selectors), $prop, $value ) ) );
	}

	protected function get_shortcode_css_selector( $shortcode_id ) {
		if ( is_numeric( $shortcode_id ) ) {
			return '#gs_teca_area_' . absint( $shortcode_id );
		}

		return '#gs_teca_area_' . sanitize_key( (string) $shortcode_id );
	}

	public function generateCustomCss( $settings, $shortCodeId ) {

		ob_start();

		$settings = teca_prepare_typography_settings( (array) $settings );
		$settings = teca_prepare_color_settings( (array) $settings );
		$settings = teca_prepare_popup_detail_settings( (array) $settings );

		$selector      = $this->get_shortcode_css_selector( $shortCodeId );
		$selector_divi = '#et-boc .et-l div ' . $selector;

		echo wp_strip_all_tags( teca_render_typography_preset_scoped_css( $settings, $selector ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS is generated from sanitized settings and stripped of HTML tags.
		echo wp_strip_all_tags( teca_render_typography_scoped_css( $settings, $selector ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS is generated from sanitized settings and stripped of HTML tags.
		echo wp_strip_all_tags( teca_render_color_scoped_css( $settings, $selector ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS is generated from sanitized settings and stripped of HTML tags.
		echo wp_strip_all_tags( teca_render_popup_detail_typography_scoped_css( $settings, $shortCodeId ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS is generated from sanitized settings and stripped of HTML tags.
		echo wp_strip_all_tags( teca_render_popup_detail_color_scoped_css( $settings, $shortCodeId ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS is generated from sanitized settings and stripped of HTML tags.

		$gradient_1 = $settings['custom_gradient_bg_1'] ?? '';
		$gradient_2 = $settings['custom_gradient_bg_2'] ?? '';
		$linear_range = $settings['linear_range'] ?? '';

		if ( $gradient_1 ) {
			$this->generateStyle( $selector, $selector_divi, ' .gspg-cat-gradient-bg-color a', '--gspg-cat-gradient-color-1', $gradient_1 );
		}

		if ( $gradient_2 ) {
			$this->generateStyle( $selector, $selector_divi, ' .gspg-cat-gradient-bg-color a', '--gspg-cat-gradient-color-2', $gradient_2 );
		}

		if ( $linear_range ) {
			$this->generateStyle( $selector, $selector_divi, ' .gspg-cat-gradient-bg-color a', '--gspg-cat-gradient-linear-range', $linear_range . 'deg' );
		}

		if (!empty($settings['item_bg_color'])) {
			$this->generateStyle($selector, $selector_divi,  ' .gspg-cat-bg-color a', '--gspg-cat-bg-color', $settings['item_bg_color']);
		}


		

		return ob_get_clean();
	}

	public function get_view_asset_dependencies( Array $settings ) {
		$scripts = [];
		$styles  = [];

		if ( empty( $settings ) || ! empty( $settings['is_preview'] ) ) {
			return [ 'scripts' => $scripts, 'styles' => $styles ];
		}

		$view_type = $settings['view_type'] ?? 'grid';

		if ( $view_type === 'carousel' ) {
			$scripts[] = 'gs-swiper';
			$styles[]  = 'gs-swiper';
		}

		if ( $view_type === 'ticker' ) {
			$scripts[] = 'gs-carousel-ticker';
		}

		if ( $view_type === 'masonry' ) {
			$scripts[] = 'gs-isotope';
		}

		if ( $view_type === 'filter' && ( $settings['gs_teca_filter_type'] ?? 'normal-filter' ) === 'normal-filter' ) {
			$scripts[] = 'gs-isotope';
		}

		return [
			'scripts' => array_values( array_unique( $scripts ) ),
			'styles'  => array_values( array_unique( $styles ) ),
		];
	}

	public function enqueue_view_assets( Array $settings ) {
		if ( teca_is_calendar_view_type( $settings['view_type'] ?? '' ) ) {
			Calendar_Renderer::mark_enqueue_assets();
		}

		$deps = $this->get_view_asset_dependencies( $settings );

		foreach ( $deps['styles'] as $handle ) {
			if ( ! wp_style_is( $handle, 'registered' ) ) {
				continue;
			}
			plugin()->scripts->add_dependency_styles( 'gs-teca-public', [ $handle ] );
			wp_enqueue_style( $handle );
		}

		foreach ( $deps['scripts'] as $handle ) {
			if ( ! wp_script_is( $handle, 'registered' ) ) {
				continue;
			}
			plugin()->scripts->add_dependency_scripts( 'gs-teca-public', [ $handle ] );
			wp_enqueue_script( $handle );
		}
	}

	public function generate_assets_data( Array $settings ) {

		if ( empty($settings) || !empty($settings['is_preview']) ) return;
		
		$this->add_item_in_asset_list( 'styles', 'gs-teca-public', [ 'gs-bootstrap-grid' ] );
		$this->add_item_in_asset_list( 'scripts', 'gs-teca-public', [ 'jquery', 'gs-images-loaded' ] );

		$view_deps = $this->get_view_asset_dependencies( $settings );

		if ( ! empty( $view_deps['scripts'] ) ) {
			$this->add_item_in_asset_list( 'scripts', 'gs-teca-public', $view_deps['scripts'] );
		}

		if ( ! empty( $view_deps['styles'] ) ) {
			$this->add_item_in_asset_list( 'styles', 'gs-teca-public', $view_deps['styles'] );
		}

		// Hooked for Pro if availabel
		do_action( 'gsteca_assets_data_generated', $settings );

		// if ( is_divi_active() ) {
		// 	$this->add_item_in_asset_list( 'styles', 'gswps-public-divi', ['gswps-public'] );
		// }

		$fonts = $this->get_fonts_from_settings( $settings );

		if ( ! empty( $fonts ) ) {
			$this->add_item_in_asset_list( 'fonts', 'google-fonts', $fonts );
		}

		$css = $this->get_shortcode_custom_css( $settings );

		if ( !empty($css) ) {
			$this->add_item_in_asset_list( 'styles', 'inline', minimize_css_simple($css) );
		}
	}

	public function get_fonts_from_settings( $settings ) {

		$typography_keys = array( 'title_typography' );

		if ( is_pro_active_and_valid() ) {
			$typography_keys = array_merge(
				$typography_keys,
				array(
					'cat_typography',
					'tag_typography',
					'org_typography',
					'date_typography',
					'details_typography',
					'venue_typography',
					'view_details_button_typography',
					'google_calendar_button_typography',
				)
			);
		}

		if ( teca_should_render_popup_detail_css( (array) $settings ) ) {
			$typography_keys = array_merge( $typography_keys, teca_get_popup_detail_typography_setting_keys() );
		}

		$fonts = array();

		$title_font = teca_get_resolved_title_font_family_from_settings( (array) $settings );
		if ( '' !== $title_font ) {
			$fonts[] = $title_font;
		}

		foreach ( $typography_keys as $key ) {
			if ( 0 === strpos( $key, 'popup_detail_' ) ) {
				$group = array_search( $key, teca_get_popup_detail_typography_group_map(), true );
				if ( false === $group || ! teca_is_popup_detail_typography_override_active( $settings, $group ) ) {
					continue;
				}
			} elseif ( ! teca_should_apply_typography( $settings, $key ) ) {
				continue;
			}

			$setting = (array) ( $settings[ $key ] ?? array() );
			if ( ! empty( $setting['getFonts'] ) ) {
				$fonts[] = $setting['getFonts'];
			}
		}

		return array_unique( $fonts );
	}

	public function load_google_fonts( $google_fonts ) {

		if ( empty( $google_fonts ) || ! is_array( $google_fonts ) ) {
			return;
		}

		if ( class_exists( 'GS_TECA\\GS_TECA_Shortcode_Fonts_Loader' ) ) {
			$loader = GS_TECA_Shortcode_Fonts_Loader::get_instance();
			foreach ( $google_fonts as $font ) {
				if ( ! empty( $font ) ) {
					$loader->enqueue_font( $font );
				}
			}
			return;
		}

		$google_only = array();
		foreach ( $google_fonts as $font ) {
			if ( empty( $font ) ) {
				continue;
			}
			if ( class_exists( 'GS_TECA\\GS_TECA_Shortcode_Fonts' ) && GS_TECA_Shortcode_Fonts::get_font_type( $font ) !== GS_TECA_Shortcode_Fonts::GOOGLE ) {
				continue;
			}
			$google_only[] = str_replace( ' ', '+', $font );
		}

		if ( empty( $google_only ) ) {
			return;
		}

		$google_fonts_url = 'https://fonts.googleapis.com/css2?' . implode( '&', array_map( fn( $f ) => "family=$f", $google_only ) ) . '&display=swap';
		$url              = set_url_scheme( $google_fonts_url, 'https' );

		if ( $url ) {
			wp_enqueue_style( 'gs-teca-google-fonts-' . md5( $url ), $url, array(), GS_TECA_VERSION );
		}
	}

	protected function typography_has_values( $setting ) {
		return teca_typography_has_values( (array) $setting );
	}

	protected function format_font_family_value( $font ) {
		if ( $font === '' || $font === null ) {
			return '';
		}
		$font = trim( (string) $font );
		if ( strpos( $font, ',' ) !== false ) {
			return $font;
		}
		return '"' . $font . '", sans-serif';
	}

	protected function format_size_value( $size ) {
		if ( $size === '' || $size === null ) {
			return '';
		}
		if ( is_numeric( $size ) ) {
			return $size . 'px';
		}
		return (string) $size;
	}

	protected function format_letter_spacing_value( $spacing ) {
		if ( $spacing === '' || $spacing === null ) {
			return '';
		}
		if ( is_numeric( $spacing ) ) {
			return $spacing . 'px';
		}
		return (string) $spacing;
	}

	protected function maybe_apply_typography_variables( $settings, $selector, $selector_divi, $targets, $typography_key, $prefix ) {
		if ( ! teca_should_apply_typography( $settings, $typography_key ) ) {
			return;
		}

		$group = array_search( $typography_key, teca_get_typography_group_map(), true );
		$field_custom = false !== $group
			? teca_get_typography_field_custom_flags( $settings, $group )
			: null;

		$this->apply_typography_variables(
			$selector,
			$selector_divi,
			$targets,
			$prefix,
			(array) ( $settings[ $typography_key ] ?? array() ),
			$field_custom
		);
	}

	protected function apply_typography_variables( $selector, $selector_divi, $targets, $prefix, $setting, $field_custom = null ) {

		if ( ! $this->typography_has_values( $setting ) ) {
			return;
		}

		$typo       = teca_normalize_typography_value( (array) $setting );
		$var_prefix = '--gsteca-event-' . $prefix;

		$maybe = static function( $field, $prop, $value ) use ( $field_custom, $selector, $selector_divi, $targets, $var_prefix ) {
			if ( is_array( $field_custom ) && empty( $field_custom[ $field ] ) ) {
				return;
			}

			if ( '' === $value || null === $value ) {
				return;
			}

			$this->generateStyle( $selector, $selector_divi, $targets, $var_prefix . $prop, $value );
		};

		$maybe( 'font_style', '-style', $typo['font_style'] );
		$maybe( 'text_decoration', '-text-decoration', $typo['text_decoration'] );
		$maybe( 'line_height', '-lineHeight', $typo['line_height'] );
		$maybe( 'letter_spacing', '-letterSpacing', teca_format_typography_letter_spacing_value( $typo['letter_spacing'] ) );
		$maybe( 'font_family', '-font-family', teca_format_typography_font_family( $typo['font_family'] ) );
		$maybe( 'font_weight', '-font-weight', teca_format_typography_weight_value( $typo['font_weight'] ) );
		$maybe( 'text_transform', '-text-transform', $typo['text_transform'] );
		$maybe( 'font_size', '-font-size', teca_format_typography_size_value( $typo['font_size'] ) );
	}

	public function enqueue_localize_script(){
		$ajax_url = admin_url('admin-ajax.php');
		$nonce = wp_create_nonce('gs_teca_user_action');
		wp_localize_script( 'gs-teca-public', 'GSTecaData', array( 'ajaxUrl' => $ajax_url, 'nonce' => $nonce ) );
	}

	public function enqueue_plugin_assets( $main_post_id, $assets = [] ) {

		if ( empty($assets) || empty($assets['styles']) || empty($assets['scripts']) ) return;

		foreach ( $assets['styles'] as $asset => $data ) {
			if ( $asset == 'inline' ) {
				if ( !empty($data) ) wp_add_inline_style( 'gs-teca-public', $data );
			} else {
				plugin()->scripts->add_dependency_styles( $asset, $data );
			}
		}
		
		foreach ( $assets['scripts'] as $asset => $data ) {
			if ( $asset == 'inline' ) {
				if ( !empty($data) ) wp_add_inline_script( 'gs-teca-public', $data );
			} else {
				plugin()->scripts->add_dependency_scripts( $asset, $data );
				foreach ( (array) $data as $dep ) {
					if ( wp_script_is( $dep, 'registered' ) ) {
						wp_enqueue_script( $dep );
					}
				}
			}
		}

		$this->load_google_fonts( $assets['fonts']['google-fonts'] ?? array() );

		wp_enqueue_style( 'gs-teca-public' );
		wp_enqueue_script( 'gs-teca-public' );
		$this->enqueue_localize_script();

		// if ( is_divi_active() ) {
		// 	// wp_enqueue_style( 'gswps-public-divi' );
		// }

		$this->enqueue_prefs_custom_css();
	}

	public function is_builder_preview() {
		if ( function_exists( __NAMESPACE__ . '\\plugin' ) && plugin()->integrations ) {
			return plugin()->integrations->is_builder_preview();
		}

		return false;
	}

	public function enqueue_builder_preview_assets() {
		plugin()->scripts->wp_enqueue_style_all( 'public' );
		plugin()->scripts->wp_enqueue_script_all( 'public' );
		$this->enqueue_localize_script();
		$this->enqueue_prefs_custom_css();
	}

	public function maybe_force_enqueue_assets( Array $settings ) {
		if ( teca_is_calendar_view_type( $settings['view_type'] ?? '' ) ) {
			Calendar_Renderer::mark_enqueue_assets();
		}

		plugin()->scripts->wp_enqueue_style_all( 'public' );
		plugin()->scripts->wp_enqueue_script_all( 'public' );

		$this->enqueue_localize_script();

		$fonts = $this->get_fonts_from_settings( $settings );
		$this->load_google_fonts( $fonts );

		// Shortcode Generated CSS
		$css = $this->get_shortcode_custom_css( $settings );
		$this->wp_add_inline_style( $css );
		
		// Prefs Custom CSS
		$this->enqueue_prefs_custom_css();
	}

	public function wp_add_inline_style( $css ) {
		if ( !empty($css) ) $css = minimize_css_simple($css);
		if ( !empty($css) ) wp_add_inline_style( 'gs-teca-public', wp_strip_all_tags($css) );
	}

	public function get_prefs_custom_css() {
		$prefs = plugin()->builder->_get_shortcode_pref( false );
		if ( empty($prefs['gs_teca_custom_css']) ) return '';
		return $prefs['gs_teca_custom_css'];
	}

	public function enqueue_prefs_custom_css() {
		$this->wp_add_inline_style( $this->get_prefs_custom_css() );
	}

	public function get_shortcode_custom_css( $settings ) {
		$shortcode_id = $settings['id'] ?? 0;

		if ( is_numeric( $shortcode_id ) ) {
			$shortcode_id = absint( $shortcode_id );
		} else {
			$shortcode_id = sanitize_key( (string) $shortcode_id );
		}

		return $this->generateCustomCss( $settings, $shortcode_id );
	}
}

if ( ! function_exists( 'gsTecaAssetGenerator' ) ) {
	function gsTecaAssetGenerator() {
		return GS_Teca_Asset_Generator::getInstance(); 
	}
}

// Must inilialized for the hooks
gsTecaAssetGenerator();

