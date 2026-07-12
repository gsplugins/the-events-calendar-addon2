<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally GS_TECA.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and may be shared with included partial templates.

$teca_card_args            = $teca_card_args ?? array();
$layout                    = $teca_card_args['layout'] ?? 'accordion-panel';
$event                     = $teca_card_args['event'] ?? array();
$visibility_settings       = $teca_card_args['visibility_settings'] ?? null;
$link_context              = teca_build_card_link_context( $teca_card_args['link_context'] ?? array() );
$skip_fields               = (array) ( $teca_card_args['skip_fields'] ?? array() );
$hide_panel_image          = ! empty( $teca_card_args['hide_panel_image'] );
$hide_panel_tags           = ! empty( $teca_card_args['hide_panel_tags'] );
$hide_panel_cat            = ! empty( $teca_card_args['hide_panel_cat'] );
$excerpt_words             = (int) ( $teca_card_args['excerpt_words'] ?? 30 );
$show_button               = ! empty( $teca_card_args['show_button'] );
$button_text               = $teca_card_args['button_text'] ?? teca_get_view_details_text();

$event_id                  = (int) ( $event['event_id'] ?? 0 );
$title                     = $event['event_name'] ?? ( $event_id ? get_the_title( $event_id ) : '' );
$image_url                 = $event_id ? get_the_post_thumbnail_url( $event_id, 'medium_large' ) : '';
$date_display              = teca_get_timeline_date_pill( $event );
$time_display              = $event_id ? teca_format_event_start_time_display( $event_id ) : '';
$venue_data                = teca_get_event_venue_display( $event );
$venue_display             = $venue_data['name'] ?? '';
$category_names            = teca_get_event_category_names( $event );
$tag_names                 = teca_get_event_tag_names( $event );
$cost_display              = $event_id ? teca_get_event_cost_display( $event_id ) : '';
$excerpt                   = $event_id ? teca_get_event_excerpt_text( $event_id, $excerpt_words ) : '';
$field_order               = $teca_card_args['field_order'] ?? teca_get_card_field_order( $visibility_settings );

if ( 'timeline-3' === $layout ) {
	$field_order = teca_reorder_timeline_3_field_order( (array) $field_order );
}

$item_class_prefix         = 'teca-' . ( 'accordion-panel' === $layout ? 'accordion' : $layout );
$title_tag                 = 'h3';

$rendered_tax_row          = false;

foreach ( $field_order as $field_key ) {
	if ( in_array( $field_key, $skip_fields, true ) ) {
		continue;
	}

	if ( ! teca_is_card_field_visible( $field_key, $visibility_settings ) ) {
		continue;
	}

	switch ( $field_key ) {
		case 'event_thumbnail':
			if ( $hide_panel_image || ! $image_url ) {
				break;
			}

			if ( 'timeline-2' === $layout ) {
				?>
				<div class="<?php teca_print_card_visible_classes( 'event_thumbnail', 'teca-event-thumb teca-timeline-thumb teca-timeline-2-thumb gs-teca-thumbnail-wrapper', $visibility_settings ); ?>">
					<?php
					echo wp_kses_post( teca_get_card_link_html(
						$event_id,
						'<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $title ) . '" loading="lazy" />',
						$link_context,
						'teca-event-image-link'
					) );
					?>
					<?php if ( teca_is_card_field_visible( 'event_cat', $visibility_settings ) && ! empty( $category_names ) ) : ?>
						<div class="teca-event-categories gs-teca-categories teca-timeline-2-categories">
							<?php foreach ( $category_names as $category_name ) : ?>
								<span class="teca-event-category gs-teca-category teca-timeline-2-category"><?php echo esc_html( $category_name ); ?></span>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
				<?php
				break;
			}

			if ( 'timeline-3' === $layout ) {
				?>
				<div class="<?php teca_print_card_visible_classes( 'event_thumbnail', 'teca-event-thumb teca-timeline-3-thumb gs-teca-thumbnail-wrapper', $visibility_settings ); ?>">
					<?php
					echo wp_kses_post( teca_get_card_link_html(
						$event_id,
						'<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $title ) . '" loading="lazy" />',
						$link_context,
						'teca-event-image-link'
					) );
					?>
				</div>
				<?php
				break;
			}

			if ( 'timeline-1' === $layout ) {
				break;
			}

			$thumb_class = 'teca-event-thumb ' . esc_attr( $item_class_prefix ) . '-thumb gs-teca-thumbnail-wrapper teca-event-thumb';
			?>
			<div class="<?php teca_print_card_visible_classes( 'event_thumbnail', $thumb_class, $visibility_settings ); ?>">
				<?php
				echo wp_kses_post( teca_get_card_link_html(
					$event_id,
					'<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $title ) . '" loading="lazy" />',
					$link_context,
					'teca-event-image-link'
				) );
				?>
			</div>
			<?php
			break;

		case 'event_title':
			if ( ! $title ) {
				break;
			}

			if ( 'timeline-2' === $layout && ! $hide_panel_tags && ! empty( $tag_names ) && teca_is_card_field_visible( 'event_tags', $visibility_settings ) ) {
				?>
				<div class="<?php teca_print_card_visible_classes( 'event_tags', 'teca-event-tags gs-teca-tags teca-timeline-2-tags', $visibility_settings ); ?>">
					<?php foreach ( $tag_names as $tag_name ) : ?>
						<span class="teca-event-tag gs-teca-tag teca-timeline-2-tag"><?php echo esc_html( $tag_name ); ?></span>
					<?php endforeach; ?>
				</div>
				<?php
			}

			$title_class = 'teca-event-title gs-teca-title ' . esc_attr( $item_class_prefix ) . '-title';
			if ( 'accordion-panel' === $layout ) {
				$title_class = 'teca-event-title gs-teca-title teca-accordion-title';
			}

			$linked_title = teca_get_card_link_html( $event_id, esc_html( $title ), $link_context, '' );
			?>
			<<?php echo tag_escape( $title_tag ); ?> class="<?php teca_print_card_visible_classes( 'event_title', $title_class, $visibility_settings ); ?>">
				<?php echo wp_kses_post( $linked_title ); ?>
			</<?php echo tag_escape( $title_tag ); ?>>
			<?php
			break;

		case 'event_date':
			if ( 'timeline-1' === $layout ) {
				$date_badge = teca_get_timeline_date_badge( $event );
				if ( ! $date_badge ) {
					break;
				}
				?>
				<div class="<?php teca_print_card_visible_classes( 'event_date', 'teca-event-date gs-teca-date teca-timeline-date teca-timeline-1-date', $visibility_settings ); ?>">
					<?php echo esc_html( $date_badge ); ?>
				</div>
				<?php
				break;
			}

			if ( ! $date_display && ! $time_display ) {
				break;
			}
			?>
			<div class="<?php teca_print_card_visible_classes( 'event_date', 'teca-event-date gs-teca-date ' . esc_attr( $item_class_prefix ) . '-date', $visibility_settings ); ?>">
				<?php if ( $date_display ) : ?>
					<span class="teca-event-date-value"><?php echo esc_html( $date_display ); ?></span>
				<?php endif; ?>
				<?php if ( $time_display ) : ?>
					<span class="teca-event-time gs-teca-date teca-event-time-value <?php echo esc_attr( $item_class_prefix ); ?>-time"><?php echo esc_html( $time_display ); ?></span>
				<?php endif; ?>
			</div>
			<?php
			break;

		case 'event_cat':
			if ( $hide_panel_cat ) {
				break;
			}

			if ( 'timeline-2' === $layout && $image_url ) {
				break;
			}

			if ( 'timeline-3' === $layout ) {
				if ( ! $rendered_tax_row && ( ( ! $hide_panel_cat && ! empty( $category_names ) ) || ( ! $hide_panel_tags && ! empty( $tag_names ) && teca_is_card_field_visible( 'event_tags', $visibility_settings ) ) ) ) {
					$rendered_tax_row = true;
					?>
					<div class="teca-timeline-3-tax-row">
						<?php if ( ! $hide_panel_cat && ! empty( $category_names ) ) : ?>
							<div class="<?php teca_print_card_visible_classes( 'event_cat', 'teca-event-categories gs-teca-categories teca-timeline-3-categories', $visibility_settings ); ?>">
								<?php foreach ( $category_names as $category_name ) : ?>
									<span class="teca-event-category gs-teca-category teca-timeline-3-category"><?php echo esc_html( $category_name ); ?></span>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
						<?php if ( ! $hide_panel_tags && ! empty( $tag_names ) && teca_is_card_field_visible( 'event_tags', $visibility_settings ) ) : ?>
							<div class="<?php teca_print_card_visible_classes( 'event_tags', 'teca-event-tags gs-teca-tags teca-timeline-3-tags', $visibility_settings ); ?>">
								<?php foreach ( $tag_names as $tag_name ) : ?>
									<span class="teca-event-tag gs-teca-tag teca-timeline-3-tag"><?php echo esc_html( $tag_name ); ?></span>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>
					<?php
				}
				break;
			}

			if ( empty( $category_names ) ) {
				break;
			}

			if ( 'timeline-2' === $layout && ! $image_url ) {
				$cat_wrap_class = 'teca-event-categories gs-teca-categories teca-timeline-2-categories teca-timeline-2-categories-inline';
			} else {
				$cat_wrap_class = 'teca-event-categories gs-teca-categories ' . esc_attr( $item_class_prefix ) . '-categories';
			}
			?>
			<div class="<?php teca_print_card_visible_classes( 'event_cat', $cat_wrap_class, $visibility_settings ); ?>">
				<?php foreach ( $category_names as $category_name ) : ?>
					<span class="teca-event-category gs-teca-category <?php echo esc_attr( $item_class_prefix ); ?>-category"><?php echo esc_html( $category_name ); ?></span>
				<?php endforeach; ?>
			</div>
			<?php
			break;

		case 'event_tags':
			if ( $hide_panel_tags || empty( $tag_names ) ) {
				break;
			}

			if ( 'timeline-2' === $layout ) {
				break;
			}

			if ( 'timeline-3' === $layout ) {
				if ( ! $rendered_tax_row ) {
					$rendered_tax_row = true;
					?>
					<div class="teca-timeline-3-tax-row">
						<?php if ( ! $hide_panel_cat && ! empty( $category_names ) && teca_is_card_field_visible( 'event_cat', $visibility_settings ) ) : ?>
							<div class="<?php teca_print_card_visible_classes( 'event_cat', 'teca-event-categories gs-teca-categories teca-timeline-3-categories', $visibility_settings ); ?>">
								<?php foreach ( $category_names as $category_name ) : ?>
									<span class="teca-event-category gs-teca-category teca-timeline-3-category"><?php echo esc_html( $category_name ); ?></span>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
						<div class="<?php teca_print_card_visible_classes( 'event_tags', 'teca-event-tags gs-teca-tags teca-timeline-3-tags', $visibility_settings ); ?>">
							<?php foreach ( $tag_names as $tag_name ) : ?>
								<span class="teca-event-tag gs-teca-tag teca-timeline-3-tag"><?php echo esc_html( $tag_name ); ?></span>
							<?php endforeach; ?>
						</div>
					</div>
					<?php
				}
				break;
			}
			?>
			<div class="<?php teca_print_card_visible_classes( 'event_tags', 'teca-event-tags gs-teca-tags ' . esc_attr( $item_class_prefix ) . '-tags', $visibility_settings ); ?>">
				<?php foreach ( $tag_names as $tag_name ) : ?>
					<span class="teca-event-tag gs-teca-tag <?php echo esc_attr( $item_class_prefix ); ?>-tag"><?php echo esc_html( $tag_name ); ?></span>
				<?php endforeach; ?>
			</div>
			<?php
			break;

		case 'event_venue':
			if ( ! $venue_display ) {
				break;
			}
			?>
			<div class="<?php teca_print_card_visible_classes( 'event_venue', 'teca-event-venue gs-teca-venue ' . esc_attr( $item_class_prefix ) . '-venue', $visibility_settings ); ?>">
				<?php echo esc_html( $venue_display ); ?>
			</div>
			<?php
			break;

		case 'event_organizer':
			$organizer = teca_get_event_organizer_name( $event );
			if ( ! $organizer ) {
				break;
			}
			?>
			<div class="<?php teca_print_card_visible_classes( 'event_organizer', 'teca-event-organizer gs-teca-organizers ' . esc_attr( $item_class_prefix ) . '-organizer', $visibility_settings ); ?>">
				<?php echo esc_html( $organizer ); ?>
			</div>
			<?php
			break;

		case 'event_details':
			if ( ! $excerpt ) {
				break;
			}
			?>
			<div class="<?php teca_print_card_visible_classes( 'event_details', 'teca-event-details teca-event-excerpt gs-teca-desc gs-teca-details ' . esc_attr( $item_class_prefix ) . '-excerpt', $visibility_settings ); ?>">
				<?php echo esc_html( $excerpt ); ?>
			</div>
			<?php
			break;

		case 'view_details_button':
			if ( ! $show_button || ! $event_id || ! teca_is_view_details_button_visible( $visibility_settings ) ) {
				break;
			}

			$button_class = 'teca-event-button gs-teca-action-btn ' . esc_attr( $item_class_prefix ) . '-button';
			$link_type    = $link_context['link_type'] ?? 'none';

			if ( in_array( $link_type, array( 'popup', 'single_page' ), true ) ) {
				$button_html = teca_get_card_link_html( $event_id, esc_html( $button_text ), $link_context, $button_class );
			} elseif ( get_the_permalink( $event_id ) ) {
				$button_html = sprintf(
					'<a class="%s" href="%s">%s</a>',
					esc_attr( $button_class ),
					esc_url( get_the_permalink( $event_id ) ),
					esc_html( $button_text )
				);
			} else {
				$button_html = '';
			}

			if ( ! empty( $button_html ) ) {
				?>
				<div class="<?php teca_print_card_visible_classes( 'view_details_button', 'gs-teca-view-details teca-event-actions ' . esc_attr( $item_class_prefix ) . '-view-details', $visibility_settings ); ?>">
					<?php echo wp_kses_post( $button_html ); ?>
				</div>
				<?php
			}
			break;

		case 'google_calendar_button':
			if ( ! $event_id ) {
				break;
			}

			teca_echo_google_calendar_button_actions(
				$event_id,
				'card',
				$visibility_settings,
				$item_class_prefix . '-google-calendar-wrap',
				array(
					'google_calendar_url' => $event['google_calendar_url'] ?? '',
				)
			);
			break;
	}
}

if ( $cost_display && ! teca_layout_suppresses_card_event_cost( $layout ) ) {
	?>
	<div class="teca-event-cost <?php echo esc_attr( $item_class_prefix ); ?>-cost">
		<?php echo esc_html( $cost_display ); ?>
	</div>
	<?php
}

// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
