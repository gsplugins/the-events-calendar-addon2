<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally GS_TECA.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and may be shared with included partial templates.

$group_key    = $group_key ?? '';
$group_events = $group_events ?? array();
$group_title  = $group_title ?? '';
$empty_class  = $empty_class ?? '';
$empty_text   = $empty_text ?? '';
$item_class   = $item_class ?? 'teca-event-layout-1-item';
$item_partial = Template_Loader::locate_template( 'events/partials/event-item.php' );
?>
<section class="teca-events-group teca-events-group-<?php echo esc_attr( $group_key ); ?>" data-event-group="<?php echo esc_attr( $group_key ); ?>">
	<header class="teca-events-group-header">
		<h3 class="teca-events-group-title"><?php echo esc_html( $group_title ); ?></h3>
	</header>

	<?php if ( empty( $group_events ) ) : ?>
		<div class="teca-events-empty <?php echo esc_attr( $empty_class ); ?>"><?php echo esc_html( $empty_text ); ?></div>
	<?php else : ?>
		<div class="teca-events-group-items">
			<?php foreach ( $group_events as $event ) : ?>
				<?php
				$event_group = $group_key;
				if ( ! is_wp_error( $item_partial ) ) {
					include $item_partial;
				}
				?>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>

<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
