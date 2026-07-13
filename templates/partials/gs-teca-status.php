<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally GS_TECA.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and may be shared with included partial templates.

$event_id     = 0;
$status       = '';
$status_class = '';

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
 * 🔹 Step 2: Resolve event status safely
 */
if ( $event_id > 0 ) {

    // TEC official helpers (preferred)
    if ( function_exists( 'tribe_is_event_cancelled' ) && tribe_is_event_cancelled( $event_id ) ) {
        $status       = __( 'Cancelled', 'the-events-calendar-addon' );
        $status_class = 'is-cancelled';
    }
    elseif ( function_exists( 'tribe_is_event_postponed' ) && tribe_is_event_postponed( $event_id ) ) {
        $status       = __( 'Postponed', 'the-events-calendar-addon' );
        $status_class = 'is-postponed';
    }
    elseif ( function_exists( 'tribe_event_is_over' ) && tribe_event_is_over( $event_id ) ) {
        $status       = __( 'Past Event', 'the-events-calendar-addon' );
        $status_class = 'is-past';
    }
    else {
        // Default upcoming / ongoing
        $status       = __( 'Upcoming Event', 'the-events-calendar-addon' );
        $status_class = 'is-upcoming';
    }
}
?>

<?php if ( $status ) : ?>
    <div class="gs-teca-status <?php echo esc_attr( $status_class ); ?>">
        <?php echo esc_html( $status ); ?>
    </div>
<?php endif; ?>

<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
