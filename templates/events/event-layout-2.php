<?php
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$featured_events  = $featured_events ?? array();
$past_events      = $past_events ?? array();
$upcoming_events  = $upcoming_events ?? array();
$default_tab      = 'upcoming';
$tab_order        = teca_get_events_layout_2_tab_order();
$groups_config    = teca_get_events_section_groups_config();
$panel_partial    = Template_Loader::locate_template( 'events/partials/event-layout-2-panel.php' );
$group_events_map = array(
	'featured'  => $featured_events,
	'past'      => $past_events,
	'upcoming'  => $upcoming_events,
);
?>
<div <?php echo teca_get_events_section_wrapper_attributes( $settings ?? array() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> data-default-tab="<?php echo esc_attr( $default_tab ); ?>">
	<div class="teca-events-layout-2-header">
		<h2 class="teca-events-layout-2-title">
			<span class="teca-events-layout-2-title-light"><?php esc_html_e( 'Popular', 'the-events-calendar-addon' ); ?></span>
			<span class="teca-events-layout-2-title-bold"><?php esc_html_e( 'Events', 'the-events-calendar-addon' ); ?></span>
		</h2>
	</div>

	<div class="teca-events-layout-2-tabs-wrap">
		<div class="teca-events-layout-2-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Event groups', 'the-events-calendar-addon' ); ?>">
			<?php foreach ( $tab_order as $group_key ) : ?>
				<?php
				$group_config = $groups_config[ $group_key ] ?? array();
				$is_active    = $default_tab === $group_key;
				?>
				<button
					type="button"
					class="teca-events-layout-2-tab <?php echo esc_attr( $group_config['tab_class'] ?? '' ); ?><?php echo $is_active ? ' is-active' : ''; ?>"
					data-event-group="<?php echo esc_attr( $group_key ); ?>"
					role="tab"
					aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
					aria-controls="teca-events-layout-2-panel-<?php echo esc_attr( $group_key ); ?>"
				>
					<?php echo esc_html( $group_config['label'] ?? '' ); ?>
				</button>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="teca-events-layout-2-panels">
		<?php
		if ( ! is_wp_error( $panel_partial ) ) {
			foreach ( $tab_order as $group_key ) {
				$group_config = $groups_config[ $group_key ] ?? array();
				$group_events = $group_events_map[ $group_key ] ?? array();
				$is_active    = $default_tab === $group_key;
				include $panel_partial;
			}
		}
		?>
	</div>
</div>
