<?php
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) exit;

$event_id    = 0;
$description = '';

/**
 * Resolve event ID from event array or global post.
 */
if ( isset( $event['event_id'] ) && (int) $event['event_id'] > 0 ) {
    $event_id = (int) $event['event_id'];
} else {
    $post = get_post();
    if ( $post instanceof \WP_Post ) {
        $event_id = $post->ID;
    }
}

/**
 * Resolve description from post content or passed event data.
 */
if ( $event_id > 0 ) {
    $event_post = get_post( $event_id );

    if ( $event_post instanceof \WP_Post ) {
        $description = $event_post->post_content;
    }
}

if ( ! $description && ! empty( $event['event_description'] ) ) {
    $description = $event['event_description'];
}

$details_length_type = $details_length_type ?? 'words';
$details_length      = isset( $details_length ) ? absint( $details_length ) : 25;

if ( $description ) {
    $description = Helpers::trim_event_details( $description, $details_length_type, $details_length );
}
?>

<?php if ( $description ) : ?>
    <div class="gs-teca-desc">
        <?php echo wp_kses_post( wpautop( $description ) ); ?>
    </div>
<?php endif; ?>
