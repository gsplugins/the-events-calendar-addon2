<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally GS_TECA.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and may be shared with included partial templates.

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

?>

<div class="gs-teca-img">
    <?php echo get_the_post_thumbnail($event_id,'full', ['class' => 'gs-teca-single-thumbnail']);  ?>
</div>

<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
