<?php
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) exit;

$event_id   = 0;
$author_id  = 0;
$author_name = '';
$author_url  = '';

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
 * 🔹 Step 2: Resolve author safely
 */
if ( $event_id > 0 ) {

    $event_post = get_post( $event_id );

    if ( $event_post instanceof \WP_Post ) {
        $author_id = (int) $event_post->post_author;
    }
}

/**
 * 🔹 Step 3: Build author data
 */
if ( $author_id > 0 ) {
    $author_name = get_the_author_meta( 'display_name', $author_id );
    $author_url  = get_author_posts_url( $author_id );
}
?>

<?php if ( $author_name ) : ?>
    <div class="gs-teca-author">

        <span class="gs-teca-author-label">
            <?php esc_html_e( 'By', 'the-events-calendar-addon' ); ?>
        </span>

        <span class="gs-teca-author-name">
            <?php echo esc_html( $author_name ); ?>
        </span>

    </div>
<?php endif; ?>
