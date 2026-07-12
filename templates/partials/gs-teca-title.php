<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally GS_TECA.
namespace GS_TECA;
if ( ! defined( 'ABSPATH' ) ) exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and may be shared with included partial templates. 

$link_target = isset( $link_target ) ? $link_target : '_blank';
$link_type   = $gs_teca_link_type ?? 'none';
$event_id = 0;

if ( isset( $event['event_id'] ) && (int) $event['event_id'] > 0 ) {
    $event_id = (int) $event['event_id'];
}

if ( ! $event_id ) {
    $post = get_post();
    if ( $post instanceof \WP_Post ) {
        $event_id = $post->ID;
    }
}

$shortcode_id = $atts['id'];

$data_src = '#gs_teca_popup_' . $event_id . '_' . $shortcode_id;

$data_theme = 'gs-teca-popup-' . esc_attr($popup_style);

if ( $event_id > 0 ) {
    $title_text = get_the_title( $event_id );
}
    // ===== Title tag lock (FREE vs PRO) =====
    
    // Prepare link wrapper
    if ( $link_type === 'single_page' ) {
        $title_text = sprintf(
            '<a href="%s" target="%s">%s</a>',
            esc_url( get_the_permalink($event_id) ),
            esc_attr( $link_target ),
            esc_html( $title_text )
        );
    } elseif ( $link_type === 'popup' ) {
        $popup_link = get_the_popup_link( $id, $event_id );

		$popup_style = empty($popup_style) ? 'default' : $popup_style;

		$data_src     = "#gs_teca_popup_{$event_id}_{$shortcode_id}";		
		$data_theme   = 'gs-teca-popup-' . esc_attr($popup_style);

        $title_text = sprintf(
            '<a href="#" class="gs_teca_pop open-popup-link" data-mfp-src="%s" data-theme="%s">%s</a>',
            esc_attr($data_src),
            esc_attr($data_theme),
            esc_html( $title_text )
        );
    } else {
        // If no link, just keep plain text
        $title_text =  $title_text ;
    }

    // Output the title with dynamic tag and classes
    printf(
        '<div class="gs-teca-title">%s</div>',
        wp_kses_post($title_text) 
    );

?>

<?php
// namespace GS_TECA;

// if ( ! defined( 'ABSPATH' ) ) exit;

// $event_id = 0;
// $title    = '';

// if ( isset( $event['event_id'] ) && (int) $event['event_id'] > 0 ) {
//     $event_id = (int) $event['event_id'];
// }

// if ( ! $event_id ) {
//     $post = get_post();
//     if ( $post instanceof \WP_Post ) {
//         $event_id = $post->ID;
//     }
// }

// if ( $event_id > 0 ) {
//     $title = get_the_title( $event_id );
// }

// if ( ! $title && ! empty( $event['event_name'] ) ) {
//     $title = $event['event_name'];
// }
// 
?>

<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
