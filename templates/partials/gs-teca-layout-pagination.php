<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally GS_TECA.
namespace GS_TECA;
if ( ! defined( 'ABSPATH' ) ) exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and may be shared with included partial templates.
/**
 * GS Team - Layout Pagination
 * @author GS Plugins <hello@gsplugins.com>
 * 
 * This template can be overridden by copying it to yourtheme/gs-team/partials/gs-team-layout-pagination.php
 * 
 * @package GS_Team/Templates
 * @version 1.0.0
 */

if( ! is_display_pagination( $carousel_enabled, $filter_enabled, $gs_teca_filter_type ) ) return;

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook name is kept for backward compatibility.
do_action( 'gs_teca_before_pagination' );

if( 'on' === $filter_enabled && 'normal-pagination' === $pagination_type && is_pro_active_and_valid() ) {
    $pagination_type = 'ajax-pagination';
}

?>


    <?php if ( 'normal-pagination' === $pagination_type ) : ?>

        <?php echo wp_kses_post(get_pagination( $id, $item_per_page,$gs_teca_found_events ?? 0 )); ?>

    <?php elseif ( 'ajax-pagination' === $pagination_type ) : ?>

        <div class="gs-teca-ajax-pagination-wrapper" data-posts-per-page="<?php echo esc_attr( $item_per_page ); ?>">
            <?php echo wp_kses_post(get_ajax_pagination( $id, $item_per_page, $gs_teca_paged ,$gs_teca_found_events ?? 0)); ?>
        </div>
        
    <?php elseif ( 'load-more-button' === $pagination_type ) : ?>

        <div class="gs-teca-load-more-wrapper">
            <button id="gs-teca-load-more-btn" class="gs-teca-load-more-btn"><?php echo esc_html( $load_button_text ); ?></button>
        </div>

    <?php elseif ( 'load-more-scroll' === $pagination_type ) : ?>

        <div id="gs-teca-load-more-scroll-<?php echo esc_attr( $id ); ?>" class = "gs-teca-load-more-scroll">
            <div class="gs-teca-loader-spinner" style="display: none;"><img src="<?php echo esc_url(GS_TECA_PLUGIN_URI . '/assets/img/loader.svg'); ?>" alt="Loader Image"></div>
        </div>

    <?php endif; ?>
    


<?php

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook name is kept for backward compatibility.
do_action( 'gs_teca_after_pagination' );

// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
