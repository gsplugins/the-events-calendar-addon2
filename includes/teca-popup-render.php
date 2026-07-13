<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally kept for backward compatibility.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default popup element order when no user order is saved.
 *
 * @return string[]
 */
function teca_get_popup_default_element_order() {
	return array(
		'event_thumbnail',
		'event_cat',
		'event_title',
		'event_date',
		'event_time',
		'event_venue',
		'event_organizer',
		'event_tags',
		'event_cost',
		'event_details',
		'view_details_button',
		'event_website',
		'google_calendar_button',
	);
}

/**
 * Popup visibility settings array.
 *
 * @param array|null $popup_visibility_settings Visibility settings.
 * @return array
 */
function teca_get_popup_visibility_settings( $popup_visibility_settings = null ) {
	$defaults = plugin()->builder->get_popup_visibility_defaults();

	if ( is_array( $popup_visibility_settings ) && ! empty( $popup_visibility_settings ) ) {
		return array_merge( $defaults, $popup_visibility_settings );
	}

	return $defaults;
}

/**
 * Ordered popup element keys respecting saved sort order.
 *
 * @param array|null $popup_visibility_settings Visibility settings.
 * @param array|null $popup_visibility_order  Saved order.
 * @return string[]
 */
function teca_get_popup_element_order( $popup_visibility_settings = null, $popup_visibility_order = null ) {
	return teca_get_popup_field_order( $popup_visibility_settings, $popup_visibility_order );
}

/**
 * Whether a popup element is visible per Visibility tab settings.
 *
 * @param string     $element_key             Element key.
 * @param array|null $popup_visibility_settings Visibility settings.
 * @return bool
 */
function teca_is_popup_element_visible( $element_key, $popup_visibility_settings = null ) {
	$settings = teca_get_popup_visibility_settings( $popup_visibility_settings );

	if ( ! isset( $settings[ $element_key ] ) ) {
		return false;
	}

	return Helpers::is_visible( $settings[ $element_key ] );
}

/**
 * Resolve event ID for popup rendering.
 *
 * @param array $event Event data.
 * @return int
 */
function teca_get_popup_event_id( array $event = array() ) {
	$event_id = 0;
	$id_keys  = array( 'event_id', 'ID', 'id', 'post_id' );

	foreach ( $id_keys as $id_key ) {
		if ( ! empty( $event[ $id_key ] ) ) {
			$event_id = (int) $event[ $id_key ];

			if ( $event_id > 0 ) {
				return $event_id;
			}
		}
	}

	if ( $event_id > 0 ) {
		return $event_id;
	}

	$post = get_post();

	if ( $post instanceof \WP_Post ) {
		return (int) $post->ID;
	}

	return 0;
}

/**
 * Resolve featured image data for popup rendering.
 *
 * @param array $event Event data.
 * @param int   $event_id Event ID.
 * @return array{url:string,alt:string}
 */
function teca_get_popup_image_data( array $event, $event_id ) {
	$image = array(
		'url' => '',
		'alt' => '',
	);

	if ( ! empty( $event['image'] ) && is_array( $event['image'] ) ) {
		$image['url'] = (string) ( $event['image']['url'] ?? '' );
		$image['alt'] = (string) ( $event['image']['alt'] ?? '' );
	}

	if ( '' === $image['url'] && ! empty( $event['event_thumbnail_url'] ) ) {
		$image['url'] = (string) $event['event_thumbnail_url'];
	}

	if ( '' === $image['url'] && ! empty( $event['event_thumbnail'] ) ) {
		$image['url'] = (string) $event['event_thumbnail'];
	}

	if ( $event_id > 0 ) {
		if ( '' === $image['url'] ) {
			$image['url'] = (string) get_the_post_thumbnail_url( $event_id, 'full' );
		}

		$thumb_id = get_post_thumbnail_id( $event_id );

		if ( '' === $image['alt'] && $thumb_id ) {
			$image['alt'] = (string) get_post_meta( $thumb_id, '_wp_attachment_image_alt', true );
		}

		if ( '' === $image['alt'] ) {
			$image['alt'] = (string) get_the_title( $event_id );
		}
	}

	return $image;
}

/**
 * Render a single popup element.
 *
 * @param string $element_key             Element key.
 * @param array  $event                   Event data.
 * @param array  $popup_visibility_settings Visibility settings.
 * @param array  $settings                Shortcode settings.
 * @return void
 */
function teca_render_popup_element( $element_key, array $event, $popup_visibility_settings = null, array $settings = array() ) {
	if ( ! teca_is_popup_element_visible( $element_key, $popup_visibility_settings ) ) {
		return;
	}

	$event_id = teca_get_popup_event_id( $event );

	if ( ! $event_id ) {
		return;
	}

	switch ( $element_key ) {
		case 'event_thumbnail':
			teca_render_popup_image_element( $event, $event_id, $popup_visibility_settings );
			break;

		case 'event_cat':
			teca_render_popup_taxonomy_element( 'category', $event, $popup_visibility_settings );
			break;

		case 'event_title':
			teca_render_popup_title_element( $event_id, $popup_visibility_settings );
			break;

		case 'event_date':
			teca_render_popup_date_element( $event_id, $popup_visibility_settings );
			break;

		case 'event_time':
			teca_render_popup_time_element( $event_id, $popup_visibility_settings );
			break;

		case 'event_venue':
			teca_render_popup_venue_element( $event, $popup_visibility_settings );
			break;

		case 'event_organizer':
			teca_render_popup_organizer_element( $event, $popup_visibility_settings );
			break;

		case 'event_tags':
			teca_render_popup_taxonomy_element( 'tags', $event, $popup_visibility_settings );
			break;

		case 'event_cost':
			teca_render_popup_cost_element( $event_id, $popup_visibility_settings );
			break;

		case 'event_details':
			teca_render_popup_description_element( $event_id, $event, $popup_visibility_settings, $settings );
			break;

		case 'event_website':
			teca_render_popup_website_element( $event_id, $popup_visibility_settings );
			break;

		case 'google_calendar_button':
			teca_render_popup_google_calendar_element( $event_id, $popup_visibility_settings );
			break;

		case 'view_details_button':
			teca_render_popup_button_element( $event_id, $popup_visibility_settings );
			break;
	}
}

/**
 * Render all popup elements in configured order.
 *
 * @param array $event                   Event data.
 * @param array $popup_visibility_settings Visibility settings.
 * @param array $popup_visibility_order  Saved order.
 * @param array $settings                Shortcode settings.
 * @return void
 */
function teca_render_popup_elements( array $event, $popup_visibility_settings = null, $popup_visibility_order = null, array $settings = array(), array $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- Element visibility exclude list, not a WP_Query exclude parameter.
			'exclude'        => array(),
			'wrap_meta_grid' => true,
		)
	);

	$order = teca_get_popup_element_order( $popup_visibility_settings, $popup_visibility_order );

	if ( ! empty( $args['exclude'] ) ) {
		$order = array_values( array_diff( $order, $args['exclude'] ) );
	}

	if ( empty( $order ) ) {
		return;
	}

	$meta_fields  = array(
		'event_date',
		'event_time',
		'event_venue',
		'event_organizer',
		'event_cost',
	);
	$action_keys  = teca_get_popup_action_element_keys();
	$in_meta_grid = false;
	$event_id     = teca_get_popup_event_id( $event );
	$order_count  = count( $order );

	for ( $index = 0; $index < $order_count; $index++ ) {
		$element_key = $order[ $index ];

		if ( in_array( $element_key, $action_keys, true ) ) {
			$action_batch = array();

			while ( $index < $order_count && in_array( $order[ $index ], $action_keys, true ) ) {
				$action_batch[] = $order[ $index ];
				$index++;
			}

			$index--;

			if ( $args['wrap_meta_grid'] && $in_meta_grid ) {
				echo '</div>';
				$in_meta_grid = false;
			}

			if ( $event_id ) {
				teca_render_popup_action_batch( $event_id, $action_batch, $popup_visibility_settings );
			}

			continue;
		}

		ob_start();
		teca_render_popup_element( $element_key, $event, $popup_visibility_settings, $settings );
		$element_markup = trim( (string) ob_get_clean() );

		if ( '' === $element_markup ) {
			continue;
		}

		$is_meta_field = in_array( $element_key, $meta_fields, true );

		if ( $args['wrap_meta_grid'] && $is_meta_field && ! $in_meta_grid ) {
			echo '<div class="teca-popup-meta-grid">';
			$in_meta_grid = true;
		}

		if ( $args['wrap_meta_grid'] && ! $is_meta_field && $in_meta_grid ) {
			echo '</div>';
			$in_meta_grid = false;
		}

		echo $element_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- popup renderer returns escaped HTML.
	}

	if ( $args['wrap_meta_grid'] && $in_meta_grid ) {
		echo '</div>';
	}

	$event_id = isset( $event['event_id'] ) ? absint( $event['event_id'] ) : 0;

	if ( $event_id && ! empty( $settings ) && is_array( $settings ) ) {
		teca_render_related_events_section(
			$event_id,
			$settings,
			array(
				'context' => 'popup',
			)
		);
	}
}

/**
 * @param array      $event Event data.
 * @param int        $event_id Event ID.
 * @param array|null $visibility Visibility settings.
 */
function teca_render_popup_image_element( array $event, $event_id, $visibility ) {
	$image_data = teca_get_popup_image_data( $event, $event_id );
	$thumb_url  = $image_data['url'];

	if ( ! $thumb_url ) {
		return;
	}

	?>
	<div class="<?php Helpers::print_visible_classes( $visibility['event_thumbnail'] ?? true, 'teca-popup-image teca-popup-element-image teca-popup-element teca-event-thumb gs-teca-thumbnail-wrapper' ); ?>">
		<img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $image_data['alt'] ); ?>">
	</div>
	<?php
}

/**
 * @param string     $type category|tags.
 * @param array      $event Event data.
 * @param array|null $visibility Visibility settings.
 */
function teca_render_popup_taxonomy_element( $type, array $event, $visibility ) {
	$field_key = ( 'category' === $type ) ? 'event_cat' : 'event_tags';
	$partial   = ( 'category' === $type ) ? 'partials/gs-teca-cat.php' : 'partials/gs-teca-tag.php';
	$class     = ( 'category' === $type )
		? 'teca-popup-taxonomies teca-popup-element teca-event-categories gs-teca-categories teca-popup-detail-categories'
		: 'teca-popup-taxonomies teca-popup-element teca-event-tags gs-teca-tag teca-popup-detail-tags';

	$popup_detail = true;

	ob_start();
	include Template_Loader::locate_template( $partial );
	$markup = trim( (string) ob_get_clean() );

	if ( '' === $markup ) {
		return;
	}

	$classes = implode( ' ', Helpers::get_visible_classes( $visibility[ $field_key ] ?? true, $class ) );

	printf(
		'<div class="%s">%s</div>',
		esc_attr( $classes ),
		$markup // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- partial output is escaped.
	);
}

/**
 * @param int        $event_id Event ID.
 * @param array|null $visibility Visibility settings.
 */
function teca_render_popup_title_element( $event_id, $visibility ) {
	$title = get_the_title( $event_id );

	if ( ! $title ) {
		return;
	}

	?>
	<div class="<?php Helpers::print_visible_classes( $visibility['event_title'] ?? true, 'teca-popup-title-wrap teca-popup-element gs-teca-title teca-event-title' ); ?>">
	<h2 class="teca-popup-title gs-teca-title teca-popup-detail-title"><?php echo esc_html( $title ); ?></h2>
	</div>
	<?php
}

/**
 * @param int        $event_id Event ID.
 * @param array|null $visibility Visibility settings.
 */
function teca_render_popup_date_element( $event_id, $visibility ) {
	$popup_style_key = 'default';
	if ( isset( $popup_style ) ) {
		$popup_style_key = sanitize_key( (string) $popup_style );
	} elseif ( isset( $settings['popup_style'] ) ) {
		$popup_style_key = sanitize_key( (string) $settings['popup_style'] );
	} elseif ( isset( $atts['popup_style'] ) ) {
		$popup_style_key = sanitize_key( (string) $atts['popup_style'] );
	}

	$popup_settings = array();
	if ( isset( $settings ) && is_array( $settings ) ) {
		$popup_settings = $settings;
	} elseif ( isset( $atts ) && is_array( $atts ) ) {
		$popup_settings = $atts;
	}

	$event = array( 'event_id' => (int) $event_id );

	teca_begin_popup_date_format_scope( $popup_style_key, $popup_settings );

	ob_start();
	include Template_Loader::locate_template( 'partials/gs-teca-date.php' );
	$markup = trim( (string) ob_get_clean() );

	teca_end_popup_date_format_scope();

	if ( '' === $markup ) {
		return;
	}

	?>
	<div class="<?php Helpers::print_visible_classes( $visibility['event_date'] ?? true, 'teca-popup-meta-item teca-popup-element teca-event-date gs-teca-date' ); ?>">
		<span class="teca-popup-meta-label"><?php esc_html_e( 'Date', 'the-events-calendar-addon' ); ?></span>
		<div class="teca-popup-meta-value teca-popup-detail-date"><?php echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
	</div>
	<?php
}

/**
 * @param int        $event_id Event ID.
 * @param array|null $visibility Visibility settings.
 */
function teca_render_popup_time_element( $event_id, $visibility ) {
	$time_display = teca_format_event_start_time_display( $event_id );

	if ( ! $time_display ) {
		return;
	}

	?>
	<div class="<?php Helpers::print_visible_classes( $visibility['event_time'] ?? true, 'teca-popup-meta-item teca-popup-element teca-event-time' ); ?>">
		<span class="teca-popup-meta-label"><?php esc_html_e( 'Time', 'the-events-calendar-addon' ); ?></span>
		<span class="teca-popup-meta-value teca-popup-detail-time"><?php echo esc_html( $time_display ); ?></span>
	</div>
	<?php
}

/**
 * @param array      $event Event data.
 * @param array|null $visibility Visibility settings.
 */
function teca_render_popup_venue_element( array $event, $visibility ) {
	$venue_props = array(
		'title'                  => true,
		'address'                => true,
		'city'                   => true,
		'state'                  => true,
		'zip'                    => true,
		'country'                => true,
		'split_location_address' => true,
	);

	ob_start();
	include Template_Loader::locate_template( 'partials/gs-teca-venue.php', $venue_props );
	$markup = trim( (string) ob_get_clean() );

	if ( '' === $markup ) {
		return;
	}

	?>
	<div class="<?php Helpers::print_visible_classes( $visibility['event_venue'] ?? true, 'teca-popup-meta-item teca-popup-element teca-event-venue gs-teca-venue' ); ?>">
		<span class="teca-popup-meta-label teca-popup-detail-venue-title"><?php esc_html_e( 'Venue', 'the-events-calendar-addon' ); ?></span>
		<div class="teca-popup-meta-value teca-popup-detail-venue-value"><?php echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
	</div>
	<?php
}

/**
 * @param array      $event Event data.
 * @param array|null $visibility Visibility settings.
 */
function teca_render_popup_organizer_element( array $event, $visibility ) {
	$organizer_props = array(
		'title' => true,
		'phone' => true,
		'email' => true,
		'url'   => true,
	);

	ob_start();
	include Template_Loader::locate_template( 'partials/gs-teca-organizer.php', $organizer_props );
	$markup = trim( (string) ob_get_clean() );

	if ( '' === $markup ) {
		return;
	}

	?>
	<div class="<?php Helpers::print_visible_classes( $visibility['event_organizer'] ?? true, 'teca-popup-meta-item teca-popup-element teca-event-organizer gs-teca-organizer' ); ?>">
		<span class="teca-popup-meta-label teca-popup-detail-organizer-title"><?php esc_html_e( 'Organizer', 'the-events-calendar-addon' ); ?></span>
		<div class="teca-popup-meta-value"><?php echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
	</div>
	<?php
}

/**
 * @param int        $event_id Event ID.
 * @param array|null $visibility Visibility settings.
 */
function teca_render_popup_cost_element( $event_id, $visibility ) {
	$cost = teca_get_event_cost_display( $event_id );

	if ( ! $cost ) {
		return;
	}

	?>
	<div class="<?php Helpers::print_visible_classes( $visibility['event_cost'] ?? true, 'teca-popup-meta-item teca-popup-element teca-event-cost' ); ?>">
		<span class="teca-popup-meta-label"><?php esc_html_e( 'Cost', 'the-events-calendar-addon' ); ?></span>
		<span class="teca-popup-meta-value teca-popup-detail-cost"><?php echo esc_html( $cost ); ?></span>
	</div>
	<?php
}

/**
 * @param int        $event_id Event ID.
 * @param array      $event Event data.
 * @param array|null $visibility Visibility settings.
 */
function teca_render_popup_description_element( $event_id, array $event, $visibility, array $settings = array() ) {
	$description = '';

	if ( $event_id > 0 ) {
		$event_post = get_post( $event_id );

		if ( $event_post instanceof \WP_Post && $event_post->post_content ) {
			$description = $event_post->post_content;
		}
	}

	if ( ! $description && ! empty( $event['event_description'] ) ) {
		$description = (string) $event['event_description'];
	}

	if ( ! $description ) {
		return;
	}

	$popup_style = ! empty( $settings['teca_active_popup_style'] ) ? (string) $settings['teca_active_popup_style'] : '';
	$base_class  = 'teca-popup-description teca-popup-element teca-event-excerpt gs-teca-desc teca-popup-detail-excerpt teca-popup-detail-description teca-popup-detail-details';

	if ( 'style-1' === $popup_style ) {
		$strings = plugin()->builder->get_translation_strings();
		$label   = $strings['gsp-teca-details'] ?? __( 'Event Details', 'the-events-calendar-addon' );
		?>
		<div class="<?php Helpers::print_visible_classes( $visibility['event_details'] ?? true, $base_class . ' teca-popup-element-details' ); ?>">
			<div class="teca-popup-section-label"><?php echo esc_html( $label ); ?></div>
			<div class="teca-popup-section-content">
				<?php echo wp_kses_post( wpautop( $description ) ); ?>
			</div>
		</div>
		<?php
		return;
	}

	if ( 'style-2' === $popup_style ) {
		$strings = plugin()->builder->get_translation_strings();
		$label   = $strings['gsp-teca-details'] ?? __( 'Event Details', 'the-events-calendar-addon' );
		?>
		<div class="<?php Helpers::print_visible_classes( $visibility['event_details'] ?? true, $base_class . ' teca-popup-element-details' ); ?>">
			<div class="teca-popup-section-label"><?php echo esc_html( $label ); ?></div>
			<div class="teca-popup-section-content">
				<?php echo wp_kses_post( wpautop( $description ) ); ?>
			</div>
		</div>
		<?php
		return;
	}

	?>
	<div class="<?php Helpers::print_visible_classes( $visibility['event_details'] ?? true, $base_class ); ?>">
		<?php echo wp_kses_post( wpautop( $description ) ); ?>
	</div>
	<?php
}

/**
 * Popup action button element keys.
 *
 * @return string[]
 */
function teca_get_popup_action_element_keys() {
	return array(
		'view_details_button',
		'event_website',
		'google_calendar_button',
	);
}

/**
 * Build markup for one popup action button.
 *
 * @param string     $element_key Element key.
 * @param int        $event_id    Event ID.
 * @param array|null $visibility  Visibility settings.
 * @return string
 */
function teca_get_popup_action_button_visibility_field( $element_key, $visibility ) {
	$settings = teca_get_popup_visibility_settings( $visibility );

	if ( ! isset( $settings[ $element_key ] ) ) {
		return true;
	}

	return $settings[ $element_key ];
}

/**
 * Append responsive visibility classes to popup action button markup.
 *
 * @param string     $element_key Element key.
 * @param string     $markup      Button markup.
 * @param array|null $visibility  Visibility settings.
 * @return string
 */
function teca_apply_popup_action_button_visibility_classes( $element_key, $markup, $visibility ) {
	if ( '' === trim( $markup ) ) {
		return '';
	}

	$field   = teca_get_popup_action_button_visibility_field( $element_key, $visibility );
	$classes = implode( ' ', Helpers::get_visible_classes( $field, 'teca-popup-action-item' ) );

	return sprintf(
		'<span class="%1$s">%2$s</span>',
		esc_attr( $classes ),
		$markup
	);
}

function teca_get_popup_action_button_markup( $element_key, $event_id, $visibility ) {
	$event_id = (int) $event_id;

	if ( $event_id <= 0 ) {
		return '';
	}

	switch ( $element_key ) {
		case 'view_details_button':
			if ( ! teca_is_popup_element_visible( 'view_details_button', $visibility ) ) {
				return '';
			}

			$permalink = get_permalink( $event_id );

			if ( ! $permalink ) {
				return '';
			}

			ob_start();
			?>
			<a class="teca-popup-button teca-event-button teca-view-details gs-teca-btn-link" href="<?php echo esc_url( $permalink ); ?>">
				<span><?php echo esc_html( teca_get_view_details_text() ); ?></span>
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
					<path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</a>
			<?php
			return teca_apply_popup_action_button_visibility_classes(
				$element_key,
				trim( (string) ob_get_clean() ),
				$visibility
			);

		case 'event_website':
			if ( ! teca_is_popup_element_visible( 'event_website', $visibility ) ) {
				return '';
			}

			$url = teca_get_event_cta_url( $event_id );

			if ( ! $url ) {
				return '';
			}

			return teca_apply_popup_action_button_visibility_classes(
				$element_key,
				sprintf(
					'<a class="teca-popup-button teca-event-button teca-event-website-btn teca-popup-website-link" href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
					esc_url( $url ),
					esc_html( teca_get_event_website_text() )
				),
				$visibility
			);

		case 'google_calendar_button':
			if ( ! teca_is_popup_element_visible( 'google_calendar_button', $visibility ) ) {
				return '';
			}

			ob_start();
			teca_render_google_calendar_button( $event_id, 'popup' );
			$markup = trim( (string) ob_get_clean() );

			return teca_apply_popup_action_button_visibility_classes( $element_key, $markup, $visibility );
	}

	return '';
}

/**
 * Render one or more consecutive popup action buttons in saved sort order.
 *
 * @param int        $event_id     Event ID.
 * @param string[]   $element_keys Ordered action keys from popup sort order.
 * @param array|null $visibility   Visibility settings.
 * @return void
 */
function teca_render_popup_action_batch( $event_id, array $element_keys, $visibility ) {
	$buttons = array();

	foreach ( $element_keys as $element_key ) {
		$markup = teca_get_popup_action_button_markup( $element_key, $event_id, $visibility );

		if ( '' !== $markup ) {
			$buttons[] = $markup;
		}
	}

	if ( empty( $buttons ) ) {
		return;
	}

	?>
	<div class="teca-popup-actions teca-popup-element">
		<?php echo implode( '', $buttons ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped per button helper. ?>
	</div>
	<?php
}

/**
 * @param int        $event_id Event ID.
 * @param array|null $visibility Visibility settings.
 */
function teca_render_popup_website_element( $event_id, $visibility ) {
	$markup = teca_get_popup_action_button_markup( 'event_website', $event_id, $visibility );

	if ( '' === $markup ) {
		return;
	}

	?>
	<div class="<?php Helpers::print_visible_classes( $visibility['event_website'] ?? true, 'teca-popup-website teca-popup-element' ); ?>">
		<?php echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
	<?php
}

/**
 * @param int        $event_id Event ID.
 * @param array|null $visibility Visibility settings.
 */
function teca_render_popup_google_calendar_element( $event_id, $visibility ) {
	$markup = teca_get_popup_action_button_markup( 'google_calendar_button', $event_id, $visibility );

	if ( '' === $markup ) {
		return;
	}

	echo '<div class="teca-popup-element teca-popup-google-calendar teca-google-calendar-actions teca-google-calendar-actions--popup">' . $markup . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * @param int        $event_id Event ID.
 * @param array|null $visibility Visibility settings.
 */
function teca_render_popup_button_element( $event_id, $visibility ) {
	$markup = teca_get_popup_action_button_markup( 'view_details_button', $event_id, $visibility );

	if ( '' === $markup ) {
		return;
	}

	echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Whether popup should show a featured image panel.
 *
 * @param array      $event Event data.
 * @param array|null $popup_visibility_settings Visibility settings.
 * @return bool
 */
function teca_popup_has_visible_image( array $event, $popup_visibility_settings = null ) {
	if ( ! teca_is_popup_element_visible( 'event_thumbnail', $popup_visibility_settings ) ) {
		return false;
	}

	$event_id   = teca_get_popup_event_id( $event );
	$image_data = teca_get_popup_image_data( $event, $event_id );

	return '' !== $image_data['url'];
}

/**
 * Element keys excluded from Popup Style 1 content panel.
 *
 * @param array      $event                   Event data.
 * @param array|null $popup_visibility_settings Visibility settings.
 * @return string[]
 */
function teca_get_popup_style_1_content_excludes( array $event, $popup_visibility_settings = null ) {
	$excludes = array( 'event_thumbnail' );

	if (
		teca_popup_has_visible_image( $event, $popup_visibility_settings )
		&& teca_is_popup_element_visible( 'event_date', $popup_visibility_settings )
	) {
		$excludes[] = 'event_date';
	}

	return $excludes;
}

/**
 * Render Popup Style 1 floating date badge for the media card.
 *
 * @param int        $event_id   Event ID.
 * @param array|null $visibility Visibility settings.
 * @return void
 */
function teca_render_popup_style_1_date_badge( $event_id, $visibility ) {
	if ( ! teca_is_popup_element_visible( 'event_date', $visibility ) ) {
		return;
	}

	$event_id = (int) $event_id;
	if ( ! $event_id ) {
		return;
	}

	$day_number  = function_exists( __NAMESPACE__ . '\\teca_get_single_style2_event_day_number' )
		? teca_get_single_style2_event_day_number( $event_id )
		: '';
	$month_label = function_exists( __NAMESPACE__ . '\\teca_get_single_event_month_short' )
		? teca_get_single_event_month_short( $event_id )
		: '';

	if ( '' === $day_number && '' === $month_label ) {
		return;
	}

	?>
	<div class="teca-popup-date-badge teca-popup-element-date" aria-hidden="true">
		<?php if ( '' !== $day_number ) : ?>
			<span class="teca-popup-date-badge-day"><?php echo esc_html( $day_number ); ?></span>
		<?php endif; ?>
		<?php if ( '' !== $month_label ) : ?>
			<span class="teca-popup-date-badge-month"><?php echo esc_html( $month_label ); ?></span>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Render Popup Style 1 floating media card with optional date badge.
 *
 * @param array      $event                   Event data.
 * @param array|null $popup_visibility_settings Visibility settings.
 * @return void
 */
function teca_render_popup_style_1_media_card( array $event, $popup_visibility_settings = null ) {
	if ( ! teca_popup_has_visible_image( $event, $popup_visibility_settings ) ) {
		return;
	}

	$event_id = teca_get_popup_event_id( $event );

	echo '<div class="teca-popup-style-1-media-card">';
	teca_render_popup_image_element( $event, $event_id, $popup_visibility_settings );
	teca_render_popup_style_1_date_badge( $event_id, $popup_visibility_settings );
	echo '</div>';
}

/**
 * Render Popup Style 2 premium floating date pill for the media panel.
 *
 * @param int        $event_id   Event ID.
 * @param array|null $visibility Visibility settings.
 * @param array      $event      Event data.
 * @return void
 */
function teca_render_popup_style_2_date_pill( $event_id, $visibility, array $event = array() ) {
	if ( ! teca_is_popup_element_visible( 'event_date', $visibility ) ) {
		return;
	}

	$event_id = (int) $event_id;
	if ( ! $event_id ) {
		return;
	}

	$day_number = function_exists( __NAMESPACE__ . '\\teca_get_single_style2_event_day_number' )
		? teca_get_single_style2_event_day_number( $event_id )
		: '';

	$popup_settings = array();
	if ( isset( $settings ) && is_array( $settings ) ) {
		$popup_settings = $settings;
	} elseif ( isset( $atts ) && is_array( $atts ) ) {
		$popup_settings = $atts;
	}

	$popup_style_key = 'default';
	if ( isset( $popup_style ) ) {
		$popup_style_key = sanitize_key( (string) $popup_style );
	} elseif ( ! empty( $popup_settings['popup_style'] ) ) {
		$popup_style_key = sanitize_key( (string) $popup_settings['popup_style'] );
	}

	$date_text = teca_format_event_start_date_text(
		$event_id,
		teca_get_popup_layout_date_key( $popup_style_key ),
		$popup_settings
	);

	if ( '' === $day_number && '' === $date_text ) {
		teca_begin_popup_date_format_scope( $popup_style_key, $popup_settings );
		ob_start();
		Template_Loader::load_template(
			'partials/gs-teca-date.php',
			array( 'event' => array_merge( $event, array( 'event_id' => $event_id ) ) )
		);
		$fallback = trim( wp_strip_all_tags( (string) ob_get_clean() ) );
		teca_end_popup_date_format_scope();
		if ( '' !== $fallback ) {
			$date_text = $fallback;
		}
	}

	if ( '' === $day_number && '' === $date_text ) {
		return;
	}

	?>
	<div class="teca-popup-style-2-date-pill teca-popup-date-badge teca-popup-element-date" aria-hidden="true">
		<?php if ( '' !== $day_number ) : ?>
			<span class="teca-popup-date-number"><?php echo esc_html( $day_number ); ?></span>
		<?php endif; ?>
		<?php if ( '' !== $date_text ) : ?>
			<span class="teca-popup-date-text"><?php echo esc_html( $date_text ); ?></span>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Render Popup Style 2 layered media panel with optional date pill.
 *
 * @param array      $event Event data.
 * @param array|null $popup_visibility_settings Visibility settings.
 * @return void
 */
function teca_render_popup_style_2_media_panel( array $event, $popup_visibility_settings = null ) {
	if ( ! teca_popup_has_visible_image( $event, $popup_visibility_settings ) ) {
		return;
	}

	$event_id   = teca_get_popup_event_id( $event );
	$image_data = teca_get_popup_image_data( $event, $event_id );

	?>
	<div class="teca-popup-style-2-media-wrap">
		<div class="teca-popup-style-2-media">
			<img
				class="teca-popup-style-2-media-image"
				src="<?php echo esc_url( $image_data['url'] ); ?>"
				alt="<?php echo esc_attr( $image_data['alt'] ); ?>"
			>
		</div>
		<?php teca_render_popup_style_2_date_pill( $event_id, $popup_visibility_settings, $event ); ?>
	</div>
	<?php
}

/**
 * Element keys excluded from Style 2 content panel.
 *
 * @param array      $event                   Event data.
 * @param array|null $popup_visibility_settings Visibility settings.
 * @return string[]
 */
function teca_get_popup_style_2_content_excludes( array $event = array(), $popup_visibility_settings = null ) {
	$excludes = array( 'event_thumbnail' );

	if (
		teca_popup_has_visible_image( $event, $popup_visibility_settings )
		&& teca_is_popup_element_visible( 'event_date', $popup_visibility_settings )
	) {
		$excludes[] = 'event_date';
	}

	return $excludes;
}
