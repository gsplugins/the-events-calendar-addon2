<?php

namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$show_date_filter         = teca_is_filter_by_name_enabled( $settings, 'filter_by_date' );
$show_day_filter          = teca_is_filter_by_name_enabled( $settings, 'filter_by_day' );
$show_category_filter     = teca_is_filter_by_name_enabled( $settings, 'filter_by_category' );
$show_tag_filter          = teca_is_filter_by_name_enabled( $settings, 'filter_by_tag' );
$show_venue_filter        = teca_is_filter_by_name_enabled( $settings, 'filter_by_venue' );
$show_city_filter         = teca_is_filter_by_name_enabled( $settings, 'filter_by_city' );
$show_state_filter        = teca_is_filter_by_name_enabled( $settings, 'filter_by_state' );
$show_country_filter      = teca_is_filter_by_name_enabled( $settings, 'filter_by_country' );
$show_organizer_filter    = teca_is_filter_by_name_enabled( $settings, 'filter_by_organizer' );
$show_cost_filter         = teca_is_filter_by_name_enabled( $settings, 'filter_by_cost' );
$show_time_filter         = teca_is_filter_by_name_enabled( $settings, 'filter_by_time' );
$show_featured_filter     = teca_is_filter_by_name_enabled( $settings, 'filter_by_featured' );
$show_event_status_filter = teca_is_filter_by_name_enabled( $settings, 'filter_by_event_status' );
$events_list              = isset( $events ) && is_array( $events ) ? $events : array();
$venue_options            = $show_venue_filter ? teca_build_venue_filter_options( $events_list ) : array();
$category_options         = $show_category_filter ? teca_build_category_filter_options( $events_list ) : array();
$tag_options              = $show_tag_filter ? teca_build_tag_filter_options( $events_list ) : array();
$city_options             = $show_city_filter ? teca_build_city_filter_options( $events_list ) : array();
$state_options            = $show_state_filter ? teca_build_state_filter_options( $events_list ) : array();
$country_options          = $show_country_filter ? teca_build_country_filter_options( $events_list ) : array();
$organizer_options        = $show_organizer_filter ? teca_build_organizer_filter_options( $events_list ) : array();

if ( ! $show_date_filter && ! $show_day_filter && ! $show_category_filter && ! $show_tag_filter && ! $show_venue_filter && ! $show_city_filter && ! $show_state_filter && ! $show_country_filter && ! $show_organizer_filter && ! $show_cost_filter && ! $show_time_filter && ! $show_featured_filter && ! $show_event_status_filter ) {
	return;
}
?>

<div class="teca-filters-by-name-bar">
	<?php if ( $show_date_filter ) : ?>
		<div class="teca-filter-field teca-filter-by-date">
			<label class="teca-calendar-filter-label teca-filter-label" for="teca-filter-date-<?php echo esc_attr( (string) $id ); ?>"><?php esc_html_e( 'Date', 'the-events-calendar-addon' ); ?></label>
			<input type="date" id="teca-filter-date-<?php echo esc_attr( (string) $id ); ?>" class="teca-calendar-filter-select teca-filter-date-input" />
		</div>
	<?php endif; ?>

	<?php if ( $show_day_filter ) : ?>
		<div class="teca-filter-field teca-filter-by-day">
			<label class="teca-calendar-filter-label teca-filter-label" for="teca-filter-day-<?php echo esc_attr( (string) $id ); ?>"><?php esc_html_e( 'Day', 'the-events-calendar-addon' ); ?></label>
			<select id="teca-filter-day-<?php echo esc_attr( (string) $id ); ?>" class="teca-calendar-filter-select teca-filter-day-select">
				<option value=""><?php esc_html_e( 'All Days', 'the-events-calendar-addon' ); ?></option>
				<option value="monday"><?php esc_html_e( 'Monday', 'the-events-calendar-addon' ); ?></option>
				<option value="tuesday"><?php esc_html_e( 'Tuesday', 'the-events-calendar-addon' ); ?></option>
				<option value="wednesday"><?php esc_html_e( 'Wednesday', 'the-events-calendar-addon' ); ?></option>
				<option value="thursday"><?php esc_html_e( 'Thursday', 'the-events-calendar-addon' ); ?></option>
				<option value="friday"><?php esc_html_e( 'Friday', 'the-events-calendar-addon' ); ?></option>
				<option value="saturday"><?php esc_html_e( 'Saturday', 'the-events-calendar-addon' ); ?></option>
				<option value="sunday"><?php esc_html_e( 'Sunday', 'the-events-calendar-addon' ); ?></option>
			</select>
		</div>
	<?php endif; ?>

	<?php if ( $show_category_filter ) : ?>
		<div class="teca-filter-field teca-filter-by-category">
			<label class="teca-calendar-filter-label teca-filter-label" for="teca-filter-category-<?php echo esc_attr( (string) $id ); ?>"><?php esc_html_e( 'Category', 'the-events-calendar-addon' ); ?></label>
			<select id="teca-filter-category-<?php echo esc_attr( (string) $id ); ?>" class="teca-calendar-filter-select teca-filter-category-select">
				<option value=""><?php esc_html_e( 'All Categories', 'the-events-calendar-addon' ); ?></option>
				<?php foreach ( $category_options as $category_option ) : ?>
					<option value="<?php echo esc_attr( (string) $category_option['value'] ); ?>"><?php echo esc_html( (string) $category_option['label'] ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	<?php endif; ?>

	<?php if ( $show_tag_filter ) : ?>
		<div class="teca-filter-field teca-filter-by-tag">
			<label class="teca-calendar-filter-label teca-filter-label" for="teca-filter-tag-<?php echo esc_attr( (string) $id ); ?>"><?php esc_html_e( 'Tag', 'the-events-calendar-addon' ); ?></label>
			<select id="teca-filter-tag-<?php echo esc_attr( (string) $id ); ?>" class="teca-calendar-filter-select teca-filter-tag-select">
				<option value=""><?php esc_html_e( 'All Tags', 'the-events-calendar-addon' ); ?></option>
				<?php foreach ( $tag_options as $tag_option ) : ?>
					<option value="<?php echo esc_attr( (string) $tag_option['value'] ); ?>"><?php echo esc_html( (string) $tag_option['label'] ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	<?php endif; ?>

	<?php if ( $show_venue_filter ) : ?>
		<div class="teca-filter-field teca-filter-by-venue">
			<label class="teca-calendar-filter-label teca-filter-label" for="teca-filter-venue-<?php echo esc_attr( (string) $id ); ?>"><?php esc_html_e( 'Venue', 'the-events-calendar-addon' ); ?></label>
			<select id="teca-filter-venue-<?php echo esc_attr( (string) $id ); ?>" class="teca-calendar-filter-select teca-filter-venue-select">
				<option value=""><?php esc_html_e( 'All Venues', 'the-events-calendar-addon' ); ?></option>
				<?php foreach ( $venue_options as $venue_option ) : ?>
					<option value="<?php echo esc_attr( (string) $venue_option['value'] ); ?>"><?php echo esc_html( (string) $venue_option['label'] ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	<?php endif; ?>

	<?php if ( $show_city_filter ) : ?>
		<div class="teca-filter-field teca-filter-by-city">
			<label class="teca-calendar-filter-label teca-filter-label" for="teca-filter-city-<?php echo esc_attr( (string) $id ); ?>"><?php esc_html_e( 'City', 'the-events-calendar-addon' ); ?></label>
			<select id="teca-filter-city-<?php echo esc_attr( (string) $id ); ?>" class="teca-calendar-filter-select teca-filter-city-select">
				<option value=""><?php esc_html_e( 'All Cities', 'the-events-calendar-addon' ); ?></option>
				<?php foreach ( $city_options as $city_option ) : ?>
					<option value="<?php echo esc_attr( (string) $city_option['value'] ); ?>"><?php echo esc_html( (string) $city_option['label'] ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	<?php endif; ?>

	<?php if ( $show_state_filter ) : ?>
		<div class="teca-filter-field teca-filter-by-state">
			<label class="teca-calendar-filter-label teca-filter-label" for="teca-filter-state-<?php echo esc_attr( (string) $id ); ?>"><?php esc_html_e( 'State', 'the-events-calendar-addon' ); ?></label>
			<select id="teca-filter-state-<?php echo esc_attr( (string) $id ); ?>" class="teca-calendar-filter-select teca-filter-state-select">
				<option value=""><?php esc_html_e( 'All States', 'the-events-calendar-addon' ); ?></option>
				<?php foreach ( $state_options as $state_option ) : ?>
					<option value="<?php echo esc_attr( (string) $state_option['value'] ); ?>"><?php echo esc_html( (string) $state_option['label'] ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	<?php endif; ?>

	<?php if ( $show_country_filter ) : ?>
		<div class="teca-filter-field teca-filter-by-country">
			<label class="teca-calendar-filter-label teca-filter-label" for="teca-filter-country-<?php echo esc_attr( (string) $id ); ?>"><?php esc_html_e( 'Country', 'the-events-calendar-addon' ); ?></label>
			<select id="teca-filter-country-<?php echo esc_attr( (string) $id ); ?>" class="teca-calendar-filter-select teca-filter-country-select">
				<option value=""><?php esc_html_e( 'All Countries', 'the-events-calendar-addon' ); ?></option>
				<?php foreach ( $country_options as $country_option ) : ?>
					<option value="<?php echo esc_attr( (string) $country_option['value'] ); ?>"><?php echo esc_html( (string) $country_option['label'] ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	<?php endif; ?>

	<?php if ( $show_organizer_filter ) : ?>
		<div class="teca-filter-field teca-filter-by-organizer">
			<label class="teca-calendar-filter-label teca-filter-label" for="teca-filter-organizer-<?php echo esc_attr( (string) $id ); ?>"><?php esc_html_e( 'Organizer', 'the-events-calendar-addon' ); ?></label>
			<select id="teca-filter-organizer-<?php echo esc_attr( (string) $id ); ?>" class="teca-calendar-filter-select teca-filter-organizer-select">
				<option value=""><?php esc_html_e( 'All Organizers', 'the-events-calendar-addon' ); ?></option>
				<?php foreach ( $organizer_options as $organizer_option ) : ?>
					<option value="<?php echo esc_attr( (string) $organizer_option['value'] ); ?>"><?php echo esc_html( (string) $organizer_option['label'] ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	<?php endif; ?>

	<?php if ( $show_cost_filter ) : ?>
		<div class="teca-filter-field teca-filter-by-cost">
			<label class="teca-calendar-filter-label teca-filter-label" for="teca-filter-cost-<?php echo esc_attr( (string) $id ); ?>"><?php esc_html_e( 'Cost', 'the-events-calendar-addon' ); ?></label>
			<select id="teca-filter-cost-<?php echo esc_attr( (string) $id ); ?>" class="teca-calendar-filter-select teca-filter-cost-select">
				<option value=""><?php esc_html_e( 'All Costs', 'the-events-calendar-addon' ); ?></option>
				<option value="free"><?php esc_html_e( 'Free', 'the-events-calendar-addon' ); ?></option>
				<option value="paid"><?php esc_html_e( 'Paid', 'the-events-calendar-addon' ); ?></option>
			</select>
		</div>
	<?php endif; ?>

	<?php if ( $show_time_filter ) : ?>
		<div class="teca-filter-field teca-filter-by-time">
			<label class="teca-calendar-filter-label teca-filter-label" for="teca-filter-time-<?php echo esc_attr( (string) $id ); ?>"><?php esc_html_e( 'Time', 'the-events-calendar-addon' ); ?></label>
			<select id="teca-filter-time-<?php echo esc_attr( (string) $id ); ?>" class="teca-calendar-filter-select teca-filter-time-select">
				<option value=""><?php esc_html_e( 'All Times', 'the-events-calendar-addon' ); ?></option>
				<option value="morning"><?php esc_html_e( 'Morning', 'the-events-calendar-addon' ); ?></option>
				<option value="afternoon"><?php esc_html_e( 'Afternoon', 'the-events-calendar-addon' ); ?></option>
				<option value="evening"><?php esc_html_e( 'Evening', 'the-events-calendar-addon' ); ?></option>
				<option value="night"><?php esc_html_e( 'Night', 'the-events-calendar-addon' ); ?></option>
			</select>
		</div>
	<?php endif; ?>

	<?php if ( $show_featured_filter ) : ?>
		<div class="teca-filter-field teca-filter-by-featured">
			<label class="teca-calendar-filter-label teca-filter-label" for="teca-filter-featured-<?php echo esc_attr( (string) $id ); ?>"><?php esc_html_e( 'Featured Events', 'the-events-calendar-addon' ); ?></label>
			<select id="teca-filter-featured-<?php echo esc_attr( (string) $id ); ?>" class="teca-calendar-filter-select teca-filter-featured-select">
				<option value=""><?php esc_html_e( 'All Events', 'the-events-calendar-addon' ); ?></option>
				<option value="featured"><?php esc_html_e( 'Featured Only', 'the-events-calendar-addon' ); ?></option>
				<option value="not-featured"><?php esc_html_e( 'Non-Featured', 'the-events-calendar-addon' ); ?></option>
			</select>
		</div>
	<?php endif; ?>

	<?php if ( $show_event_status_filter ) : ?>
		<div class="teca-filter-field teca-filter-by-status">
			<label class="teca-calendar-filter-label teca-filter-label" for="teca-filter-status-<?php echo esc_attr( (string) $id ); ?>"><?php esc_html_e( 'Event Status', 'the-events-calendar-addon' ); ?></label>
			<select id="teca-filter-status-<?php echo esc_attr( (string) $id ); ?>" class="teca-calendar-filter-select teca-filter-status-select">
				<option value=""><?php esc_html_e( 'All Statuses', 'the-events-calendar-addon' ); ?></option>
				<option value="upcoming"><?php esc_html_e( 'Upcoming', 'the-events-calendar-addon' ); ?></option>
				<option value="ongoing"><?php esc_html_e( 'Ongoing', 'the-events-calendar-addon' ); ?></option>
				<option value="past"><?php esc_html_e( 'Past', 'the-events-calendar-addon' ); ?></option>
			</select>
		</div>
	<?php endif; ?>

	<div class="teca-filter-field teca-filter-clear-field">
		<button type="button" class="teca-filters-clear"><?php esc_html_e( 'Clear', 'the-events-calendar-addon' ); ?></button>
	</div>
</div>
<div class="teca-filters-by-name-empty-message" hidden><?php esc_html_e( 'No events found.', 'the-events-calendar-addon' ); ?></div>
