<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally GS_TECA.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and may be shared with included partial templates.

$event_id   = 0;
$start_date = '';
$end_date   = '';
$all_day    = false;

if ( isset( $event['event_id'] ) && (int) $event['event_id'] > 0 ) {
	$event_id = (int) $event['event_id'];
} else {
	$post = get_post();
	if ( $post instanceof \WP_Post ) {
		$event_id = $post->ID;
	}
}

if ( $event_id > 0 ) {
	$start_date = get_post_meta( $event_id, '_EventStartDate', true );
	$end_date   = get_post_meta( $event_id, '_EventEndDate', true );
	$all_day    = (bool) get_post_meta( $event_id, '_EventAllDay', true );
}

$format_args = array();
$scope       = teca_is_popup_date_format_scope() ? 'popup' : '';

if ( isset( $settings ) && is_array( $settings ) ) {
	$format_args['settings'] = $settings;
}

if ( 'popup' === $scope ) {
	$format_args['scope'] = 'popup';

	if ( isset( $GLOBALS['teca_date_format_layout_key'] ) ) {
		$format_args['layout_key'] = (string) $GLOBALS['teca_date_format_layout_key'];
	}

	if ( isset( $GLOBALS['teca_date_format_settings'] ) && is_array( $GLOBALS['teca_date_format_settings'] ) ) {
		$format_args['settings'] = $GLOBALS['teca_date_format_settings'];
	}
}

$date_context    = teca_resolve_date_format_render_context( $format_args );
$layout_key      = $date_context['layout_key'];
$format_settings = $date_context['settings'];
$start_formatted = '';
$end_formatted   = '';

if ( $event_id > 0 ) {
	$start_formatted = teca_format_event_start_date_text( $event_id, $layout_key, $format_settings );
	$end_formatted   = teca_format_event_end_date_text( $event_id, $layout_key, $format_settings );
} elseif ( $start_date ) {
	$start_formatted = teca_format_layout_date_string( $start_date, $layout_key, $format_settings );
}

if ( $end_date && ! $event_id ) {
	$end_formatted = teca_format_layout_date_string( $end_date, $layout_key, $format_settings );
}
?>

<?php if ( $start_formatted ) : ?>
	<div class="gs-teca-date">
		<svg
			xmlns="http://www.w3.org/2000/svg"
			width="20"
			height="20"
			viewBox="0 0 24 24"
			fill="none"
			stroke="currentColor"
			stroke-width="1.8"
			stroke-linecap="round"
			stroke-linejoin="round"
			class="gs-teca-icon gs-teca-icon-date"
		>
			<line x1="8" y1="2.5" x2="8" y2="6"></line>
			<line x1="16" y1="2.5" x2="16" y2="6"></line>
			<rect x="3" y="4.5" width="18" height="16" rx="3" ry="3"></rect>
			<line x1="3" y1="9" x2="21" y2="9"></line>
			<circle cx="12" cy="14.5" r="1.8"></circle>
		</svg>

		<span
			class="gs-teca-date-start teca-date-text"
			<?php if ( $event_id ) : ?>
				data-event-id="<?php echo esc_attr( (string) $event_id ); ?>"
			<?php endif; ?>
			<?php if ( $start_date ) : ?>
				data-start-date="<?php echo esc_attr( (string) $start_date ); ?>"
				data-start-timestamp="<?php echo esc_attr( (string) strtotime( (string) $start_date ) ); ?>"
			<?php endif; ?>
		><?php echo esc_html( $start_formatted ); ?></span>

		<?php if ( ! $all_day && $end_formatted && $end_formatted !== $start_formatted ) : ?>
			<span class="gs-teca-date-separator">–</span>
			<span
				class="gs-teca-date-end teca-date-text"
				<?php if ( $event_id ) : ?>
					data-event-id="<?php echo esc_attr( (string) $event_id ); ?>"
				<?php endif; ?>
				<?php if ( $end_date ) : ?>
					data-end-date="<?php echo esc_attr( (string) $end_date ); ?>"
					data-end-timestamp="<?php echo esc_attr( (string) strtotime( (string) $end_date ) ); ?>"
				<?php endif; ?>
			><?php echo esc_html( $end_formatted ); ?></span>
		<?php endif; ?>

		<?php if ( $all_day ) : ?>
			<span class="gs-teca-date-allday">
				<?php esc_html_e( 'All Day', 'the-events-calendar-addon2' ); ?>
			</span>
		<?php endif; ?>

	</div>
<?php endif; ?>

<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
