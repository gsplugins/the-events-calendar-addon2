<?php
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$group_key    = $group_key ?? '';
$group_events = $group_events ?? array();
$group_config = $group_config ?? array();
$is_active    = ! empty( $is_active );
$item_partial = Template_Loader::locate_template( 'events/partials/event-layout-1-item.php' );

$panel_class = $group_config['panel_class'] ?? 'teca-events-panel-' . $group_key;
$empty_class = $group_config['empty_class'] ?? 'teca-events-empty-' . $group_key;
$empty_text  = $group_config['empty_text'] ?? '';
?>
<div
	class="teca-events-layout-1-panel <?php echo esc_attr( $panel_class ); ?><?php echo $is_active ? ' is-active' : ''; ?>"
	id="teca-events-layout-1-panel-<?php echo esc_attr( $group_key ); ?>"
	data-event-group-panel="<?php echo esc_attr( $group_key ); ?>"
	role="tabpanel"
	<?php echo $is_active ? '' : 'hidden'; ?>
>
	<?php if ( empty( $group_events ) ) : ?>
		<div class="teca-events-empty <?php echo esc_attr( $empty_class ); ?>"><?php echo esc_html( $empty_text ); ?></div>
	<?php else : ?>
		<div class="teca-events-layout-1-list">
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
</div>
