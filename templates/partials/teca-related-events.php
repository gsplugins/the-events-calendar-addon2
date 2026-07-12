<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally GS_TECA.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and may be shared with included partial templates.

$event_ids = isset( $event_ids ) && is_array( $event_ids ) ? $event_ids : array();
$title     = isset( $title ) ? (string) $title : __( 'Related Events', 'the-events-calendar-addon2' );
$settings  = isset( $settings ) && is_array( $settings ) ? $settings : array();
$context   = isset( $context ) ? sanitize_key( (string) $context ) : 'single';

$event_ids = array_values( array_filter( array_map( 'absint', $event_ids ) ) );

if ( empty( $event_ids ) ) {
	return;
}

$section_class = 'teca-related-events teca-related-events--' . sanitize_html_class( $context );
?>
<section class="<?php echo esc_attr( $section_class ); ?>">
	<h2 class="teca-related-events__title"><?php echo esc_html( $title ); ?></h2>
	<div class="teca-related-events__grid">
		<?php foreach ( $event_ids as $related_event_id ) : ?>
			<?php teca_render_related_event_card( $related_event_id, $settings ); ?>
		<?php endforeach; ?>
	</div>
</section>

<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
