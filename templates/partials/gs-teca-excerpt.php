<?php
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) exit;

$event_id = 0;
$excerpt  = '';

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
 * 🔹 Step 2: Resolve excerpt safely
 */
if ( $event_id > 0 ) {

    $event_post = get_post( $event_id );

    if ( $event_post instanceof \WP_Post ) {

        // 1️⃣ If manual excerpt exists
        if ( ! empty( $event_post->post_excerpt ) ) {
            $excerpt = $event_post->post_excerpt;
        }
        // 2️⃣ Auto-generate excerpt from content
        elseif ( ! empty( $event_post->post_content ) ) {
            $excerpt = wp_trim_words(
                wp_strip_all_tags( $event_post->post_content ),
                25,
                '…'
            );
        }
    }
}

/**
 * 🔹 Step 3: Final fallback (builder data)
 */
if ( ! $excerpt && ! empty( $event['event_excerpt'] ) ) {
    $excerpt = $event['event_excerpt'];
}
?>

<?php if ( $excerpt ) : ?>
    <div class="gs-teca-excerpt">
        <?php echo esc_html( $excerpt ); ?>
    </div>
<?php endif; ?>
