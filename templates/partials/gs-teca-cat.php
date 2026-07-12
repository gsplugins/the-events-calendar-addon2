<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally GS_TECA.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and may be shared with included partial templates.

$event_id   = 0;
$categories = [];


if ( isset( $event['event_id'] ) && (int) $event['event_id'] > 0 ) {
    $event_id = (int) $event['event_id'];
} else {
    $post = get_post();
    if ( $post instanceof \WP_Post ) {
        $event_id = $post->ID;
    }
}


if ( $event_id > 0 ) {

    $terms = get_the_terms( $event_id, 'tribe_events_cat' );

    if ( ! is_wp_error( $terms ) && ! empty( $terms ) && is_array( $terms ) ) {
        foreach ( $terms as $term ) {
            if ( isset( $term->name ) ) {
                $categories[] = $term->name;
            }
        }
    }
}
?>

<?php if ( ! empty( $categories ) ) : ?>
    <div class="teca-event-categories gs-teca-categories<?php echo ! empty( $popup_detail ) ? ' teca-popup-detail-categories' : ''; ?>">
        <?php foreach ( $categories as $cat ) : ?>
            <span class="teca-event-category gs-teca-category<?php echo ! empty( $popup_detail ) ? ' teca-popup-detail-category' : ''; ?>">
                <?php echo esc_html( $cat ); ?>
            </span>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
