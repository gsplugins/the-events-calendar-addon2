<?php
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$visibility = teca_get_popup_visibility_settings( $popup_visibility_settings ?? null );
$event_data = isset( $event ) && is_array( $event ) ? $event : array();
$shortcode  = isset( $settings ) && is_array( $settings ) ? $settings : array();
?>

<div class="teca-popup teca-popup-default">
	<div class="teca-popup-dialog">
		<div class="teca-popup-body">
			<?php
			teca_render_popup_elements(
				$event_data,
				$visibility,
				$popup_visibility_order ?? null,
				$shortcode
			);
			?>
		</div>
	</div>
</div>
