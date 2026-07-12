<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally GS_TECA.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and may be shared with included partial templates.

$featured_events = $featured_events ?? array();
$past_events     = $past_events ?? array();
$upcoming_events = $upcoming_events ?? array();
$default_tab     = 'upcoming';
$groups_config   = teca_get_events_layout_1_groups_config();
$panel_partial   = Template_Loader::locate_template( 'events/partials/event-layout-1-panel.php' );
$group_events_map = array(
	'featured'  => $featured_events,
	'past'      => $past_events,
	'upcoming'  => $upcoming_events,
);
?>
<div <?php echo teca_get_events_section_wrapper_attributes( $settings ?? array() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> data-default-tab="<?php echo esc_attr( $default_tab ); ?>">
	<div class="teca-events-layout-1-hero">
		<div class="teca-events-layout-1-hero-sliders">
			<?php foreach ( $groups_config as $group_key => $group_config ) : ?>
				<?php
				$group_events = teca_get_events_section_group_events( $group_key, $featured_events, $past_events, $upcoming_events );
				$hero_images  = teca_get_events_hero_images( $group_events, 'large' );
				$is_active    = $default_tab === $group_key;
				?>
				<div
					class="teca-events-layout-1-hero-slider<?php echo $is_active ? ' is-active' : ''; ?>"
					data-event-group-slide="<?php echo esc_attr( $group_key ); ?>"
					<?php echo $is_active ? '' : 'hidden'; ?>
				>
					<?php if ( empty( $hero_images ) ) : ?>
						<div class="teca-events-layout-1-slide teca-events-layout-1-slide--fallback is-active" aria-hidden="true"></div>
					<?php else : ?>
						<?php foreach ( $hero_images as $index => $image ) : ?>
							<div
								class="teca-events-layout-1-slide<?php echo 0 === $index ? ' is-active' : ''; ?>"
								data-event-group-slide="<?php echo esc_attr( $group_key ); ?>"
								style="background-image:url('<?php echo esc_url( $image['url'] ); ?>')"
								role="img"
								aria-label="<?php echo esc_attr( $image['alt'] ); ?>"
							></div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="teca-events-layout-1-hero-overlay">
			<div class="teca-events-layout-1-hero-copy">
				<h2 class="teca-events-layout-1-hero-title"><?php esc_html_e( 'Events', 'the-events-calendar-addon2' ); ?></h2>
				<p class="teca-events-layout-1-hero-subtitle"><?php esc_html_e( 'Discover and explore featured, past, and upcoming events.', 'the-events-calendar-addon2' ); ?></p>
			</div>
		</div>
	</div>

	<div class="teca-events-layout-1-tabs-wrap">
		<div class="teca-events-layout-1-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Event groups', 'the-events-calendar-addon2' ); ?>">
			<?php foreach ( $groups_config as $group_key => $group_config ) : ?>
				<?php $is_active = $default_tab === $group_key; ?>
				<button
					type="button"
					class="teca-events-layout-1-tab <?php echo esc_attr( $group_config['tab_class'] ); ?><?php echo $is_active ? ' is-active' : ''; ?>"
					data-event-group="<?php echo esc_attr( $group_key ); ?>"
					role="tab"
					aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
					aria-controls="teca-events-layout-1-panel-<?php echo esc_attr( $group_key ); ?>"
				>
					<span class="teca-events-layout-1-tab-label"><?php echo esc_html( $group_config['label'] ); ?></span>
				</button>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="teca-events-layout-1-panels-wrap">
		<div class="teca-events-layout-1-panels">
			<?php
			if ( ! is_wp_error( $panel_partial ) ) {
				foreach ( $groups_config as $group_key => $group_config ) {
					$group_events = $group_events_map[ $group_key ] ?? array();
					$is_active    = $default_tab === $group_key;
					include $panel_partial;
				}
			}
			?>
		</div>
	</div>
</div>

<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
