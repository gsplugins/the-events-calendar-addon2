<?php
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$filter_mode   = sanitize_key( (string) ( $filter_mode ?? 'daily' ) );
$layout_id     = sanitize_key( (string) ( $layout_id ?? 'teca' ) );
$events        = $events ?? array();
$filter_modes  = teca_get_calendar_select_filter_options();
$mode_fields   = array( 'daily', 'weekly', 'monthly', 'quarterly', 'yearly' );
$bar_id        = 'teca-calendar-filter-bar-' . $layout_id;
?>

<div class="teca-calendar-filter-bar" data-filter-mode="<?php echo esc_attr( $filter_mode ); ?>" id="<?php echo esc_attr( $bar_id ); ?>">
	<div class="teca-calendar-filter-field teca-calendar-filter-mode-field">
		<label class="teca-calendar-filter-label" for="<?php echo esc_attr( $bar_id ); ?>-mode">
			<?php esc_html_e( 'Select Filter', 'the-events-calendar-addon' ); ?>
		</label>
		<select id="<?php echo esc_attr( $bar_id ); ?>-mode" class="teca-calendar-filter-select teca-calendar-filter-mode">
			<?php foreach ( $filter_modes as $option ) : ?>
				<option value="<?php echo esc_attr( $option['value'] ); ?>"<?php selected( $filter_mode, $option['value'] ); ?>>
					<?php echo esc_html( $option['label'] ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>

	<?php foreach ( $mode_fields as $mode ) : ?>
		<?php
		$options     = teca_get_calendar_filter_options( $events, $mode );
		$config      = teca_get_calendar_filter_config( $mode );
		$select_id   = $bar_id . '-' . $mode;
		$field_class = 'teca-calendar-filter-field teca-calendar-' . $mode . '-field';
		$is_hidden   = $filter_mode !== $mode;
		?>
		<div class="<?php echo esc_attr( $field_class ); ?>"<?php echo $is_hidden ? ' hidden' : ''; ?>>
			<label class="teca-calendar-filter-label" for="<?php echo esc_attr( $select_id ); ?>">
				<?php echo esc_html( $config['label'] ); ?>
			</label>
			<select
				id="<?php echo esc_attr( $select_id ); ?>"
				class="teca-calendar-filter-select teca-calendar-filter-value teca-calendar-<?php echo esc_attr( $mode ); ?>-select"
				data-filter-mode="<?php echo esc_attr( $mode ); ?>"
			>
				<?php foreach ( $options as $value => $label ) : ?>
					<?php
					if ( is_array( $label ) ) {
						$option_value = isset( $label['value'] ) ? (string) $label['value'] : (string) $value;
						$option_label = isset( $label['label'] ) ? (string) $label['label'] : $option_value;
					} else {
						$option_value = (string) $value;
						$option_label = (string) $label;

						if ( preg_match( '/^\d{4}$/', $option_label ) && is_numeric( $option_value ) && 'all' !== $option_value ) {
							$option_value = $option_label;
						}
					}
					?>
					<option value="<?php echo esc_attr( $option_value ); ?>"><?php echo esc_html( $option_label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	<?php endforeach; ?>

	<div
		class="teca-calendar-filter-message teca-calendar-filter-empty-message"
		data-empty-filter-message="<?php echo esc_attr__( 'No events found for the selected filter.', 'the-events-calendar-addon' ); ?>"
		data-empty-all-message="<?php echo esc_attr__( 'No events found.', 'the-events-calendar-addon' ); ?>"
		hidden
	>
		<?php esc_html_e( 'No events found for the selected filter.', 'the-events-calendar-addon' ); ?>
	</div>
</div>
