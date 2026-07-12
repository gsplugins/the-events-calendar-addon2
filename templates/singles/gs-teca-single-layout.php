<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally GS_TECA.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and may be shared with included partial templates.

$style_key = isset( $teca_single_page_style_key )
	? teca_normalize_single_page_style_key( $teca_single_page_style_key )
	: teca_get_single_page_style_key( teca_get_single_page_settings_for_current_event() );

teca_set_active_single_page_style_key( $style_key );
teca_set_date_format_context(
	teca_get_single_layout_date_key( $style_key ),
	teca_get_single_page_settings_for_current_event()
);
$style_class   = teca_get_single_page_style_class( $style_key );
$sorted_fields = teca_get_single_page_sorted_fields();
$event_payload = array( 'event_id' => get_the_ID() );

teca_maybe_render_single_page_status_badge_fallback( $sorted_fields, (int) get_the_ID() );

if ( 'default' === $style_key ) :
	$has_hero = teca_single_default_has_hero_image( $event_payload, $sorted_fields );
	?>
	<div class="teca-single-page <?php echo esc_attr( $style_class ); ?>" data-teca-single-style="<?php echo esc_attr( $style_key ); ?>">
		<div class="teca-single-default-shell">
			<?php if ( $has_hero ) : ?>
				<div class="teca-single-default-hero">
					<?php teca_render_single_page_element( 'event_thumbnail', $event_payload, $sorted_fields, $style_key ); ?>
				</div>
			<?php endif; ?>

			<div class="teca-single-default-main">
				<?php teca_render_single_default_page( $event_payload, $sorted_fields, $style_key ); ?>
			</div>
		</div>
	</div>
	<?php
elseif ( 'style-1' === $style_key ) :
	?>
	<div class="teca-single-page <?php echo esc_attr( $style_class ); ?>" data-teca-single-style="<?php echo esc_attr( $style_key ); ?>">
		<?php teca_render_single_style1_page( $event_payload, $sorted_fields, $style_key ); ?>
	</div>
	<?php
elseif ( 'style-2' === $style_key ) :
	$has_style2_media = teca_single_style2_has_ticket_media( $event_payload, $sorted_fields );
	$style2_class     = $style_class . ( $has_style2_media ? '' : ' teca-single-no-image' );
	?>
	<div class="teca-single-page <?php echo esc_attr( $style2_class ); ?>" data-teca-single-style="<?php echo esc_attr( $style_key ); ?>">
		<?php teca_render_single_style2_page( $event_payload, $sorted_fields, $style_key ); ?>
	</div>
	<?php
elseif ( 'style-3' === $style_key ) :
	$has_style3_media = teca_single_style3_has_hero_media( $event_payload, $sorted_fields );
	$style3_class     = $style_class . ( $has_style3_media ? '' : ' teca-single-no-image' );
	?>
	<div class="teca-single-page <?php echo esc_attr( $style3_class ); ?>" data-teca-single-style="<?php echo esc_attr( $style_key ); ?>">
		<?php teca_render_single_style3_page( $event_payload, $sorted_fields, $style_key ); ?>
	</div>
	<?php
elseif ( 'style-4' === $style_key ) :
	$has_style4_media = teca_single_style4_has_showcase_media( $event_payload, $sorted_fields );
	$style4_class     = $style_class . ( $has_style4_media ? '' : ' teca-single-no-image' );
	?>
	<div class="teca-single-page <?php echo esc_attr( $style4_class ); ?>" data-teca-single-style="<?php echo esc_attr( $style_key ); ?>">
		<?php teca_render_single_style4_page( $event_payload, $sorted_fields, $style_key ); ?>
	</div>
	<?php
elseif ( 'style-5' === $style_key ) :
	$has_style5_media = teca_single_style5_has_hero_media( $event_payload, $sorted_fields );
	$style5_class     = $style_class . ( $has_style5_media ? '' : ' teca-single-no-image' );
	?>
	<div class="teca-single-page <?php echo esc_attr( $style5_class ); ?>" data-teca-single-style="<?php echo esc_attr( $style_key ); ?>">
		<?php teca_render_single_style5_page( $event_payload, $sorted_fields, $style_key ); ?>
	</div>
	<?php
else :
	$content_class = 'teca-single-' . sanitize_html_class( $style_key ) . '-content';
	?>
	<div class="teca-single-page <?php echo esc_attr( $style_class ); ?>" data-teca-single-style="<?php echo esc_attr( $style_key ); ?>">
		<div class="<?php echo esc_attr( $content_class ); ?>">
			<?php teca_render_single_page_elements( $event_payload, $sorted_fields, $style_key ); ?>
		</div>
	</div>
	<?php
endif;

// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
