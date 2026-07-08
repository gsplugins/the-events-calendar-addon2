<?php
namespace GS_TECA;

$popup_style = isset( $atts['popup_style'] ) ? sanitize_key( $atts['popup_style'] ) : 'default';
$popup_style = teca_resolve_popup_style_for_context( $popup_style );

$popup_settings = array();

if ( isset( $settings ) && is_array( $settings ) ) {
	$popup_settings = $settings;
} elseif ( isset( $atts ) && is_array( $atts ) ) {
	$popup_settings = $atts;
}

// Do not set popup date format variables in the event loop scope.
// They leak into the next event card and break layout date formatting.

// If not Pro and style isn’t "default", always fallback to "default"
// if ( ( ! is_pro_active() ) && $popup_style !== 'default' ) {
//     $popup_style = 'default';
// }
$event_id = 0;

// Preview safe event ID
if ( isset( $event['event_id'] ) && $event['event_id'] > 0 ) {
    $event_id = (int) $event['event_id'];
}

// Fallback to global post only if valid
if ( ! $event_id ) {
    $post = get_post();
    if ( $post instanceof \WP_Post ) {
        $event_id = $post->ID;
    }
}
?>
<div id="gs_teca_popup_<?php echo esc_attr($event_id); ?>_<?php echo esc_attr($id); ?>"
    class="gs_teca_popup_shortcode_<?php echo esc_attr($id); ?> white-popup mfp-hide mfp-with-anim gs_teca_popup <?php echo esc_attr('gs-teca-popup-' . $popup_style); ?>">
  <div class="mfp-content--container">
    <?php
    switch ($popup_style) {
        case 'style-one':
            include Template_Loader::locate_template('popups/gs-teca-popup-style-one.php');
            break;
        case 'style-two':
            include Template_Loader::locate_template('popups/gs-teca-popup-style-two.php');
            break;
        case 'style-three':
            include Template_Loader::locate_template('popups/gs-teca-popup-style-three.php');
            break;
        case 'style-four':
            include Template_Loader::locate_template('popups/gs-teca-popup-style-four.php');
            break;
        case 'style-five':
            include Template_Loader::locate_template('popups/gs-teca-popup-style-five.php');
            break;
        case 'style-six':
            include Template_Loader::locate_template('popups/gs-teca-popup-style-six.php');
            break;
        case 'default':
        default:
            include Template_Loader::locate_template('popups/gs-teca-popup-default.php');
            break;
    }
    ?>
  </div>
</div>
