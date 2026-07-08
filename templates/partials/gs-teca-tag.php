<?php
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) exit;

$event_id = 0;
$tags     = [];

/**
 * 🔹 Step 1: Safe event ID resolve
 * Priority: event array → fallback global post
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
 * 🔹 Step 2: Query event tags safely
 * Event tags use default WP taxonomy = post_tag
 */
if ( $event_id > 0 ) {

    $terms = get_the_terms( $event_id, 'post_tag' );

    if ( ! is_wp_error( $terms ) && ! empty( $terms ) && is_array( $terms ) ) {
        foreach ( $terms as $term ) {
            if ( isset( $term->name ) ) {
                $tags[] = $term->name;
            }
        }
    }
}
?>

<?php if ( ! empty( $tags ) ) : ?>
    <div class="teca-event-tags gs-teca-tags<?php echo ! empty( $popup_detail ) ? ' teca-popup-detail-tags' : ''; ?>">
        <?php foreach ( $tags as $tag ) : ?>
            <span class="teca-event-tag gs-teca-tag<?php echo ! empty( $popup_detail ) ? ' teca-popup-detail-tag' : ''; ?>">
                <?php echo esc_html( $tag ); ?>
            </span>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
