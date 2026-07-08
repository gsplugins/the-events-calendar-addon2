<?php
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$filter_type = $filter_type ?? 'daily';
$layout_id   = $layout_id ?? 'teca';
$options     = $options ?? array();
$config      = $config ?? teca_get_calendar_filter_config( $filter_type );
$select_id   = 'teca-calendar-filter-' . $filter_type . '-' . $layout_id;
?>

<div class="teca-calendar-filter <?php echo esc_attr( $config['wrapper_class'] ); ?>" data-filter-type="<?php echo esc_attr( $filter_type ); ?>">
	<label class="teca-calendar-filter-label" for="<?php echo esc_attr( $select_id ); ?>">
		<?php echo esc_html( $config['label'] ); ?>
	</label>
	<select id="<?php echo esc_attr( $select_id ); ?>" class="teca-calendar-filter-select <?php echo esc_attr( $config['select_class'] ); ?>">
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

<div
	class="teca-calendar-filter-message <?php echo esc_attr( $config['message_class'] ); ?>"
	<?php if ( ! empty( $config['empty_template'] ) ) : ?>
		data-empty-template="<?php echo esc_attr( $config['empty_template'] ); ?>"
	<?php endif; ?>
	hidden
>
	<?php echo esc_html( $config['message'] ); ?>
</div>
