<?php

namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) exit;
$event_id = 0;

if ( isset( $event['event_id'] ) && $event['event_id'] > 0 ) {
    $event_id = (int) $event['event_id'];
}

if ( ! $event_id ) {
    $post = get_post();
    if ( $post instanceof \WP_Post ) {
        $event_id = $post->ID;
    }
}
$shortcode_id = $atts['id'];
$popup_style = $atts['popup_style'];
$link_class = 'gs-teca-action-btn gs-teca-action-btn--popup gs_teca_pop open-popup-link';
$data_src     = "#gs_teca_popup_{$event_id}_{$shortcode_id}";		
$data_theme   = 'gs-teca-popup-' . esc_attr($popup_style);
$media = get_popup_media( $event_id );


?>

<?php printf( '<a class="%s" href="#" data-mfp-src="%s" data-theme="%s">', esc_attr($link_class), esc_attr($data_src), esc_attr($data_theme) ); ?>
    
    <svg width="28" height="17" viewBox="0 0 28 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14.0027 17C6.38949 17 0.427972 9.34661 0.176384 9.01839C-0.0587948 8.711 -0.0587948 8.289 0.176384 7.98161C0.427972 7.65339 6.38949 0 14.0027 0C21.616 0 27.5775 7.65339 27.8236 7.98161C28.0588 8.289 28.0588 8.711 27.8236 9.01839C27.5775 9.34661 21.616 17 14.0027 17ZM2.10157 8.5026C3.53999 10.1594 8.39672 15.2443 14.0027 15.2443C19.6197 15.2443 24.4709 10.1646 25.9039 8.5026C24.4655 6.84064 19.6088 1.76096 14.0027 1.76096C8.38031 1.76096 3.53453 6.83543 2.10157 8.5026Z" fill="currentColor"/><path d="M14.0027 13.7751C10.9509 13.7751 8.46235 11.4097 8.46235 8.4974C8.46235 5.58504 10.9509 3.22495 14.0027 3.22495C17.0546 3.22495 19.5431 5.59025 19.5431 8.50261C19.5431 11.415 17.0546 13.7751 14.0027 13.7751ZM14.0027 4.98069C11.9682 4.98069 10.311 6.5593 10.311 8.4974C10.311 10.4355 11.9682 12.0141 14.0027 12.0141C16.0373 12.0141 17.6945 10.4355 17.6945 8.4974C17.6945 6.5593 16.0373 4.98069 14.0027 4.98069Z" fill="currentColor"/></svg>
    
</a>