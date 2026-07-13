<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally GS_TECA.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and may be shared with included partial templates.

$event_id = 0;

if ( isset( $event['event_id'] ) && (int) $event['event_id'] > 0 ) {
	$event_id = (int) $event['event_id'];
} else {
	$post = get_post();
	if ( $post instanceof \WP_Post ) {
		$event_id = $post->ID;
	}
}

if ( ! $event_id ) {
	return;
}

$links              = array();
$link_type          = $gs_teca_link_type ?? 'none';
$shortcode_id       = isset( $atts['id'] ) ? $atts['id'] : ( $settings['id'] ?? '' );
$table_social_prefix = $teca_table_social_prefix ?? 'teca-table-style-1';
$social_links_class  = $table_social_prefix . '-social-links';
$social_btn_class    = $table_social_prefix . '-social-btn';

if ( 'single_page' === $link_type ) {
	$permalink = get_permalink( $event_id );
	if ( $permalink ) {
		$links[] = array(
			'url'   => $permalink,
			'label' => __( 'Event page', 'the-events-calendar-addon' ),
			'icon'  => 'link',
		);
	}
}

if ( function_exists( 'tribe_get_event_website_url' ) ) {
	$event_website = tribe_get_event_website_url( $event_id );
	if ( $event_website ) {
		$links[] = array(
			'url'   => $event_website,
			'label' => __( 'Event website', 'the-events-calendar-addon' ),
			'icon'  => 'external',
		);
	}
}

$organizer_ids = array();

if ( function_exists( 'tribe_get_organizer_ids' ) ) {
	$organizer_ids = (array) tribe_get_organizer_ids( $event_id );
} elseif ( function_exists( 'tribe_get_organizer_id' ) ) {
	$single = (int) tribe_get_organizer_id( $event_id );
	if ( $single ) {
		$organizer_ids = array( $single );
	}
}

$seen_urls = wp_list_pluck( $links, 'url' );

foreach ( $organizer_ids as $organizer_id ) {
	$organizer_id = (int) $organizer_id;
	if ( ! $organizer_id ) {
		continue;
	}

	$organizer_url = get_post_meta( $organizer_id, '_OrganizerWebsite', true );
	if ( $organizer_url && ! in_array( $organizer_url, $seen_urls, true ) ) {
		$links[] = array(
			'url'   => $organizer_url,
			'label' => get_the_title( $organizer_id ),
			'icon'  => 'external',
		);
		$seen_urls[] = $organizer_url;
	}
}

if ( empty( $links ) && 'popup' === $link_type ) {
	$data_src   = '#gs_teca_popup_' . $event_id . '_' . $shortcode_id;
	$popup_style = empty( $popup_style ) ? 'default' : $popup_style;
	$data_theme = 'gs-teca-popup-' . esc_attr( $popup_style );
	?>
	<button
		type="button"
		class="<?php echo esc_attr( $social_btn_class ); ?> gs_teca_pop open-popup-link"
		data-mfp-src="<?php echo esc_attr( $data_src ); ?>"
		data-theme="<?php echo esc_attr( $data_theme ); ?>"
		aria-label="<?php esc_attr_e( 'View event details', 'the-events-calendar-addon' ); ?>"
	>
		<svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
			<path d="M10 6H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4M14 4h6m0 0v6m0-6L10 14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
		</svg>
	</button>
	<?php
	return;
}

if ( empty( $links ) ) {
	return;
}
?>

<div class="<?php echo esc_attr( $social_links_class ); ?>">
	<?php foreach ( $links as $link_item ) : ?>
		<a
			href="<?php echo esc_url( $link_item['url'] ); ?>"
			class="<?php echo esc_attr( $social_btn_class ); ?>"
			target="_blank"
			rel="noopener noreferrer"
			aria-label="<?php echo esc_attr( $link_item['label'] ); ?>"
		>
			<?php if ( 'external' === $link_item['icon'] ) : ?>
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
					<path d="M10 6H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4M14 4h6m0 0v6m0-6L10 14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			<?php else : ?>
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
					<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			<?php endif; ?>
		</a>
	<?php endforeach; ?>
</div>

<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
