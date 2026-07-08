<?php
namespace GS_TECA;

use GS_TECA\Helpers;

if ( ! defined( 'ABSPATH' ) ) exit;

foreach ( $events as $event ) :

  $event_id     = (int) ( $event['event_id'] ?? 0 );
  $time_display = $event_id ? teca_format_event_start_time_display( $event_id ) : '';

  $classes = [
    'gs-teca-single',
    'teca-grid-style-1-item',
    get_col_classes(
      $settings['columns'],
      $settings['columns_tablet'],
      $settings['columns_mobile_portrait'],
      $settings['columns_mobile']
    ),
  ];

  if ( $gs_teca_link_type == 'popup' ) $classes[] = 'single-member-pop';
  
  $term_classes = gs_teca_get_the_term_classes($event['event_id'],$view_type,$gs_filters_by);
  if ( ! is_wp_error( $term_classes ) && ! empty( $term_classes ) ) $classes[] = $term_classes;
?>

  <div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"<?php echo teca_get_event_filter_attributes_html( $event ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

    <article class="gs-teca-event-main teca-grid-style-1-card">

      <!-- Featured Image -->
      <?php if ( Helpers::is_visible( $visibility_settings['event_thumbnail'] ?? true ) ) : ?>
      <div class="gs-teca-event-img-wrapper teca-grid-style-1-thumb">
          <div class="<?php Helpers::print_visible_classes( $visibility_settings['event_thumbnail'], 'gs-teca-thumbnail-wrapper teca-event-thumb' ); ?>">
            <?php include Template_Loader::locate_template( 'partials/gs-teca-event-thumbnail.php' ); ?>
          </div>
      </div>
      <?php endif; ?>

      <!-- Content -->
      <div class="gs-teca-glass-content teca-grid-style-1-content">

        <?php
        $show_date = Helpers::is_visible( $visibility_settings['event_date'] ?? true );
        $show_cat  = Helpers::is_visible( $visibility_settings['event_cat'] ?? true );
        ?>
        <?php if ( $show_date || $show_cat ) : ?>
        <div class="teca-grid-style-1-topbar">
          <?php if ( $show_date ) : ?>
            <div class="<?php Helpers::print_visible_classes( $visibility_settings['event_date'], 'gs-teca-glass-date teca-event-date' ); ?>">
              <?php include Template_Loader::locate_template('partials/gs-teca-date.php'); ?>
            </div>
          <?php endif; ?>

          <?php if ( $show_cat ) : ?>
            <div class="<?php Helpers::print_visible_classes( $visibility_settings['event_cat'], 'gs-teca-categories teca-event-categories' ); ?>">
              <?php include Template_Loader::locate_template('partials/gs-teca-cat.php'); ?>
            </div>
          <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Title -->
        <?php if ( Helpers::is_visible( $visibility_settings['event_title'] ?? true ) ) : ?>
          <div class="<?php Helpers::print_visible_classes( $visibility_settings['event_title'], 'gs-teca-title teca-grid-style-1-title teca-event-title' ); ?>">
            <?php include Template_Loader::locate_template('partials/gs-teca-title.php'); ?>
          </div>
        <?php endif; ?>


        <?php
          $has_meta = ( $time_display && Helpers::is_visible( $visibility_settings['event_date'] ?? true ) )
            || Helpers::is_visible( $visibility_settings['event_venue'] ?? true )
            || Helpers::is_visible( $visibility_settings['event_organizer'] ?? true );
        ?>
        <?php if ( $has_meta ) : ?>
        <div class="gs-teca-glass-meta teca-grid-style-1-meta teca-event-meta">
          <?php if ( $time_display && Helpers::is_visible( $visibility_settings['event_date'] ?? true ) ) : ?>
            <span class="teca-event-time"><?php echo esc_html( $time_display ); ?></span>
          <?php endif; ?>

          <!-- Venue -->
          <?php if ( Helpers::is_visible( $visibility_settings['event_venue'] ?? true ) ) : ?>
            <div class="<?php Helpers::print_visible_classes( $visibility_settings['event_venue'], 'gs-teca-venue teca-event-venue' ); ?>">
              <?php include Template_Loader::locate_template('partials/gs-teca-venue.php', $venue_props = ['title' => true, 'city' => false, 'state' => false, 'zip' => false, 'country' => false, 'address' => false]); ?>
            </div>
          <?php endif; ?>

          <!-- Organizer -->
          <?php if ( Helpers::is_visible( $visibility_settings['event_organizer'] ?? true ) ) : ?>
            <div class="<?php Helpers::print_visible_classes( $visibility_settings['event_organizer'], 'gs-teca-organizer teca-event-organizer' ); ?>">
              <?php include Template_Loader::locate_template('partials/gs-teca-organizer.php', $organizer_props = ['title' => true, 'phone' => false, 'email' => false, 'url' => false ]); ?>
            </div>
          <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Details -->
        <?php if ( Helpers::is_visible( $visibility_settings['event_details'] ?? true ) ) : ?>
          <div class="<?php Helpers::print_visible_classes( $visibility_settings['event_details'], 'gs-teca-desc teca-grid-style-1-excerpt teca-event-excerpt' ); ?>">
            <?php include Template_Loader::locate_template('partials/gs-teca-details.php'); ?>
          </div>
        <?php endif; ?>

        <?php
        teca_echo_google_calendar_button_actions(
          $event_id,
          'card',
          $visibility_settings ?? null,
          'teca-grid-style-1-google-calendar-wrap',
          array( 'google_calendar_url' => $event['google_calendar_url'] ?? '' )
        );
        ?>

        <?php
          $show_footer_tags = Helpers::is_visible( $visibility_settings['event_tags'] ?? true );
          $show_footer_link = teca_should_show_view_details_button( $visibility_settings ?? null, $gs_teca_link_type );
          if ( $show_footer_tags || $show_footer_link ) :
        ?>
        <div class="teca-grid-style-1-footer">
          <?php if ( $show_footer_tags ) : ?>
          <div class="<?php Helpers::print_visible_classes( $visibility_settings['event_tags'], 'gs-teca-tag teca-event-tags' ); ?>">
            <?php include Template_Loader::locate_template('partials/gs-teca-tag.php'); ?>
          </div>
          <?php endif; ?>

          <?php if ( $show_footer_link ) : ?>
          <div class="<?php teca_print_card_visible_classes( 'view_details_button', 'gs-teca-view-details', $visibility_settings ?? null ); ?>">
            <?php
            echo wp_kses_post(
              teca_get_view_details_button_html(
                $event_id,
                array(
                  'link_context' => teca_build_theme_link_context( $gs_teca_link_type, $atts['id'], $popup_style ?? 'default', $link_target ?? '_blank' ),
                  'button_class' => 'teca-event-button teca-grid-style-1-button',
                  'inner_html'   => '<span>' . esc_html( teca_get_view_details_text() ) . '</span>',
                )
              )
            );
            ?>
          </div>
          <?php endif; ?>
        </div>
        <?php endif; ?>

      </div>

    </article>

    <?php include Template_Loader::locate_template('popups/gs-teca-layout-popup.php') ?>

  </div>

<?php endforeach; ?>
