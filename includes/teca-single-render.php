<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally kept for backward compatibility.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Safely include a single page layout template.
 *
 * @param string|null $style_key Optional style key. Uses saved layout settings when null.
 * @return void
 */
function teca_include_single_layout_template( $style_key = null ) {
	if ( null === $style_key ) {
		$style_key = teca_get_single_page_style_key();
	} else {
		$style_key = teca_normalize_single_page_style_key( $style_key );
	}

	teca_set_active_single_page_style_key( $style_key );

	// Explicit include-scope variable for gs-teca-single-layout.php.
	$teca_single_page_style_key = $style_key;

	$layout = GS_TECA_PLUGIN_DIR . 'templates/singles/gs-teca-single-layout.php';

	if ( class_exists( __NAMESPACE__ . '\\Template_Loader' ) ) {
		$located = Template_Loader::locate_template( 'singles/gs-teca-single-layout.php' );

		if ( ! is_wp_error( $located ) && ! empty( $located ) ) {
			$layout = $located;
		}
	}

	if ( ! file_exists( $layout ) ) {
		return;
	}

	include $layout;
}

/**
 * Store the active single page style key for the current request.
 *
 * @param string $style_key Style key.
 * @return void
 */
function teca_set_active_single_page_style_key( $style_key ) {
	$GLOBALS['teca_active_single_page_style_key'] = teca_normalize_single_page_style_key( $style_key );
}

/**
 * Get the active single page style key for the current request.
 *
 * @return string
 */
function teca_get_active_single_page_style_key() {
	if ( ! empty( $GLOBALS['teca_active_single_page_style_key'] ) ) {
		return teca_normalize_single_page_style_key( $GLOBALS['teca_active_single_page_style_key'] );
	}

	return teca_get_single_page_style_key();
}

/**
 * Normalize any saved single page style value to a canonical style key.
 *
 * @param mixed $raw Raw style value or settings array.
 * @return string
 */
function teca_normalize_single_page_style_key( $raw ) {
	if ( is_array( $raw ) ) {
		if ( isset( $raw['single_page_style'] ) ) {
			$raw = $raw['single_page_style'];
		} elseif ( isset( $raw['single_style'] ) ) {
			$raw = $raw['single_style'];
		} elseif ( isset( $raw['single_template'] ) ) {
			$raw = $raw['single_template'];
		} elseif ( isset( $raw['value'] ) ) {
			$raw = $raw['value'];
		} else {
			return 'default';
		}
	}

	if ( is_object( $raw ) ) {
		if ( isset( $raw->single_page_style ) ) {
			$raw = $raw->single_page_style;
		} elseif ( isset( $raw->value ) ) {
			$raw = $raw->value;
		} else {
			return 'default';
		}
	}

	$raw = teca_extract_single_page_setting_value( $raw, 'default' );
	$raw = strtolower( trim( sanitize_text_field( (string) $raw ) ) );
	$raw = str_replace( array( ' ', '_' ), '-', $raw );

	$map = array(
		''                 => 'default',
		'default'          => 'default',
		'single-default'   => 'default',
		'style-default'    => 'default',
		'1'                => 'style-1',
		'2'                => 'style-2',
		'3'                => 'style-3',
		'4'                => 'style-4',
		'5'                => 'style-5',
		'style-one'        => 'style-1',
		'style-1'          => 'style-1',
		'single-style-1'   => 'style-1',
		'style1'           => 'style-1',
		'style-two'        => 'style-2',
		'style-2'          => 'style-2',
		'single-style-2'   => 'style-2',
		'style2'           => 'style-2',
		'style-three'      => 'style-3',
		'style-3'          => 'style-3',
		'single-style-3'   => 'style-3',
		'style3'           => 'style-3',
		'style-four'       => 'style-4',
		'style-4'          => 'style-4',
		'single-style-4'   => 'style-4',
		'style4'           => 'style-4',
		'style-five'       => 'style-5',
		'style-5'          => 'style-5',
		'single-style-5'   => 'style-5',
		'style5'           => 'style-5',
	);

	if ( isset( $map[ $raw ] ) ) {
		return $map[ $raw ];
	}

	return 'default';
}

/**
 * Read saved global single page layout settings for the current event request.
 *
 * TEC single events are not tied to a shortcode instance, so this uses the
 * plugin-wide layout option saved from the admin Layout screen.
 *
 * @return array
 */
function teca_get_single_page_settings_for_current_event() {
	$settings = teca_get_single_page_layout_settings();

	if ( ! is_array( $settings ) ) {
		$settings = array();
	}

	/**
	 * Filter global single page settings before render.
	 *
	 * @param array $settings Saved layout settings.
	 */
	return (array) apply_filters( 'teca_single_page_settings', $settings );
}

/**
 * Read and normalize the saved single page style key.
 *
 * @param mixed $settings Raw style value, settings array, or null to read saved layout option.
 * @return string
 */
function teca_get_single_page_style_key( $settings = null ) {
	if ( null === $settings ) {
		$settings = teca_get_single_page_settings_for_current_event();
	}

	if ( ! is_array( $settings ) ) {
		return teca_resolve_single_page_style_key_for_context(
			teca_normalize_single_page_style_key( $settings )
		);
	}

	$possible_keys = array(
		'single_page_style',
		'single_style',
		'single_template',
		'single_layout',
		'single_event_style',
	);

	foreach ( $possible_keys as $key ) {
		if ( ! isset( $settings[ $key ] ) ) {
			continue;
		}

		$raw = teca_extract_single_page_setting_value( $settings[ $key ], '' );

		if ( '' === $raw ) {
			continue;
		}

		return teca_resolve_single_page_style_key_for_context(
			teca_normalize_single_page_style_key( $raw )
		);
	}

	return 'default';
}

/**
 * Resolve style key from a single page template file path.
 *
 * @param string $template_path Absolute template path.
 * @return string|null
 */
function teca_get_single_page_style_key_from_template_path( $template_path ) {
	$basename = basename( (string) $template_path );

	$map = array(
		'gs-teca-single-default.php'  => 'default',
		'gs-teca-single.php'          => null,
		'gs-teca-single-style-one.php'  => 'style-1',
		'gs-teca-single-style-1.php'    => 'style-1',
		'gs-teca-single-style-two.php'  => 'style-2',
		'gs-teca-single-style-2.php'    => 'style-2',
		'gs-teca-single-style-three.php' => 'style-3',
		'gs-teca-single-style-3.php'    => 'style-3',
		'gs-teca-single-style-four.php' => 'style-4',
		'gs-teca-single-style-4.php'    => 'style-4',
		'gs-teca-single-style-five.php' => 'style-5',
		'gs-teca-single-style-5.php'    => 'style-5',
	);

	return array_key_exists( $basename, $map ) ? $map[ $basename ] : null;
}

/**
 * Map canonical style key to frontend root CSS class.
 *
 * @param string|null $style_key Canonical style key.
 * @return string
 */
function teca_get_single_page_style_class( $style_key = null ) {
	if ( null === $style_key ) {
		$style_key = teca_get_single_page_style_key();
	} else {
		$style_key = teca_normalize_single_page_style_key( $style_key );
	}

	$map = array(
		'default' => 'teca-single-default',
		'style-1' => 'teca-single-style-1',
		'style-2' => 'teca-single-style-2',
		'style-3' => 'teca-single-style-3',
		'style-4' => 'teca-single-style-4',
		'style-5' => 'teca-single-style-5',
	);

	return $map[ $style_key ] ?? 'teca-single-default';
}

/**
 * Map canonical style key to legacy body class.
 *
 * @param string|null $style_key Canonical style key.
 * @return string
 */
function teca_get_single_page_body_class( $style_key = null ) {
	if ( null === $style_key ) {
		$style_key = teca_get_single_page_style_key();
	} else {
		$style_key = teca_normalize_single_page_style_key( $style_key );
	}

	$map = array(
		'default' => 'gs-single-default',
		'style-1' => 'gs-single-style-one',
		'style-2' => 'gs-single-style-two',
		'style-3' => 'gs-single-style-three',
		'style-4' => 'gs-single-style-four',
		'style-5' => 'gs-single-style-five',
	);

	return $map[ $style_key ] ?? 'gs-single-default';
}

/**
 * Resolve single page wrapper template path for a style key.
 *
 * @param string|null $style_key Canonical style key.
 * @return string
 */
function teca_get_single_page_template_path( $style_key = null ) {
	if ( null === $style_key ) {
		$style_key = teca_get_single_page_style_key();
	} else {
		$style_key = teca_normalize_single_page_style_key( $style_key );
	}

	$basename_map = array(
		'default' => array( 'default' ),
		'style-1' => array( 'style-one', 'style-1' ),
		'style-2' => array( 'style-two', 'style-2' ),
		'style-3' => array( 'style-three', 'style-3' ),
		'style-4' => array( 'style-four', 'style-4' ),
		'style-5' => array( 'style-five', 'style-5' ),
	);

	$candidates = $basename_map[ $style_key ] ?? array( 'default' );

	foreach ( $candidates as $basename ) {
		$plugin_file = GS_TECA_PLUGIN_DIR . 'templates/singles/gs-teca-single-' . $basename . '.php';

		if ( file_exists( $plugin_file ) ) {
			return $plugin_file;
		}
	}

	$unified = GS_TECA_PLUGIN_DIR . 'templates/singles/gs-teca-single.php';
	if ( file_exists( $unified ) ) {
		return $unified;
	}

	return GS_TECA_PLUGIN_DIR . 'templates/singles/gs-teca-single-default.php';
}

/**
 * @param int    $post_id  Post ID.
 * @param string $taxonomy Taxonomy slug.
 * @return \WP_Term[]
 */
function teca_single_get_safe_terms( $post_id, $taxonomy ) {
	$post_id = (int) $post_id;
	$terms   = get_the_terms( $post_id, $taxonomy );

	if ( is_wp_error( $terms ) || empty( $terms ) || ! is_array( $terms ) ) {
		return array();
	}

	return $terms;
}

/**
 * @param \WP_Term|int $term Term object or ID.
 * @return string
 */
function teca_single_get_safe_term_link( $term ) {
	$link = get_term_link( $term );

	if ( is_wp_error( $link ) ) {
		return '';
	}

	return (string) $link;
}

function teca_get_single_page_sorted_fields() {
	$keys = array(
		'event_thumbnail',
		'event_cat',
		'event_title',
		'event_date',
		'event_time',
		'event_venue',
		'event_organizer',
		'event_map',
		'event_tags',
		'event_cost',
		'event_details',
		'event_website',
		'google_calendar_button',
		'event_related_section',
	);

	return plugin()->builder->get_scoped_fields( $keys );
}

function teca_get_single_page_element_order( array $sorted_fields ) {
	return array_keys( $sorted_fields );
}

function teca_is_single_page_element_visible( $element_key, array $sorted_fields ) {
	if ( ! isset( $sorted_fields[ $element_key ] ) ) {
		return false;
	}

	return Helpers::is_visible( $sorted_fields[ $element_key ] );
}

/**
 * @param array $event          Event data.
 * @param array $sorted_fields  Visibility settings.
 * @param string $style_key     Style key.
 * @param array $args           Optional args (exclude).
 * @return void
 */
function teca_render_single_page_elements( array $event, array $sorted_fields, $style_key = 'default', array $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- Element visibility exclude list, not a WP_Query exclude parameter.
			'exclude'        => array(),
			'wrap_meta_grid' => ( 'default' === $style_key ),
		)
	);

	$order = teca_get_single_page_element_order( $sorted_fields );

	if ( ! empty( $args['exclude'] ) ) {
		$order = array_values( array_diff( $order, $args['exclude'] ) );
	}

	if ( empty( $order ) ) {
		return;
	}

	$info_highlight_fields = array(
		'event_date',
		'event_venue',
		'event_organizer',
	);
	$tax_row_fields       = array(
		'event_cat',
		'event_tags',
	);
	$secondary_meta_fields = array(
		'event_time',
		'event_cost',
	);
	$in_highlight_grid     = false;
	$in_meta_grid          = false;
	$in_tax_row            = false;

	foreach ( $order as $element_key ) {
		ob_start();
		teca_render_single_page_element( $element_key, $event, $sorted_fields, $style_key );
		$markup = trim( (string) ob_get_clean() );

		if ( '' === $markup ) {
			continue;
		}

		if ( $args['wrap_meta_grid'] ) {
			$is_highlight      = in_array( $element_key, $info_highlight_fields, true );
			$is_tax_field      = in_array( $element_key, $tax_row_fields, true );
			$is_secondary_meta = in_array( $element_key, $secondary_meta_fields, true );

			if ( $is_highlight && ! $in_highlight_grid ) {
				if ( $in_meta_grid ) {
					echo '</div>';
					$in_meta_grid = false;
				}
				if ( $in_tax_row ) {
					echo '</div>';
					$in_tax_row = false;
				}
				echo '<div class="teca-single-info-highlight"><div class="teca-single-info-grid">';
				$in_highlight_grid = true;
			} elseif ( ! $is_highlight && $in_highlight_grid ) {
				echo '</div></div>';
				$in_highlight_grid = false;
			}

			if ( $is_secondary_meta && ! $in_meta_grid ) {
				if ( $in_highlight_grid ) {
					echo '</div></div>';
					$in_highlight_grid = false;
				}
				if ( $in_tax_row ) {
					echo '</div>';
					$in_tax_row = false;
				}
				echo '<div class="teca-single-meta-grid">';
				$in_meta_grid = true;
			} elseif ( ! $is_secondary_meta && $in_meta_grid ) {
				echo '</div>';
				$in_meta_grid = false;
			}

			if ( $is_tax_field && ! $in_tax_row ) {
				if ( $in_highlight_grid ) {
					echo '</div></div>';
					$in_highlight_grid = false;
				}
				if ( $in_meta_grid ) {
					echo '</div>';
					$in_meta_grid = false;
				}
				echo '<div class="teca-single-tax-row">';
				$in_tax_row = true;
			} elseif ( ! $is_tax_field && $in_tax_row ) {
				echo '</div>';
				$in_tax_row = false;
			}
		}

		echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- renderer returns escaped HTML.
	}

	if ( $args['wrap_meta_grid'] ) {
		if ( $in_highlight_grid ) {
			echo '</div></div>';
		}
		if ( $in_meta_grid ) {
			echo '</div>';
		}
		if ( $in_tax_row ) {
			echo '</div>';
		}
	}
}

/**
 * Meta/info field keys for single page default style.
 *
 * @return string[]
 */
function teca_get_single_default_meta_field_keys() {
	return array(
		'event_date',
		'event_time',
		'event_venue',
		'event_organizer',
		'event_cost',
	);
}

/**
 * Long-form content field keys for single page default style.
 *
 * @return string[]
 */
function teca_get_single_default_content_field_keys() {
	return array(
		'event_map',
		'event_details',
		'event_related_section',
	);
}

/**
 * Resolve admin label text for a single page field.
 *
 * @param string $element_key Element key.
 * @return string
 */
function teca_get_single_page_element_label( $element_key ) {
	if ( ! function_exists( __NAMESPACE__ . '\\plugin' ) || ! plugin()->builder ) {
		return '';
	}

	$defaults = plugin()->builder->get_visibility_defaults();

	if ( empty( $defaults[ $element_key ]['translation_key'] ) ) {
		return '';
	}

	$translation_key = $defaults[ $element_key ]['translation_key'];
	$strings         = plugin()->builder->get_translation_strings();

	return isset( $strings[ $translation_key ] ) ? (string) $strings[ $translation_key ] : '';
}

/**
 * Build default single page render queue with meta fields batched together.
 *
 * @param array $order Element order.
 * @return array<int, array<string, mixed>>
 */
function teca_build_single_default_render_queue( array $order ) {
	$meta_keys      = teca_get_single_default_meta_field_keys();
	$first_meta_pos = null;

	foreach ( $order as $index => $element_key ) {
		if ( in_array( $element_key, $meta_keys, true ) ) {
			$first_meta_pos = (int) $index;
			break;
		}
	}

	$meta_order  = array_values(
		array_filter(
			$order,
			static function ( $element_key ) use ( $meta_keys ) {
				return in_array( $element_key, $meta_keys, true );
			}
		)
	);
	$meta_added  = false;
	$render_queue = array();
	$action_keys  = teca_get_single_default_action_element_keys();
	$order_count  = count( $order );

	for ( $index = 0; $index < $order_count; $index++ ) {
		$element_key = $order[ $index ];

		if ( in_array( $element_key, $meta_keys, true ) ) {
			if ( ! $meta_added && null !== $first_meta_pos ) {
				$render_queue[] = array(
					'type' => 'meta_block',
					'keys' => $meta_order,
				);
				$meta_added = true;
			}
			continue;
		}

		if ( in_array( $element_key, $action_keys, true ) ) {
			$action_batch = array();

			while ( $index < $order_count && in_array( $order[ $index ], $action_keys, true ) ) {
				$action_batch[] = $order[ $index ];
				$index++;
			}

			$index--;

			$render_queue[] = array(
				'type' => 'action_batch',
				'keys' => $action_batch,
			);
			continue;
		}

		$render_queue[] = array(
			'type' => 'element',
			'key'  => $element_key,
		);
	}

	return $render_queue;
}

/**
 * Render default style info/meta card grids.
 *
 * @param array  $event         Event data.
 * @param array  $sorted_fields Visibility settings.
 * @param string $style_key     Style key.
 * @param array  $meta_order    Ordered meta field keys.
 * @return void
 */
function teca_render_single_default_meta_block( array $event, array $sorted_fields, $style_key, array $meta_order ) {
	if ( empty( $meta_order ) ) {
		return;
	}

	$info_highlight_fields = array(
		'event_date',
		'event_venue',
		'event_organizer',
	);
	$secondary_meta_fields = array(
		'event_time',
		'event_cost',
	);
	$in_highlight_grid     = false;
	$in_meta_grid          = false;

	foreach ( $meta_order as $element_key ) {
		ob_start();
		teca_render_single_page_element( $element_key, $event, $sorted_fields, $style_key );
		$markup = trim( (string) ob_get_clean() );

		if ( '' === $markup ) {
			continue;
		}

		$is_highlight      = in_array( $element_key, $info_highlight_fields, true );
		$is_secondary_meta = in_array( $element_key, $secondary_meta_fields, true );

		if ( $is_highlight && ! $in_highlight_grid ) {
			if ( $in_meta_grid ) {
				echo '</div>';
				$in_meta_grid = false;
			}
			echo '<div class="teca-single-info-highlight"><div class="teca-single-info-grid">';
			$in_highlight_grid = true;
		} elseif ( ! $is_highlight && $in_highlight_grid ) {
			echo '</div></div>';
			$in_highlight_grid = false;
		}

		if ( $is_secondary_meta && ! $in_meta_grid ) {
			if ( $in_highlight_grid ) {
				echo '</div></div>';
				$in_highlight_grid = false;
			}
			echo '<div class="teca-single-meta-grid">';
			$in_meta_grid = true;
		} elseif ( ! $is_secondary_meta && $in_meta_grid ) {
			echo '</div>';
			$in_meta_grid = false;
		}

		echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- renderer returns escaped HTML.
	}

	if ( $in_highlight_grid ) {
		echo '</div></div>';
	}
	if ( $in_meta_grid ) {
		echo '</div>';
	}
}

/**
 * Shared single page action button element keys.
 *
 * @return string[]
 */
function teca_get_single_action_element_keys() {
	return array(
		'event_website',
		'google_calendar_button',
	);
}

/**
 * Default single page action button element keys.
 *
 * @return string[]
 */
function teca_get_single_default_action_element_keys() {
	return teca_get_single_action_element_keys();
}

/**
 * Build markup for one single page action button.
 *
 * @param string $element_key   Element key.
 * @param int    $event_id      Event ID.
 * @param array  $sorted_fields Visibility settings.
 * @return string
 */
function teca_get_single_action_button_markup( $element_key, $event_id, array $sorted_fields ) {
	$event_id = (int) $event_id;

	if ( $event_id <= 0 || ! teca_is_single_page_element_visible( $element_key, $sorted_fields ) ) {
		return '';
	}

	switch ( $element_key ) {
		case 'event_website':
			$url = teca_get_event_cta_url( $event_id );

			if ( ! $url ) {
				return '';
			}

			$classes = implode( ' ', Helpers::get_visible_classes( $sorted_fields['event_website'] ?? true, 'teca-single-action-item' ) );

			return sprintf(
				'<span class="%1$s"><a class="teca-single-website-link teca-event-website-btn teca-event-button" href="%2$s" target="_blank" rel="noopener noreferrer">%3$s</a></span>',
				esc_attr( $classes ),
				esc_url( $url ),
				esc_html( teca_get_event_website_text() )
			);

		case 'google_calendar_button':
			ob_start();
			teca_render_google_calendar_button( $event_id, 'single' );
			$markup = trim( (string) ob_get_clean() );

			if ( '' === $markup ) {
				return '';
			}

			$classes = implode( ' ', Helpers::get_visible_classes( $sorted_fields['google_calendar_button'] ?? true, 'teca-single-action-item' ) );

			return sprintf(
				'<span class="%1$s">%2$s</span>',
				esc_attr( $classes ),
				$markup
			);
	}

	return '';
}

/**
 * Render single page action buttons in one row.
 *
 * @param int      $event_id         Event ID.
 * @param string[] $element_keys     Ordered action keys.
 * @param array    $sorted_fields    Visibility settings.
 * @param string   $wrapper_classes  Wrapper class string.
 * @return void
 */
function teca_render_single_action_batch( $event_id, array $element_keys, array $sorted_fields, $wrapper_classes ) {
	$buttons = array();

	foreach ( $element_keys as $element_key ) {
		$markup = teca_get_single_action_button_markup( $element_key, $event_id, $sorted_fields );

		if ( '' !== $markup ) {
			$buttons[] = $markup;
		}
	}

	if ( empty( $buttons ) ) {
		return;
	}

	echo '<div class="' . esc_attr( $wrapper_classes ) . '">';
	echo implode( '', $buttons ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped per button helper.
	echo '</div>';
}

/**
 * Render default single page action buttons in one row.
 *
 * @param int      $event_id      Event ID.
 * @param string[] $element_keys  Ordered action keys.
 * @param array    $sorted_fields Visibility settings.
 * @return void
 */
function teca_render_single_default_action_batch( $event_id, array $element_keys, array $sorted_fields ) {
	teca_render_single_action_batch(
		$event_id,
		$element_keys,
		$sorted_fields,
		'teca-single-default-actions teca-single-element teca-single-element-actions'
	);
}

/**
 * Render default single page layout with isolated content sections.
 *
 * @param array  $event         Event data.
 * @param array  $sorted_fields Visibility settings.
 * @param string $style_key     Style key.
 * @return void
 */
function teca_render_single_default_page( array $event, array $sorted_fields, $style_key = 'default' ) {
	$order = teca_get_single_page_element_order( $sorted_fields );
	$order = array_values( array_diff( $order, array( 'event_thumbnail' ) ) );

	if ( empty( $order ) ) {
		return;
	}

	$tax_row_fields = array(
		'event_cat',
		'event_tags',
	);
	$render_queue   = teca_build_single_default_render_queue( $order );
	$in_tax_row     = false;

	foreach ( $render_queue as $queue_item ) {
		if ( 'meta_block' === $queue_item['type'] ) {
			if ( $in_tax_row ) {
				echo '</div>';
				$in_tax_row = false;
			}

			teca_render_single_default_meta_block( $event, $sorted_fields, $style_key, $queue_item['keys'] );
			continue;
		}

		if ( 'action_batch' === $queue_item['type'] ) {
			if ( $in_tax_row ) {
				echo '</div>';
				$in_tax_row = false;
			}

			$event_id = teca_get_popup_event_id( $event );

			if ( $event_id ) {
				teca_render_single_default_action_batch( $event_id, $queue_item['keys'], $sorted_fields );
			}

			continue;
		}

		$element_key = $queue_item['key'];

		ob_start();
		teca_render_single_page_element( $element_key, $event, $sorted_fields, $style_key );
		$markup = trim( (string) ob_get_clean() );

		if ( '' === $markup ) {
			continue;
		}

		$is_tax_field = in_array( $element_key, $tax_row_fields, true );

		if ( $is_tax_field && ! $in_tax_row ) {
			echo '<div class="teca-single-tax-row">';
			$in_tax_row = true;
		} elseif ( ! $is_tax_field && $in_tax_row ) {
			echo '</div>';
			$in_tax_row = false;
		}

		echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- renderer returns escaped HTML.
	}

	if ( $in_tax_row ) {
		echo '</div>';
	}
}

/**
 * Open premium info card markup for default single page style.
 *
 * @param string $visible_class Element classes.
 * @return void
 */
function teca_render_single_default_info_card_open( $visible_class ) {
	echo '<div class="' . esc_attr( $visible_class ) . '">';
	echo '<span class="teca-single-meta-icon" aria-hidden="true"></span>';
	echo '<span class="teca-single-meta-content">';
}

/**
 * Close premium info card markup for default single page style.
 *
 * @return void
 */
function teca_render_single_default_info_card_close() {
	echo '</span></div>';
}

/**
 * Resolve event day number for default date card.
 *
 * @param int $event_id Event ID.
 * @return string
 */
function teca_get_single_event_day_number( $event_id ) {
	$event_id = (int) $event_id;
	if ( ! $event_id ) {
		return '';
	}

	if ( function_exists( 'tribe_get_start_date' ) ) {
		$day_number = tribe_get_start_date( $event_id, false, 'd' );
		if ( ! empty( $day_number ) ) {
			return (string) $day_number;
		}
	}

	$start_date = get_post_meta( $event_id, '_EventStartDate', true );
	if ( ! empty( $start_date ) ) {
		return gmdate( 'd', strtotime( $start_date ) );
	}

	return '';
}

function teca_render_single_page_element( $element_key, array $event, array $sorted_fields, $style_key = 'default' ) {
	$event_id = teca_get_popup_event_id( $event );

	if ( ! $event_id ) {
		return;
	}

	if ( ! teca_is_single_page_element_visible( $element_key, $sorted_fields ) ) {
		return;
	}

	if ( 'event_related_section' === $element_key ) {
		$field_classes = 'teca-single-element teca-single-element-related-events';
		$visible_class = implode( ' ', Helpers::get_visible_classes( $sorted_fields[ $element_key ], $field_classes ) );
		$layout_settings = teca_get_single_page_settings_for_current_event();

		ob_start();
		teca_render_related_events_section(
			$event_id,
			$layout_settings,
			array(
				'context'       => 'single',
				'sorted_fields' => $sorted_fields,
			)
		);
		$markup = trim( (string) ob_get_clean() );

		if ( '' === $markup ) {
			return;
		}

		echo '<div class="' . esc_attr( $visible_class ) . '">' . $markup . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- related events template escapes output.
		return;
	}

	$slug          = sanitize_html_class( str_replace( 'event_', '', $element_key ) );
	$field_classes = 'teca-single-element teca-single-element-' . $slug;
	$visible_class = implode( ' ', Helpers::get_visible_classes( $sorted_fields[ $element_key ], $field_classes ) );

	switch ( $element_key ) {
		case 'event_thumbnail':
			$image = teca_get_popup_image_data( $event, $event_id );
			if ( empty( $image['url'] ) ) {
				return;
			}
			echo '<div class="' . esc_attr( $visible_class ) . ' teca-single-image"><img src="' . esc_url( $image['url'] ) . '" alt="' . esc_attr( $image['alt'] ) . '"></div>';
			break;

		case 'event_cat':
			$terms = teca_single_get_safe_terms( $event_id, 'tribe_events_cat' );
			if ( empty( $terms ) ) {
				return;
			}
			$cat_wrap_class = ( 'default' === $style_key ) ? 'teca-single-tax-left' : '';
			echo '<div class="' . esc_attr( trim( $visible_class . ' ' . $cat_wrap_class ) ) . '"><div class="teca-event-categories gs-teca-categories">';
			foreach ( $terms as $term ) {
				$link = teca_single_get_safe_term_link( $term );
				$name = isset( $term->name ) ? (string) $term->name : '';
				if ( '' === $name ) {
					continue;
				}
				if ( $link ) {
					echo '<a class="teca-event-category gs-teca-category" href="' . esc_url( $link ) . '">' . esc_html( $name ) . '</a>';
				} else {
					echo '<span class="teca-event-category gs-teca-category">' . esc_html( $name ) . '</span>';
				}
			}
			echo '</div></div>';
			break;

		case 'event_title':
			echo '<div class="' . esc_attr( $visible_class ) . '">';
			$status_badge = teca_get_single_event_status_badge_html( $event_id );
			if ( '' !== $status_badge ) {
				echo $status_badge; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in badge builder.
			}
			echo '<h1 class="teca-single-title">' . esc_html( get_the_title( $event_id ) ) . '</h1></div>';
			break;

		case 'event_date':
			ob_start();
			Template_Loader::load_template(
				'partials/gs-teca-date.php',
				array( 'event' => $event )
			);
			$markup = trim( (string) ob_get_clean() );
			if ( '' !== $markup ) {
				if ( 'default' === $style_key ) {
					$day_number = teca_get_single_event_day_number( $event_id );
					echo '<div class="' . esc_attr( $visible_class ) . '">';
					if ( '' !== $day_number ) {
						echo '<span class="teca-single-date-number" aria-hidden="true">' . esc_html( $day_number ) . '</span>';
					}
					echo '<span class="teca-single-meta-content">';
					echo '<span class="teca-single-meta-label">' . esc_html__( 'Date', 'the-events-calendar-addon2' ) . '</span>';
					echo '<span class="teca-single-meta-value">' . $markup . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo '</span>';
					echo '</div>';
				} elseif ( 'style-3' === $style_key ) {
					$day_number = teca_get_single_style2_event_day_number( $event_id );
					echo '<div class="' . esc_attr( $visible_class ) . '">';
					if ( '' !== $day_number ) {
						echo '<span class="teca-single-date-number" aria-hidden="true">' . esc_html( $day_number ) . '</span>';
					}
					echo '<span class="teca-single-meta-content">';
					echo '<span class="teca-single-meta-label">' . esc_html__( 'Date', 'the-events-calendar-addon2' ) . '</span>';
					echo '<span class="teca-single-meta-value">' . $markup . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo '</span>';
					echo '</div>';
				} elseif ( 'style-4' === $style_key ) {
					$day_number  = teca_get_single_style2_event_day_number( $event_id );
					$month_label = teca_get_single_event_month_short( $event_id );
					echo '<div class="' . esc_attr( $visible_class . ' teca-single-style-4-date-chronicle' ) . '">';
					if ( '' !== $month_label ) {
						echo '<span class="teca-single-style-4-date-month">' . esc_html( $month_label ) . '</span>';
					}
					if ( '' !== $day_number ) {
						echo '<span class="teca-single-date-number" aria-hidden="true">' . esc_html( $day_number ) . '</span>';
					}
					echo '<span class="teca-single-meta-content">';
					echo '<span class="teca-single-meta-label">' . esc_html__( 'Date', 'the-events-calendar-addon2' ) . '</span>';
					echo '<span class="teca-single-meta-value">' . $markup . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo '</span>';
					echo '</div>';
				} elseif ( 'style-5' === $style_key ) {
					$day_number = teca_get_single_style2_event_day_number( $event_id );
					echo '<div class="' . esc_attr( $visible_class . ' teca-single-style-5-journey-step' ) . '">';
					echo '<div class="teca-single-style-5-journey-body">';
					if ( '' !== $day_number ) {
						echo '<span class="teca-single-date-number" aria-hidden="true">' . esc_html( $day_number ) . '</span>';
					}
					echo '<span class="teca-single-meta-content">';
					echo '<span class="teca-single-meta-label">' . esc_html__( 'Date', 'the-events-calendar-addon2' ) . '</span>';
					echo '<span class="teca-single-meta-value">' . $markup . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo '</span>';
					echo '</div>';
					echo '</div>';
				} else {
					echo '<div class="' . esc_attr( $visible_class ) . '"><span class="teca-single-meta-label">' . esc_html__( 'Date', 'the-events-calendar-addon2' ) . '</span>' . $markup . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			}
			break;

		case 'event_time':
			$time = teca_format_event_start_time_display( $event_id );
			if ( ! $time ) {
				return;
			}
			if ( 'style-5' === $style_key ) {
				echo '<div class="' . esc_attr( $visible_class . ' teca-single-style-5-journey-step' ) . '"><div class="teca-single-style-5-journey-body"><span class="teca-single-meta-content"><span class="teca-single-meta-label">' . esc_html__( 'Time', 'the-events-calendar-addon2' ) . '</span><span class="teca-single-meta-value">' . esc_html( $time ) . '</span></span></div></div>';
				break;
			}
			echo '<div class="' . esc_attr( $visible_class ) . '"><span class="teca-single-meta-label">' . esc_html__( 'Time', 'the-events-calendar-addon2' ) . '</span><span class="teca-single-meta-value">' . esc_html( $time ) . '</span></div>';
			break;

		case 'event_venue':
			$venue_props = array( 'title' => true, 'address' => true, 'city' => true, 'state' => true, 'zip' => true, 'country' => true );
			ob_start();
			Template_Loader::load_template(
				'partials/gs-teca-venue.php',
				array(
					'event'       => $event,
					'venue_props' => $venue_props,
				)
			);
			$markup = trim( (string) ob_get_clean() );
			if ( '' !== $markup ) {
				if ( 'default' === $style_key ) {
					teca_render_single_default_info_card_open( $visible_class );
					echo '<span class="teca-single-meta-label">' . esc_html__( 'Venue', 'the-events-calendar-addon2' ) . '</span>';
					echo '<span class="teca-single-meta-value">' . $markup . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					teca_render_single_default_info_card_close();
				} elseif ( 'style-3' === $style_key ) {
					echo '<div class="' . esc_attr( $visible_class ) . '">';
					echo '<span class="teca-single-meta-label">' . esc_html__( 'Venue', 'the-events-calendar-addon2' ) . '</span>';
					echo '<span class="teca-single-meta-value">' . $markup . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo '</div>';
				} elseif ( 'style-4' === $style_key ) {
					echo '<div class="' . esc_attr( $visible_class . ' teca-single-style-4-info-card' ) . '">';
					echo '<span class="teca-single-meta-label">' . esc_html__( 'Venue', 'the-events-calendar-addon2' ) . '</span>';
					echo '<span class="teca-single-meta-value">' . $markup . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo '</div>';
				} elseif ( 'style-5' === $style_key ) {
					echo '<div class="' . esc_attr( $visible_class . ' teca-single-style-5-journey-step teca-single-style-5-journey-step--venue' ) . '">';
					echo '<div class="teca-single-style-5-journey-body">';
					echo '<span class="teca-single-meta-content">';
					echo '<span class="teca-single-meta-label">' . esc_html__( 'Venue', 'the-events-calendar-addon2' ) . '</span>';
					echo '<span class="teca-single-meta-value">' . $markup . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo '</span>';
					echo '</div>';
					echo '</div>';
				} else {
					echo '<div class="' . esc_attr( $visible_class ) . '"><span class="teca-single-meta-label">' . esc_html__( 'Venue', 'the-events-calendar-addon2' ) . '</span>' . $markup . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			}
			break;

		case 'event_organizer':
			$organizer_props = array( 'title' => true, 'phone' => true, 'email' => true, 'url' => true );
			ob_start();
			Template_Loader::load_template(
				'partials/gs-teca-organizer.php',
				array(
					'event'           => $event,
					'organizer_props' => $organizer_props,
				)
			);
			$markup = trim( (string) ob_get_clean() );
			if ( '' !== $markup ) {
				if ( 'default' === $style_key ) {
					teca_render_single_default_info_card_open( $visible_class );
					echo '<span class="teca-single-meta-label">' . esc_html__( 'Organizer', 'the-events-calendar-addon2' ) . '</span>';
					echo '<span class="teca-single-meta-value">' . $markup . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					teca_render_single_default_info_card_close();
				} elseif ( 'style-3' === $style_key ) {
					echo '<div class="' . esc_attr( $visible_class ) . '">';
					echo '<span class="teca-single-meta-label">' . esc_html__( 'Organizer', 'the-events-calendar-addon2' ) . '</span>';
					echo '<span class="teca-single-meta-value">' . $markup . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo '</div>';
				} elseif ( 'style-4' === $style_key ) {
					echo '<div class="' . esc_attr( $visible_class . ' teca-single-style-4-info-card teca-single-style-4-info-card--organizer' ) . '">';
					echo '<span class="teca-single-meta-label">' . esc_html__( 'Organizer', 'the-events-calendar-addon2' ) . '</span>';
					echo '<span class="teca-single-meta-value">' . $markup . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo '</div>';
				} elseif ( 'style-5' === $style_key ) {
					echo '<div class="' . esc_attr( $visible_class . ' teca-single-style-5-journey-step teca-single-style-5-journey-step--organizer' ) . '">';
					echo '<div class="teca-single-style-5-journey-body">';
					echo '<span class="teca-single-meta-content">';
					echo '<span class="teca-single-meta-label">' . esc_html__( 'Organizer', 'the-events-calendar-addon2' ) . '</span>';
					echo '<span class="teca-single-meta-value">' . $markup . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo '</span>';
					echo '</div>';
					echo '</div>';
				} else {
					echo '<div class="' . esc_attr( $visible_class ) . '"><span class="teca-single-meta-label">' . esc_html__( 'Organizer', 'the-events-calendar-addon2' ) . '</span>' . $markup . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			}
			break;

		case 'event_tags':
			$tags = teca_single_get_safe_terms( $event_id, 'post_tag' );
			if ( empty( $tags ) ) {
				return;
			}
			$tag_wrap_class = ( 'default' === $style_key ) ? 'teca-single-tax-right' : '';
			echo '<div class="' . esc_attr( trim( $visible_class . ' ' . $tag_wrap_class ) ) . '"><div class="teca-event-tags gs-teca-tags">';
			foreach ( $tags as $term ) {
				$link = teca_single_get_safe_term_link( $term );
				$name = isset( $term->name ) ? (string) $term->name : '';
				if ( '' === $name ) {
					continue;
				}
				if ( $link ) {
					echo '<a class="teca-event-tag gs-teca-tag" href="' . esc_url( $link ) . '">' . esc_html( $name ) . '</a>';
				} else {
					echo '<span class="teca-event-tag gs-teca-tag">' . esc_html( $name ) . '</span>';
				}
			}
			echo '</div></div>';
			break;

		case 'event_cost':
			$cost = teca_get_event_cost_display( $event_id );
			if ( ! $cost ) {
				return;
			}
			if ( 'style-5' === $style_key ) {
				echo '<div class="' . esc_attr( $visible_class . ' teca-single-style-5-journey-step teca-single-style-5-journey-step--cost' ) . '"><div class="teca-single-style-5-journey-body"><span class="teca-single-meta-content"><span class="teca-single-meta-label">' . esc_html__( 'Cost', 'the-events-calendar-addon2' ) . '</span><span class="teca-single-meta-value">' . esc_html( $cost ) . '</span></span></div></div>';
				break;
			}
			echo '<div class="' . esc_attr( $visible_class ) . '"><span class="teca-single-meta-label">' . esc_html__( 'Cost', 'the-events-calendar-addon2' ) . '</span><span class="teca-single-meta-value">' . esc_html( $cost ) . '</span></div>';
			break;

		case 'event_details':
			$content = get_post_field( 'post_content', $event_id );
			if ( ! $content ) {
				return;
			}

			if ( in_array( $style_key, array( 'default', 'style-1', 'style-2', 'style-3', 'style-4', 'style-5' ), true ) ) {
				$section_label  = teca_get_single_page_element_label( 'event_details' );
				$section_class  = 'default' === $style_key ? ' teca-single-content-section' : '';
				echo '<div class="' . esc_attr( $visible_class . $section_class ) . '">';
				if ( '' !== $section_label ) {
					echo '<div class="teca-single-section-label">' . esc_html( $section_label ) . '</div>';
				}
				echo '<div class="teca-single-section-content teca-single-description">' . wp_kses_post( wpautop( $content ) ) . '</div>';
				echo '</div>';
				break;
			}

			echo '<div class="' . esc_attr( $visible_class ) . ' teca-single-description">' . wp_kses_post( wpautop( $content ) ) . '</div>';
			break;

		case 'event_map':
			teca_render_single_event_map( $event_id, $style_key, $visible_class );
			break;

		case 'event_website':
			if ( in_array( $style_key, array( 'default', 'style-1', 'style-2', 'style-3' ), true ) ) {
				return;
			}

			$url = teca_get_event_cta_url( $event_id );
			if ( ! $url ) {
				return;
			}
			echo '<div class="' . esc_attr( $visible_class ) . '"><a class="teca-single-website-link teca-event-button" href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( teca_get_event_website_text() ) . '</a></div>';
			break;

		case 'google_calendar_button':
			if ( in_array( $style_key, array( 'default', 'style-1', 'style-2', 'style-3', 'style-4' ), true ) ) {
				return;
			}

			ob_start();
			teca_render_google_calendar_button( $event_id, 'single' );
			$markup = trim( (string) ob_get_clean() );

			if ( '' === $markup ) {
				return;
			}

			echo '<div class="' . esc_attr( $visible_class ) . ' teca-google-calendar-actions teca-google-calendar-actions--single">' . $markup . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			break;
	}
}

function teca_render_single_event_map( $event_id, $style_key = 'default', $visible_class = '' ) {
	$event_id = (int) $event_id;

	if ( ! $event_id ) {
		return;
	}

	$map_data = teca_get_single_event_map_data( $event_id );

	if ( empty( $map_data['has_location'] ) ) {
		return;
	}

	Template_Loader::load_template(
		'partials/gs-teca-map.php',
		array(
			'event_id'      => $event_id,
			'map_data'      => $map_data,
			'style_key'     => $style_key,
			'visible_class' => $visible_class,
		)
	);
}

/**
 * Whether default hero image should render.
 *
 * @param array $event         Event data.
 * @param array $sorted_fields Visibility settings.
 * @return bool
 */
function teca_single_default_has_hero_image( array $event, array $sorted_fields ) {
	if ( ! teca_is_single_page_element_visible( 'event_thumbnail', $sorted_fields ) ) {
		return false;
	}

	$event_id = teca_get_popup_event_id( $event );
	$image    = teca_get_popup_image_data( $event, $event_id );

	return ! empty( $image['url'] );
}

/**
 * Hero field keys for single page style 1.
 *
 * @return string[]
 */
function teca_get_single_style1_hero_field_keys() {
	return array(
		'event_thumbnail',
		'event_cat',
		'event_title',
		'event_date',
		'event_time',
		'event_venue',
		'event_organizer',
		'event_cost',
		'event_website',
		'google_calendar_button',
	);
}

/**
 * Summary field keys grouped in style 1 hero.
 *
 * @return string[]
 */
function teca_get_single_style1_summary_field_keys() {
	return array(
		'event_date',
		'event_time',
		'event_venue',
		'event_organizer',
		'event_cost',
	);
}

/**
 * CTA field keys grouped in style 1 hero.
 *
 * @return string[]
 */
function teca_get_single_style1_action_field_keys() {
	return teca_get_single_action_element_keys();
}

/**
 * Whether style 1 hero media should render.
 *
 * @param array $event         Event data.
 * @param array $sorted_fields Visibility settings.
 * @return bool
 */
function teca_single_style1_has_hero_media( array $event, array $sorted_fields ) {
	if ( ! teca_is_single_page_element_visible( 'event_thumbnail', $sorted_fields ) ) {
		return false;
	}

	$event_id = teca_get_popup_event_id( $event );
	$image    = teca_get_popup_image_data( $event, $event_id );

	return ! empty( $image['url'] );
}

/**
 * Resolve short month label for style 1 media badge.
 *
 * @param int $event_id Event ID.
 * @return string
 */
function teca_get_single_event_month_short( $event_id ) {
	$event_id = (int) $event_id;
	if ( ! $event_id ) {
		return '';
	}

	if ( function_exists( 'tribe_get_start_date' ) ) {
		$month_label = tribe_get_start_date( $event_id, false, 'M' );
		if ( ! empty( $month_label ) ) {
			return (string) $month_label;
		}
	}

	$start_date = get_post_meta( $event_id, '_EventStartDate', true );
	if ( ! empty( $start_date ) ) {
		return (string) date_i18n( 'M', strtotime( $start_date ) );
	}

	return '';
}

/**
 * Render premium split layout for single page style 1.
 *
 * @param array  $event         Event data.
 * @param array  $sorted_fields Visibility settings.
 * @param string $style_key     Style key.
 * @return void
 */
function teca_render_single_style1_page( array $event, array $sorted_fields, $style_key = 'style-1' ) {
	$order       = teca_get_single_page_element_order( $sorted_fields );
	$hero_keys   = teca_get_single_style1_hero_field_keys();
	$summary_keys = teca_get_single_style1_summary_field_keys();
	$action_keys = teca_get_single_style1_action_field_keys();
	$hero_order  = array();
	$body_order  = array();

	foreach ( $order as $element_key ) {
		if ( in_array( $element_key, $hero_keys, true ) ) {
			$hero_order[] = $element_key;
		} else {
			$body_order[] = $element_key;
		}
	}

	$has_media = teca_single_style1_has_hero_media( $event, $sorted_fields );
	$tags_on_media = $has_media && teca_is_single_page_element_visible( 'event_tags', $sorted_fields );
	$hero_class = 'teca-single-style-1-hero' . ( $has_media ? '' : ' teca-single-style-1-hero--no-media' );

	echo '<div class="' . esc_attr( $hero_class ) . '">';
	echo '<div class="teca-single-style-1-hero-content">';

	$in_summary = false;
	$event_id   = teca_get_popup_event_id( $event );
	$hero_count = count( $hero_order );

	for ( $index = 0; $index < $hero_count; $index++ ) {
		$element_key = $hero_order[ $index ];

		if ( 'event_thumbnail' === $element_key ) {
			continue;
		}

		if ( in_array( $element_key, $action_keys, true ) ) {
			if ( $in_summary ) {
				echo '</div>';
				$in_summary = false;
			}

			$action_batch = array();

			while ( $index < $hero_count && in_array( $hero_order[ $index ], $action_keys, true ) ) {
				$action_batch[] = $hero_order[ $index ];
				$index++;
			}

			$index--;

			if ( $event_id ) {
				teca_render_single_action_batch(
					$event_id,
					$action_batch,
					$sorted_fields,
					'teca-single-style-1-hero-actions teca-single-actions teca-single-element teca-single-element-actions'
				);
			}

			continue;
		}

		ob_start();
		teca_render_single_page_element( $element_key, $event, $sorted_fields, $style_key );
		$markup = trim( (string) ob_get_clean() );

		if ( '' === $markup ) {
			continue;
		}

		$is_summary = in_array( $element_key, $summary_keys, true );

		if ( $is_summary && ! $in_summary ) {
			echo '<div class="teca-single-style-1-summary">';
			$in_summary = true;
		} elseif ( ! $is_summary && $in_summary ) {
			echo '</div>';
			$in_summary = false;
		}

		echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- renderer returns escaped HTML.
	}

	if ( $in_summary ) {
		echo '</div>';
	}

	echo '</div>';

	if ( $has_media ) {
		$event_id        = teca_get_popup_event_id( $event );
		$show_date_badge = teca_is_single_page_element_visible( 'event_date', $sorted_fields );
		$day_number      = '';
		$month_label     = '';

		if ( $show_date_badge && $event_id ) {
			if ( function_exists( 'tribe_get_start_date' ) ) {
				$day_number  = tribe_get_start_date( $event_id, false, 'j' );
				$month_label = tribe_get_start_date( $event_id, false, 'M' );
			} else {
				$start_date = get_post_meta( $event_id, '_EventStartDate', true );
				if ( ! empty( $start_date ) ) {
					$timestamp   = strtotime( $start_date );
					$day_number  = (string) date_i18n( 'j', $timestamp );
					$month_label = (string) date_i18n( 'M', $timestamp );
				}
			}
		}

		echo '<div class="teca-single-style-1-media">';
		if ( '' !== $day_number && '' !== $month_label ) {
			echo '<div class="teca-single-style-1-date-badge" aria-hidden="true">';
			echo '<span class="teca-single-style-1-date-badge-day">' . esc_html( $day_number ) . '</span>';
			echo '<span class="teca-single-style-1-date-badge-month">' . esc_html( $month_label ) . '</span>';
			echo '</div>';
		}
		teca_render_single_page_element( 'event_thumbnail', $event, $sorted_fields, $style_key );

		if ( $tags_on_media ) {
			ob_start();
			teca_render_single_page_element( 'event_tags', $event, $sorted_fields, $style_key );
			$tags_markup = trim( (string) ob_get_clean() );

			if ( '' !== $tags_markup ) {
				echo '<div class="teca-single-style-1-tag-overlay">' . $tags_markup . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- renderer returns escaped HTML.
			}
		}

		echo '</div>';
	}

	echo '</div>';

	if ( empty( $body_order ) ) {
		return;
	}

	ob_start();
	$has_body = false;

	foreach ( $body_order as $element_key ) {
		if ( $tags_on_media && 'event_tags' === $element_key ) {
			continue;
		}

		ob_start();
		teca_render_single_page_element( $element_key, $event, $sorted_fields, $style_key );
		$markup = trim( (string) ob_get_clean() );

		if ( '' === $markup ) {
			continue;
		}

		echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- renderer returns escaped HTML.
		$has_body = true;
	}

	$body_markup = (string) ob_get_clean();

	if ( $has_body ) {
		echo '<div class="teca-single-style-1-body">' . $body_markup . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

/**
 * Ticket field keys for single page style 2.
 *
 * @return string[]
 */
function teca_get_single_style2_ticket_field_keys() {
	return array(
		'event_thumbnail',
		'event_date',
		'event_time',
		'event_cat',
		'event_title',
		'event_venue',
		'event_organizer',
		'event_cost',
		'event_website',
		'google_calendar_button',
	);
}

/**
 * Left ticket panel field keys for style 2.
 *
 * @return string[]
 */
function teca_get_single_style2_left_field_keys() {
	return array(
		'event_date',
		'event_time',
	);
}

/**
 * Main ticket content field keys for style 2.
 *
 * @return string[]
 */
function teca_get_single_style2_main_field_keys() {
	return array(
		'event_cat',
		'event_title',
		'event_venue',
		'event_organizer',
		'event_cost',
		'event_website',
		'google_calendar_button',
	);
}

/**
 * CTA field keys grouped in style 2 ticket main.
 *
 * @return string[]
 */
function teca_get_single_style2_action_field_keys() {
	return teca_get_single_action_element_keys();
}

/**
 * Whether style 2 ticket media should render.
 *
 * @param array $event         Event data.
 * @param array $sorted_fields Visibility settings.
 * @return bool
 */
function teca_single_style2_has_ticket_media( array $event, array $sorted_fields ) {
	if ( ! teca_is_single_page_element_visible( 'event_thumbnail', $sorted_fields ) ) {
		return false;
	}

	$event_id = teca_get_popup_event_id( $event );
	$image    = teca_get_popup_image_data( $event, $event_id );

	return ! empty( $image['url'] );
}

/**
 * Resolve event day number for style 2 ticket date panel.
 *
 * @param int $event_id Event ID.
 * @return string
 */
function teca_get_single_style2_event_day_number( $event_id ) {
	$event_id = (int) $event_id;
	if ( ! $event_id ) {
		return '';
	}

	if ( function_exists( 'tribe_get_start_date' ) ) {
		$day_number = tribe_get_start_date( $event_id, false, 'j' );
		if ( ! empty( $day_number ) ) {
			return (string) $day_number;
		}
	}

	$start_date = get_post_meta( $event_id, '_EventStartDate', true );
	if ( ! empty( $start_date ) ) {
		return (string) date_i18n( 'j', strtotime( $start_date ) );
	}

	return '';
}

/**
 * Render style 2 ticket date block with large day number.
 *
 * @param array  $event         Event data.
 * @param array  $sorted_fields Visibility settings.
 * @param string $style_key     Style key.
 * @return string
 */
function teca_get_single_style2_ticket_date_markup( array $event, array $sorted_fields, $style_key = 'style-2' ) {
	if ( ! teca_is_single_page_element_visible( 'event_date', $sorted_fields ) ) {
		return '';
	}

	$event_id = teca_get_popup_event_id( $event );
	if ( ! $event_id ) {
		return '';
	}

	ob_start();
	Template_Loader::load_template(
		'partials/gs-teca-date.php',
		array( 'event' => $event )
	);
	$date_markup = trim( (string) ob_get_clean() );

	if ( '' === $date_markup ) {
		return '';
	}

	$day_number  = teca_get_single_style2_event_day_number( $event_id );
	$month_label = teca_get_single_event_month_short( $event_id );
	$slug          = 'date';
	$field_classes = 'teca-single-element teca-single-element-' . $slug;
	$visible_class = implode( ' ', Helpers::get_visible_classes( $sorted_fields['event_date'], $field_classes ) );

	ob_start();
	echo '<div class="' . esc_attr( $visible_class ) . ' teca-single-style-2-date-block">';
	if ( '' !== $day_number ) {
		echo '<span class="teca-single-date-number" aria-hidden="true">' . esc_html( $day_number ) . '</span>';
	}
	if ( '' !== $month_label ) {
		echo '<span class="teca-single-style-2-date-month">' . esc_html( $month_label ) . '</span>';
	}
	echo '<span class="teca-single-meta-label">' . esc_html__( 'Date', 'the-events-calendar-addon2' ) . '</span>';
	echo '<span class="teca-single-meta-value">' . $date_markup . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '</div>';

	return (string) ob_get_clean();
}

/**
 * Capture rendered markup for a single style 2 element.
 *
 * @param string $element_key   Element key.
 * @param array  $event         Event data.
 * @param array  $sorted_fields Visibility settings.
 * @param string $style_key     Style key.
 * @return string
 */
function teca_get_single_style2_element_markup( $element_key, array $event, array $sorted_fields, $style_key = 'style-2' ) {
	if ( 'event_date' === $element_key ) {
		return teca_get_single_style2_ticket_date_markup( $event, $sorted_fields, $style_key );
	}

	ob_start();
	teca_render_single_page_element( $element_key, $event, $sorted_fields, $style_key );

	return trim( (string) ob_get_clean() );
}

/**
 * Whether style 2 left ticket panel has renderable content.
 *
 * @param array  $left_order    Ordered left field keys.
 * @param array  $event         Event data.
 * @param array  $sorted_fields Visibility settings.
 * @param string $style_key     Style key.
 * @return bool
 */
function teca_single_style2_has_left_panel( array $left_order, array $event, array $sorted_fields, $style_key = 'style-2' ) {
	foreach ( $left_order as $element_key ) {
		if ( '' !== teca_get_single_style2_element_markup( $element_key, $event, $sorted_fields, $style_key ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Render premium ticket layout for single page style 2.
 *
 * @param array  $event         Event data.
 * @param array  $sorted_fields Visibility settings.
 * @param string $style_key     Style key.
 * @return void
 */
function teca_render_single_style2_page( array $event, array $sorted_fields, $style_key = 'style-2' ) {
	$order        = teca_get_single_page_element_order( $sorted_fields );
	$ticket_keys  = teca_get_single_style2_ticket_field_keys();
	$left_keys    = teca_get_single_style2_left_field_keys();
	$main_keys    = teca_get_single_style2_main_field_keys();
	$action_keys  = teca_get_single_style2_action_field_keys();
	$ticket_order = array();
	$body_order   = array();

	foreach ( $order as $element_key ) {
		if ( in_array( $element_key, $ticket_keys, true ) ) {
			$ticket_order[] = $element_key;
		} else {
			$body_order[] = $element_key;
		}
	}

	$left_order = array_values(
		array_filter(
			$ticket_order,
			static function ( $element_key ) use ( $left_keys ) {
				return in_array( $element_key, $left_keys, true );
			}
		)
	);
	$main_order = array_values(
		array_filter(
			$ticket_order,
			static function ( $element_key ) use ( $main_keys ) {
				return in_array( $element_key, $main_keys, true );
			}
		)
	);

	$has_media     = teca_single_style2_has_ticket_media( $event, $sorted_fields );
	$tags_on_media = $has_media && teca_is_single_page_element_visible( 'event_tags', $sorted_fields );
	$has_left      = teca_single_style2_has_left_panel( $left_order, $event, $sorted_fields, $style_key );

	$ticket_class = 'teca-single-style-2-ticket';
	if ( ! $has_media ) {
		$ticket_class .= ' teca-single-style-2-ticket--no-media';
	}
	if ( ! $has_left ) {
		$ticket_class .= ' teca-single-style-2-ticket--no-left';
	}

	echo '<div class="' . esc_attr( $ticket_class ) . '">';

	if ( $has_left ) {
		echo '<div class="teca-single-style-2-ticket-left">';

		foreach ( $left_order as $element_key ) {
			$markup = teca_get_single_style2_element_markup( $element_key, $event, $sorted_fields, $style_key );
			if ( '' !== $markup ) {
				echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- renderer returns escaped HTML.
			}
		}

		echo '</div>';
	}

	echo '<div class="teca-single-style-2-ticket-main">';

	$event_id   = teca_get_popup_event_id( $event );
	$main_count = count( $main_order );

	for ( $index = 0; $index < $main_count; $index++ ) {
		$element_key = $main_order[ $index ];

		if ( in_array( $element_key, $action_keys, true ) ) {
			$action_batch = array();

			while ( $index < $main_count && in_array( $main_order[ $index ], $action_keys, true ) ) {
				$action_batch[] = $main_order[ $index ];
				$index++;
			}

			$index--;

			if ( $event_id ) {
				teca_render_single_action_batch(
					$event_id,
					$action_batch,
					$sorted_fields,
					'teca-single-style-2-ticket-actions teca-single-actions teca-single-element teca-single-element-actions'
				);
			}

			continue;
		}

		$markup = teca_get_single_style2_element_markup( $element_key, $event, $sorted_fields, $style_key );

		if ( '' === $markup ) {
			continue;
		}

		echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- renderer returns escaped HTML.
	}

	echo '</div>';

	if ( $has_media ) {
		echo '<div class="teca-single-style-2-ticket-media">';
		teca_render_single_page_element( 'event_thumbnail', $event, $sorted_fields, $style_key );

		if ( $tags_on_media ) {
			ob_start();
			teca_render_single_page_element( 'event_tags', $event, $sorted_fields, $style_key );
			$tags_markup = trim( (string) ob_get_clean() );

			if ( '' !== $tags_markup ) {
				echo '<div class="teca-single-style-2-tag-overlay">' . $tags_markup . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- renderer returns escaped HTML.
			}
		}

		echo '</div>';
	}

	echo '</div>';

	if ( empty( $body_order ) ) {
		return;
	}

	ob_start();
	$has_body = false;

	foreach ( $body_order as $element_key ) {
		if ( $tags_on_media && 'event_tags' === $element_key ) {
			continue;
		}

		ob_start();
		teca_render_single_page_element( $element_key, $event, $sorted_fields, $style_key );
		$markup = trim( (string) ob_get_clean() );

		if ( '' === $markup ) {
			continue;
		}

		echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- renderer returns escaped HTML.
		$has_body = true;
	}

	$body_markup = (string) ob_get_clean();

	if ( $has_body ) {
		echo '<div class="teca-single-style-2-body">' . $body_markup . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

/**
 * Hero field keys for single page style 3.
 *
 * @return string[]
 */
function teca_get_single_style3_hero_field_keys() {
	return array(
		'event_thumbnail',
		'event_cat',
		'event_title',
	);
}

/**
 * Title card field keys for style 3.
 *
 * @return string[]
 */
function teca_get_single_style3_title_card_field_keys() {
	return array(
		'event_cat',
		'event_title',
	);
}

/**
 * Meta strip field keys for style 3.
 *
 * @return string[]
 */
function teca_get_single_style3_meta_strip_field_keys() {
	return array(
		'event_date',
		'event_time',
		'event_venue',
		'event_organizer',
		'event_cost',
	);
}

/**
 * Content field keys for style 3.
 *
 * @return string[]
 */
function teca_get_single_style3_content_field_keys() {
	return array(
		'event_map',
		'event_details',
		'event_related_section',
		'event_tags',
		'event_website',
		'google_calendar_button',
	);
}

/**
 * CTA field keys grouped in style 3 content.
 *
 * @return string[]
 */
function teca_get_single_style3_action_field_keys() {
	return teca_get_single_action_element_keys();
}

/**
 * Whether style 3 hero media should render.
 *
 * @param array $event         Event data.
 * @param array $sorted_fields Visibility settings.
 * @return bool
 */
function teca_single_style3_has_hero_media( array $event, array $sorted_fields ) {
	if ( ! teca_is_single_page_element_visible( 'event_thumbnail', $sorted_fields ) ) {
		return false;
	}

	$event_id = teca_get_popup_event_id( $event );
	$image    = teca_get_popup_image_data( $event, $event_id );

	return ! empty( $image['url'] );
}

/**
 * Capture rendered markup for a single style 3 element.
 *
 * @param string $element_key   Element key.
 * @param array  $event         Event data.
 * @param array  $sorted_fields Visibility settings.
 * @param string $style_key     Style key.
 * @return string
 */
function teca_get_single_style3_element_markup( $element_key, array $event, array $sorted_fields, $style_key = 'style-3' ) {
	ob_start();
	teca_render_single_page_element( $element_key, $event, $sorted_fields, $style_key );

	return trim( (string) ob_get_clean() );
}

/**
 * Whether style 3 title card has renderable content.
 *
 * @param array  $title_order   Ordered title card field keys.
 * @param array  $event         Event data.
 * @param array  $sorted_fields Visibility settings.
 * @param string $style_key     Style key.
 * @return bool
 */
function teca_single_style3_has_title_card( array $title_order, array $event, array $sorted_fields, $style_key = 'style-3' ) {
	foreach ( $title_order as $element_key ) {
		if ( '' !== teca_get_single_style3_element_markup( $element_key, $event, $sorted_fields, $style_key ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Whether style 3 meta strip has renderable content.
 *
 * @param array  $meta_order    Ordered meta field keys.
 * @param array  $event         Event data.
 * @param array  $sorted_fields Visibility settings.
 * @param string $style_key     Style key.
 * @return bool
 */
function teca_single_style3_has_meta_strip( array $meta_order, array $event, array $sorted_fields, $style_key = 'style-3' ) {
	foreach ( $meta_order as $element_key ) {
		if ( '' !== teca_get_single_style3_element_markup( $element_key, $event, $sorted_fields, $style_key ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Render magazine feature layout for single page style 3.
 *
 * @param array  $event         Event data.
 * @param array  $sorted_fields Visibility settings.
 * @param string $style_key     Style key.
 * @return void
 */
function teca_render_single_style3_page( array $event, array $sorted_fields, $style_key = 'style-3' ) {
	$order         = teca_get_single_page_element_order( $sorted_fields );
	$hero_keys     = teca_get_single_style3_hero_field_keys();
	$title_keys    = teca_get_single_style3_title_card_field_keys();
	$meta_keys     = teca_get_single_style3_meta_strip_field_keys();
	$content_keys  = teca_get_single_style3_content_field_keys();
	$action_keys   = teca_get_single_style3_action_field_keys();
	$title_order   = array();
	$meta_order    = array();
	$content_order = array();

	foreach ( $order as $element_key ) {
		if ( in_array( $element_key, $title_keys, true ) ) {
			$title_order[] = $element_key;
		} elseif ( in_array( $element_key, $meta_keys, true ) ) {
			$meta_order[] = $element_key;
		} elseif ( in_array( $element_key, $content_keys, true ) ) {
			$content_order[] = $element_key;
		} elseif ( ! in_array( $element_key, $hero_keys, true ) ) {
			$content_order[] = $element_key;
		}
	}

	$has_media     = teca_single_style3_has_hero_media( $event, $sorted_fields );
	$tags_on_media = $has_media && teca_is_single_page_element_visible( 'event_tags', $sorted_fields );
	$has_title     = teca_single_style3_has_title_card( $title_order, $event, $sorted_fields, $style_key );
	$has_meta      = teca_single_style3_has_meta_strip( $meta_order, $event, $sorted_fields, $style_key );

	if ( $has_media ) {
		echo '<div class="teca-single-style-3-hero">';
		teca_render_single_page_element( 'event_thumbnail', $event, $sorted_fields, $style_key );

		if ( $tags_on_media ) {
			ob_start();
			teca_render_single_page_element( 'event_tags', $event, $sorted_fields, $style_key );
			$tags_markup = trim( (string) ob_get_clean() );

			if ( '' !== $tags_markup ) {
				echo '<div class="teca-single-style-3-tag-overlay">' . $tags_markup . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- renderer returns escaped HTML.
			}
		}

		echo '<div class="teca-single-style-3-hero-overlay" aria-hidden="true"></div>';
		echo '</div>';
	}

	if ( $has_title ) {
		$title_card_class = 'teca-single-style-3-title-card';
		if ( ! $has_media ) {
			$title_card_class .= ' teca-single-style-3-title-card--no-hero';
		}

		echo '<div class="' . esc_attr( $title_card_class ) . '">';

		foreach ( $title_order as $element_key ) {
			$markup = teca_get_single_style3_element_markup( $element_key, $event, $sorted_fields, $style_key );
			if ( '' !== $markup ) {
				echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- renderer returns escaped HTML.
			}
		}

		echo '</div>';
	}

	if ( ! $has_meta && empty( $content_order ) ) {
		return;
	}

	echo '<div class="teca-single-style-3-shell">';

	if ( $has_meta ) {
		echo '<div class="teca-single-style-3-meta-strip">';

		foreach ( $meta_order as $element_key ) {
			$markup = teca_get_single_style3_element_markup( $element_key, $event, $sorted_fields, $style_key );
			if ( '' !== $markup ) {
				echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- renderer returns escaped HTML.
			}
		}

		echo '</div>';
	}

	if ( ! empty( $content_order ) ) {
		$has_content   = false;
		$event_id      = teca_get_popup_event_id( $event );
		$content_count = count( $content_order );

		for ( $index = 0; $index < $content_count; $index++ ) {
			$element_key = $content_order[ $index ];

			if ( $tags_on_media && 'event_tags' === $element_key ) {
				continue;
			}

			if ( in_array( $element_key, $action_keys, true ) ) {
				if ( ! $has_content ) {
					echo '<div class="teca-single-style-3-content">';
					$has_content = true;
				}

				$action_batch = array();

				while ( $index < $content_count && in_array( $content_order[ $index ], $action_keys, true ) ) {
					$action_batch[] = $content_order[ $index ];
					$index++;
				}

				$index--;

				if ( $event_id ) {
					teca_render_single_action_batch(
						$event_id,
						$action_batch,
						$sorted_fields,
						'teca-single-style-3-actions teca-single-actions teca-single-element teca-single-element-actions'
					);
				}

				continue;
			}

			$markup = teca_get_single_style3_element_markup( $element_key, $event, $sorted_fields, $style_key );

			if ( '' === $markup ) {
				continue;
			}

			if ( ! $has_content ) {
				echo '<div class="teca-single-style-3-content">';
				$has_content = true;
			}

			echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- renderer returns escaped HTML.
		}

		if ( $has_content ) {
			echo '</div>';
		}
	}

	echo '</div>';
}

/**
 * Rail field keys for single page style 4.
 *
 * @return string[]
 */
function teca_get_single_style4_rail_field_keys() {
	return array(
		'event_date',
		'event_cat',
		'event_time',
		'event_cost',
		'event_website',
	);
}

/**
 * Main showcase field keys for style 4.
 *
 * @return string[]
 */
function teca_get_single_style4_main_field_keys() {
	return array(
		'event_thumbnail',
		'event_title',
		'event_venue',
		'event_organizer',
		'event_map',
		'event_details',
		'event_related_section',
		'event_tags',
	);
}

/**
 * Info grid field keys grouped in style 4 main area.
 *
 * @return string[]
 */
function teca_get_single_style4_info_grid_field_keys() {
	return array(
		'event_venue',
		'event_organizer',
	);
}

/**
 * CTA field keys for style 4 rail.
 *
 * @return string[]
 */
function teca_get_single_style4_action_field_keys() {
	return array(
		'event_website',
	);
}

/**
 * Render Google Calendar action below event details for style 4.
 *
 * @param int   $event_id      Event ID.
 * @param array $sorted_fields Visibility settings.
 * @return void
 */
function teca_render_single_style4_calendar_action( $event_id, array $sorted_fields ) {
	$event_id = (int) $event_id;

	if ( $event_id <= 0 || ! teca_is_single_page_element_visible( 'google_calendar_button', $sorted_fields ) ) {
		return;
	}

	ob_start();
	teca_render_google_calendar_button( $event_id, 'single' );
	$markup = trim( (string) ob_get_clean() );

	if ( '' === $markup ) {
		return;
	}

	$classes = implode( ' ', Helpers::get_visible_classes( $sorted_fields['google_calendar_button'] ?? true, 'teca-single-style-4-calendar-action' ) );

	echo '<div class="' . esc_attr( $classes ) . '">' . $markup . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- helper returns escaped HTML.
}

/**
 * Whether style 4 showcase image should render.
 *
 * @param array $event         Event data.
 * @param array $sorted_fields Visibility settings.
 * @return bool
 */
function teca_single_style4_has_showcase_media( array $event, array $sorted_fields ) {
	if ( ! teca_is_single_page_element_visible( 'event_thumbnail', $sorted_fields ) ) {
		return false;
	}

	$event_id = teca_get_popup_event_id( $event );
	$image    = teca_get_popup_image_data( $event, $event_id );

	return ! empty( $image['url'] );
}

/**
 * Capture rendered markup for a single style 4 element.
 *
 * @param string $element_key   Element key.
 * @param array  $event         Event data.
 * @param array  $sorted_fields Visibility settings.
 * @param string $style_key     Style key.
 * @return string
 */
function teca_get_single_style4_element_markup( $element_key, array $event, array $sorted_fields, $style_key = 'style-4' ) {
	ob_start();
	teca_render_single_page_element( $element_key, $event, $sorted_fields, $style_key );

	return trim( (string) ob_get_clean() );
}

/**
 * Whether style 4 rail has renderable content.
 *
 * @param array  $rail_order    Ordered rail field keys.
 * @param array  $event         Event data.
 * @param array  $sorted_fields Visibility settings.
 * @param string $style_key     Style key.
 * @return bool
 */
function teca_single_style4_has_rail( array $rail_order, array $event, array $sorted_fields, $style_key = 'style-4' ) {
	foreach ( $rail_order as $element_key ) {
		if ( '' !== teca_get_single_style4_element_markup( $element_key, $event, $sorted_fields, $style_key ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Whether style 4 main area has renderable content.
 *
 * @param array  $main_order    Ordered main field keys.
 * @param array  $event         Event data.
 * @param array  $sorted_fields Visibility settings.
 * @param string $style_key     Style key.
 * @return bool
 */
function teca_single_style4_has_main( array $main_order, array $event, array $sorted_fields, $style_key = 'style-4' ) {
	foreach ( $main_order as $element_key ) {
		if ( '' !== teca_get_single_style4_element_markup( $element_key, $event, $sorted_fields, $style_key ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Render a style 4 main element, grouping venue/organizer in an info grid.
 *
 * @param string $element_key     Element key.
 * @param array  $main_order      Full main order.
 * @param int    $index           Current index in main order.
 * @param array  $event           Event data.
 * @param array  $sorted_fields   Visibility settings.
 * @param string $style_key       Style key.
 * @return void
 */
function teca_render_single_style4_main_element( $element_key, array $main_order, $index, array $event, array $sorted_fields, $style_key = 'style-4' ) {
	$info_keys = teca_get_single_style4_info_grid_field_keys();

	if ( ! in_array( $element_key, $info_keys, true ) ) {
		if ( 'event_details' === $element_key ) {
			$markup = teca_get_single_style4_element_markup( $element_key, $event, $sorted_fields, $style_key );

			if ( '' !== $markup ) {
				echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- renderer returns escaped HTML.
			}

			$event_id = teca_get_popup_event_id( $event );
			teca_render_single_style4_calendar_action( $event_id, $sorted_fields );

			return;
		}

		$markup = teca_get_single_style4_element_markup( $element_key, $event, $sorted_fields, $style_key );
		if ( '' !== $markup ) {
			if ( 'event_thumbnail' === $element_key ) {
				echo '<div class="teca-single-style-4-image-wrap">';
				echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- renderer returns escaped HTML.
				echo '</div>';
			} elseif ( 'event_title' === $element_key ) {
				echo '<div class="teca-single-style-4-headline">';
				echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- renderer returns escaped HTML.
				echo '</div>';
			} else {
				echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- renderer returns escaped HTML.
			}
		}
		return;
	}

	$grid_keys = array();
	for ( $i = $index, $len = count( $main_order ); $i < $len; $i++ ) {
		if ( in_array( $main_order[ $i ], $info_keys, true ) ) {
			$grid_keys[] = $main_order[ $i ];
		} else {
			break;
		}
	}

	if ( empty( $grid_keys ) ) {
		return;
	}

	$grid_markup = '';
	foreach ( $grid_keys as $grid_key ) {
		$markup = teca_get_single_style4_element_markup( $grid_key, $event, $sorted_fields, $style_key );
		if ( '' !== $markup ) {
			$grid_markup .= $markup;
		}
	}

	if ( '' === $grid_markup ) {
		return;
	}

	echo '<div class="teca-single-style-4-info-grid">' . $grid_markup . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- renderer returns escaped HTML.
}

/**
 * Render side-rail showcase layout for single page style 4.
 *
 * @param array  $event         Event data.
 * @param array  $sorted_fields Visibility settings.
 * @param string $style_key     Style key.
 * @return void
 */
function teca_render_single_style4_page( array $event, array $sorted_fields, $style_key = 'style-4' ) {
	$order         = teca_get_single_page_element_order( $sorted_fields );
	$rail_keys     = teca_get_single_style4_rail_field_keys();
	$main_keys     = teca_get_single_style4_main_field_keys();
	$action_keys   = teca_get_single_style4_action_field_keys();
	$info_keys     = teca_get_single_style4_info_grid_field_keys();
	$rail_order    = array();
	$main_order    = array();

	foreach ( $order as $element_key ) {
		if ( 'google_calendar_button' === $element_key ) {
			continue;
		}

		if ( in_array( $element_key, $rail_keys, true ) ) {
			$rail_order[] = $element_key;
		} elseif ( in_array( $element_key, $main_keys, true ) ) {
			$main_order[] = $element_key;
		} elseif ( ! in_array( $element_key, array_merge( $rail_keys, $main_keys ), true ) ) {
			$main_order[] = $element_key;
		}
	}

	$has_rail = teca_single_style4_has_rail( $rail_order, $event, $sorted_fields, $style_key );
	$has_main = teca_single_style4_has_main( $main_order, $event, $sorted_fields, $style_key );

	if ( ! $has_rail && ! $has_main ) {
		return;
	}

	$layout_class = 'teca-single-style-4-layout';
	if ( ! $has_rail ) {
		$layout_class .= ' teca-single-style-4-layout--no-rail';
	}

	echo '<div class="' . esc_attr( $layout_class ) . '">';

	if ( $has_rail ) {
		echo '<aside class="teca-single-style-4-rail">';
		echo '<div class="teca-single-style-4-rail-inner">';
		$in_actions = false;

		foreach ( $rail_order as $element_key ) {
			$markup = teca_get_single_style4_element_markup( $element_key, $event, $sorted_fields, $style_key );

			if ( '' === $markup ) {
				continue;
			}

			$is_action = in_array( $element_key, $action_keys, true );

			if ( $is_action && ! $in_actions ) {
				echo '<div class="teca-single-style-4-rail-actions">';
				$in_actions = true;
			} elseif ( ! $is_action && $in_actions ) {
				echo '</div>';
				$in_actions = false;
			}

			echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- renderer returns escaped HTML.
		}

		if ( $in_actions ) {
			echo '</div>';
		}

		echo '</div>';
		echo '</aside>';
	}

	if ( $has_main ) {
		echo '<main class="teca-single-style-4-main">';
		echo '<div class="teca-single-style-4-showcase">';
		echo '<div class="teca-single-style-4-canvas">';

		$main_count = count( $main_order );
		for ( $i = 0; $i < $main_count; $i++ ) {
			$element_key = $main_order[ $i ];

			if ( in_array( $element_key, $info_keys, true ) ) {
				$is_first_in_run = true;
				if ( $i > 0 && in_array( $main_order[ $i - 1 ], $info_keys, true ) ) {
					$is_first_in_run = false;
				}
				if ( ! $is_first_in_run ) {
					continue;
				}
			}

			teca_render_single_style4_main_element( $element_key, $main_order, $i, $event, $sorted_fields, $style_key );
		}

		echo '</div>';
		echo '</div>';
		echo '</main>';
	}

	echo '</div>';
}

/**
 * Intro field keys for single page style 5 hero.
 *
 * @return string[]
 */
function teca_get_single_style5_intro_field_keys() {
	return array(
		'event_cat',
		'event_title',
		'event_website',
		'event_tags',
	);
}

/**
 * Journey field keys for style 5.
 *
 * @return string[]
 */
function teca_get_single_style5_journey_field_keys() {
	return array(
		'event_date',
		'event_time',
		'event_venue',
		'event_organizer',
		'event_cost',
	);
}

/**
 * Details field keys for style 5.
 *
 * @return string[]
 */
function teca_get_single_style5_details_field_keys() {
	return array(
		'event_map',
		'event_details',
		'event_related_section',
	);
}

/**
 * Hero media field keys for style 5.
 *
 * @return string[]
 */
function teca_get_single_style5_hero_media_keys() {
	return array(
		'event_thumbnail',
		'event_tags',
	);
}

/**
 * CTA field keys grouped in style 5 intro.
 *
 * @return string[]
 */
function teca_get_single_style5_action_field_keys() {
	return array(
		'event_website',
	);
}

/**
 * Whether style 5 hero media should render.
 *
 * @param array $event         Event data.
 * @param array $sorted_fields Visibility settings.
 * @return bool
 */
function teca_single_style5_has_hero_media( array $event, array $sorted_fields ) {
	if ( ! teca_is_single_page_element_visible( 'event_thumbnail', $sorted_fields ) ) {
		return false;
	}

	$event_id = teca_get_popup_event_id( $event );
	$image    = teca_get_popup_image_data( $event, $event_id );

	return ! empty( $image['url'] );
}

/**
 * Capture rendered markup for a single style 5 element.
 *
 * @param string $element_key   Element key.
 * @param array  $event         Event data.
 * @param array  $sorted_fields Visibility settings.
 * @param string $style_key     Style key.
 * @return string
 */
function teca_get_single_style5_element_markup( $element_key, array $event, array $sorted_fields, $style_key = 'style-5' ) {
	ob_start();
	teca_render_single_page_element( $element_key, $event, $sorted_fields, $style_key );

	return trim( (string) ob_get_clean() );
}

/**
 * Whether style 5 intro has renderable content.
 *
 * @param array  $intro_order   Ordered intro field keys.
 * @param array  $event         Event data.
 * @param array  $sorted_fields Visibility settings.
 * @param string $style_key     Style key.
 * @return bool
 */
function teca_single_style5_has_intro( array $intro_order, array $event, array $sorted_fields, $style_key = 'style-5' ) {
	foreach ( $intro_order as $element_key ) {
		if ( '' !== teca_get_single_style5_element_markup( $element_key, $event, $sorted_fields, $style_key ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Whether style 5 journey section has renderable content.
 *
 * @param array  $journey_order Ordered journey field keys.
 * @param array  $event         Event data.
 * @param array  $sorted_fields Visibility settings.
 * @param string $style_key     Style key.
 * @return bool
 */
function teca_single_style5_has_journey( array $journey_order, array $event, array $sorted_fields, $style_key = 'style-5' ) {
	foreach ( $journey_order as $element_key ) {
		if ( '' !== teca_get_single_style5_element_markup( $element_key, $event, $sorted_fields, $style_key ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Render event journey layout for single page style 5.
 *
 * @param array  $event         Event data.
 * @param array  $sorted_fields Visibility settings.
 * @param string $style_key     Style key.
 * @return void
 */
function teca_render_single_style5_page( array $event, array $sorted_fields, $style_key = 'style-5' ) {
	$order          = teca_get_single_page_element_order( $sorted_fields );
	$intro_keys     = teca_get_single_style5_intro_field_keys();
	$journey_keys   = teca_get_single_style5_journey_field_keys();
	$details_keys   = teca_get_single_style5_details_field_keys();
	$media_keys     = teca_get_single_style5_hero_media_keys();
	$action_keys    = teca_get_single_style5_action_field_keys();
	$intro_order    = array();
	$journey_order  = array();
	$details_order  = array();

	foreach ( $order as $element_key ) {
		if ( in_array( $element_key, $intro_keys, true ) ) {
			$intro_order[] = $element_key;
		} elseif ( in_array( $element_key, $journey_keys, true ) ) {
			$journey_order[] = $element_key;
		} elseif ( in_array( $element_key, $details_keys, true ) ) {
			$details_order[] = $element_key;
		} elseif ( ! in_array( $element_key, $media_keys, true ) ) {
			$journey_order[] = $element_key;
		}
	}

	$has_media      = teca_single_style5_has_hero_media( $event, $sorted_fields );
	$tags_on_media  = $has_media && teca_is_single_page_element_visible( 'event_tags', $sorted_fields );
	$has_intro      = teca_single_style5_has_intro( $intro_order, $event, $sorted_fields, $style_key );
	$has_journey    = teca_single_style5_has_journey( $journey_order, $event, $sorted_fields, $style_key );
	$has_hero       = $has_intro || $has_media;

	if ( ! $has_hero && ! $has_journey && empty( $details_order ) ) {
		return;
	}

	if ( $has_hero ) {
		$hero_class = 'teca-single-style-5-hero';
		if ( ! $has_media ) {
			$hero_class .= ' teca-single-style-5-hero--no-media';
		}

		echo '<section class="' . esc_attr( $hero_class ) . '">';

		if ( $has_intro ) {
			ob_start();
			$in_actions = false;

			foreach ( $intro_order as $element_key ) {
				if ( $tags_on_media && 'event_tags' === $element_key ) {
					continue;
				}

				$markup = teca_get_single_style5_element_markup( $element_key, $event, $sorted_fields, $style_key );

				if ( '' === $markup ) {
					continue;
				}

				$is_action = in_array( $element_key, $action_keys, true );

				if ( $is_action && ! $in_actions ) {
					echo '<div class="teca-single-style-5-intro-actions">';
					$in_actions = true;
				} elseif ( ! $is_action && $in_actions ) {
					echo '</div>';
					$in_actions = false;
				}

				echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- renderer returns escaped HTML.
			}

			if ( $in_actions ) {
				echo '</div>';
			}

			$intro_markup = trim( (string) ob_get_clean() );

			if ( '' !== $intro_markup ) {
				echo '<div class="teca-single-style-5-intro">' . $intro_markup . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- renderer returns escaped HTML.
			}
		}

		if ( $has_media ) {
			echo '<div class="teca-single-style-5-media">';
			teca_render_single_page_element( 'event_thumbnail', $event, $sorted_fields, $style_key );

			if ( $tags_on_media ) {
				ob_start();
				teca_render_single_page_element( 'event_tags', $event, $sorted_fields, $style_key );
				$tags_markup = trim( (string) ob_get_clean() );

				if ( '' !== $tags_markup ) {
					echo '<div class="teca-single-style-5-tag-overlay">' . $tags_markup . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- renderer returns escaped HTML.
				}
			}

			echo '</div>';
		}

		echo '</section>';
	}

	if ( $has_journey ) {
		echo '<section class="teca-single-style-5-journey">';

		foreach ( $journey_order as $element_key ) {
			$markup = teca_get_single_style5_element_markup( $element_key, $event, $sorted_fields, $style_key );
			if ( '' !== $markup ) {
				echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- renderer returns escaped HTML.
			}
		}

		echo '</section>';
	}

	if ( ! empty( $details_order ) ) {
		$details_markup = '';

		foreach ( $details_order as $element_key ) {
			$markup = teca_get_single_style5_element_markup( $element_key, $event, $sorted_fields, $style_key );
			if ( '' !== $markup ) {
				$details_markup .= $markup;
			}
		}

		if ( '' !== $details_markup ) {
			echo '<section class="teca-single-style-5-details">' . $details_markup . '</section>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- renderer returns escaped HTML.
		}
	}
}
