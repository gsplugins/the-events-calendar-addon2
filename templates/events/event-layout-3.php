<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally GS_TECA.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and may be shared with included partial templates.

$featured_events  = $featured_events ?? array();
$past_events      = $past_events ?? array();
$upcoming_events  = $upcoming_events ?? array();
$default_tab      = 'upcoming';
$tab_order        = teca_get_events_layout_3_tab_order();
$groups_config    = teca_get_events_section_groups_config();
$panel_partial    = Template_Loader::locate_template( 'events/partials/event-layout-3-panel.php' );
$group_events_map = array(
	'featured'  => $featured_events,
	'past'      => $past_events,
	'upcoming'  => $upcoming_events,
);
$default_config   = $groups_config[ $default_tab ] ?? array();
?>
<div <?php echo teca_get_events_section_wrapper_attributes( $settings ?? array() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> data-default-tab="<?php echo esc_attr( $default_tab ); ?>">
	<div class="teca-events-layout-3-shell">
		<div class="teca-events-layout-3-header">
			<div class="teca-events-layout-3-branding">
				<h2 class="teca-events-layout-3-brand-title"><?php esc_html_e( 'Events', 'the-events-calendar-addon' ); ?></h2>
			</div>

			<div class="teca-events-layout-3-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Event groups', 'the-events-calendar-addon' ); ?>">
				<?php foreach ( $tab_order as $group_key ) : ?>
					<?php
					$group_config = $groups_config[ $group_key ] ?? array();
					$is_active    = $default_tab === $group_key;
					?>
					<button
						type="button"
						class="teca-events-layout-3-tab <?php echo esc_attr( $group_config['tab_class'] ?? '' ); ?><?php echo $is_active ? ' is-active' : ''; ?>"
						data-event-group="<?php echo esc_attr( $group_key ); ?>"
						data-group-label="<?php echo esc_attr( $group_config['label'] ?? '' ); ?>"
						role="tab"
						aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
						aria-controls="teca-events-layout-3-panel-<?php echo esc_attr( $group_key ); ?>"
					>
						<?php echo esc_html( $group_config['label'] ?? '' ); ?>
					</button>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="teca-events-layout-3-hero">
			<div class="teca-events-layout-3-hero-sliders">
				<?php foreach ( $tab_order as $group_key ) : ?>
					<?php
					$group_events = teca_get_events_section_group_events( $group_key, $featured_events, $past_events, $upcoming_events );
					$hero_images  = teca_get_events_hero_images( $group_events, 'large' );
					$is_active    = $default_tab === $group_key;
					?>
					<div
						class="teca-events-layout-3-hero-slider<?php echo $is_active ? ' is-active' : ''; ?>"
						data-event-group-slide="<?php echo esc_attr( $group_key ); ?>"
						<?php echo $is_active ? '' : 'hidden'; ?>
					>
						<?php if ( empty( $hero_images ) ) : ?>
							<div class="teca-events-layout-3-slide teca-events-layout-3-slide--fallback is-active" aria-hidden="true"></div>
						<?php else : ?>
							<?php foreach ( $hero_images as $index => $image ) : ?>
								<div
									class="teca-events-layout-3-slide<?php echo 0 === $index ? ' is-active' : ''; ?>"
									data-event-group-slide="<?php echo esc_attr( $group_key ); ?>"
									data-slide-title="<?php echo esc_attr( $image['alt'] ); ?>"
									style="background-image:url('<?php echo esc_url( $image['url'] ); ?>')"
									role="img"
									aria-label="<?php echo esc_attr( $image['alt'] ); ?>"
								></div>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="teca-events-layout-3-hero-content">
				<h3 class="teca-events-layout-3-hero-title" data-hero-title>
					<?php echo esc_html( $default_config['label'] ?? __( 'Events', 'the-events-calendar-addon' ) ); ?>
				</h3>
				<p class="teca-events-layout-3-hero-subtitle">
					<?php esc_html_e( 'Discover events curated for you.', 'the-events-calendar-addon' ); ?>
				</p>
			</div>
		</div>

		<div class="teca-events-layout-3-events">
			<div class="teca-events-layout-3-events-heading">
				<h3 class="teca-events-layout-3-events-title" data-events-title>
					<?php echo esc_html( $default_config['label'] ?? __( 'Events', 'the-events-calendar-addon' ) ); ?>
				</h3>
			</div>

			<div class="teca-events-layout-3-panels">
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
	</div>
</div>

<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
