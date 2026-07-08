<?php
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$visibility = teca_get_popup_visibility_settings( $popup_visibility_settings ?? null );
$event_data = isset( $event ) && is_array( $event ) ? $event : array();
$shortcode  = isset( $settings ) && is_array( $settings ) ? $settings : array();
$has_media  = teca_popup_has_visible_image( $event_data, $visibility );
$exclude    = teca_get_popup_style_2_content_excludes( $event_data, $visibility );

$shortcode['teca_active_popup_style'] = 'style-2';
?>

<div class="teca-popup teca-popup-style-2<?php echo $has_media ? '' : ' teca-popup-no-image'; ?>">
	<div class="teca-popup-dialog">
		<div class="teca-popup-style-2-deck">
			<?php if ( $has_media ) : ?>
				<?php teca_render_popup_style_2_media_panel( $event_data, $visibility ); ?>
			<?php endif; ?>

			<div class="teca-popup-style-2-panel teca-popup-body">
				<?php
				teca_render_popup_elements(
					$event_data,
					$visibility,
					$popup_visibility_order ?? null,
					$shortcode,
					array(
						'exclude'        => $exclude,
						'wrap_meta_grid' => true,
					)
				);
				?>
			</div>
		</div>
	</div>
</div>
