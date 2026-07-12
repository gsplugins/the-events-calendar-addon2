<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally GS_TECA.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and may be shared with included partial templates.

$show_title_search     = teca_is_search_by_enabled( $settings, 'search_by_title' );
$show_venue_search     = teca_is_search_by_enabled( $settings, 'search_by_venue' );
$show_organizer_search = teca_is_search_by_enabled( $settings, 'search_by_organizer' );
$show_city_search      = teca_is_search_by_enabled( $settings, 'search_by_city' );

if ( ! $show_title_search && ! $show_venue_search && ! $show_organizer_search && ! $show_city_search ) {
	return;
}
?>

<div class="teca-search-by-bar" data-instance-id="<?php echo esc_attr( (string) $id ); ?>" data-result-limit="<?php echo esc_attr( (string) teca_get_search_result_limit( $settings ) ); ?>">
	<?php if ( $show_title_search ) : ?>
		<div class="teca-filter-field teca-search-field teca-search-by-title">
			<label class="teca-calendar-filter-label teca-filter-label teca-search-label" for="teca-search-title-<?php echo esc_attr( (string) $id ); ?>"><?php esc_html_e( 'Title', 'the-events-calendar-addon2' ); ?></label>
			<input type="search" id="teca-search-title-<?php echo esc_attr( (string) $id ); ?>" class="teca-calendar-filter-select teca-search-input teca-search-title-input" placeholder="<?php esc_attr_e( 'Search by title', 'the-events-calendar-addon2' ); ?>" autocomplete="off" />
		</div>
	<?php endif; ?>

	<?php if ( $show_venue_search ) : ?>
		<div class="teca-filter-field teca-search-field teca-search-by-venue">
			<label class="teca-calendar-filter-label teca-filter-label teca-search-label" for="teca-search-venue-<?php echo esc_attr( (string) $id ); ?>"><?php esc_html_e( 'Venue', 'the-events-calendar-addon2' ); ?></label>
			<input type="search" id="teca-search-venue-<?php echo esc_attr( (string) $id ); ?>" class="teca-calendar-filter-select teca-search-input teca-search-venue-input" placeholder="<?php esc_attr_e( 'Search by venue', 'the-events-calendar-addon2' ); ?>" autocomplete="off" />
		</div>
	<?php endif; ?>

	<?php if ( $show_organizer_search ) : ?>
		<div class="teca-filter-field teca-search-field teca-search-by-organizer">
			<label class="teca-calendar-filter-label teca-filter-label teca-search-label" for="teca-search-organizer-<?php echo esc_attr( (string) $id ); ?>"><?php esc_html_e( 'Organizer', 'the-events-calendar-addon2' ); ?></label>
			<input type="search" id="teca-search-organizer-<?php echo esc_attr( (string) $id ); ?>" class="teca-calendar-filter-select teca-search-input teca-search-organizer-input" placeholder="<?php esc_attr_e( 'Search by organizer', 'the-events-calendar-addon2' ); ?>" autocomplete="off" />
		</div>
	<?php endif; ?>

	<?php if ( $show_city_search ) : ?>
		<div class="teca-filter-field teca-search-field teca-search-by-city">
			<label class="teca-calendar-filter-label teca-filter-label teca-search-label" for="teca-search-city-<?php echo esc_attr( (string) $id ); ?>"><?php esc_html_e( 'City', 'the-events-calendar-addon2' ); ?></label>
			<input type="search" id="teca-search-city-<?php echo esc_attr( (string) $id ); ?>" class="teca-calendar-filter-select teca-search-input teca-search-city-input" placeholder="<?php esc_attr_e( 'Search by city', 'the-events-calendar-addon2' ); ?>" autocomplete="off" />
		</div>
	<?php endif; ?>
</div>
<div class="teca-search-loading" hidden><?php esc_html_e( 'Searching events...', 'the-events-calendar-addon2' ); ?></div>
<div class="teca-search-by-empty-message" hidden><?php esc_html_e( 'No events found.', 'the-events-calendar-addon2' ); ?></div>

<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
