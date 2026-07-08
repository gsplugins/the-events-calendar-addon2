<?php
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * 🔹 Venue Display Control
 */
$venue_props = $venue_props ?? [
    'title'   => true,
    'address' => false,
    'city'    => true,
    'state'   => false,
    'zip'     => false,
    'country' => false,
];

$event_id = 0;
$venue_id = 0;
$venue    = [];

/**
 * Step 1: Resolve Event ID
 */
if ( isset( $event['event_id'] ) && (int) $event['event_id'] > 0 ) {
    $event_id = (int) $event['event_id'];
} else {
    $post = get_post();
    if ( $post instanceof \WP_Post ) {
        $event_id = $post->ID;
    }
}

/**
 * Step 2: Resolve Venue ID
 */
if ( $event_id > 0 ) {

    if ( function_exists( 'tribe_get_venue_id' ) ) {
        $venue_id = (int) tribe_get_venue_id( $event_id );
    }

    if ( ! $venue_id ) {
        $venue_id = (int) get_post_meta( $event_id, '_EventVenueID', true );
    }
}

/**
 * Step 3: Build Venue Data
 */
if ( $venue_id > 0 ) {

    $venue = [
        'title'   => get_the_title( $venue_id ),
        'address' => get_post_meta( $venue_id, '_VenueAddress', true ),
        'city'    => get_post_meta( $venue_id, '_VenueCity', true ),
        'state'   => get_post_meta( $venue_id, '_VenueState', true ),
        'zip'     => get_post_meta( $venue_id, '_VenueZip', true ),
        'country' => get_post_meta( $venue_id, '_VenueCountry', true ),
    ];
}
?>

<?php if ( ! empty( $venue ) ) : ?>
<div class="gs-teca-venue">

    <div class="gs-teca-venue-main">
        <svg xmlns="http://www.w3.org/2000/svg"
            width="20"
            height="20"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="gs-teca-icon gs-teca-icon-venue">
            <path d="M12 21s7-6.2 7-11a7 7 0 0 0-14 0c0 4.8 7 11 7 11z"></path>
            <circle cx="12" cy="10" r="2.5"></circle>
        </svg>
        <?php if ( $venue_props['title'] && ! empty( $venue['title'] ) ) : ?>
            <span class="gs-teca-venue-name teca-popup-detail-venue-value">
                <?php echo esc_html( $venue['title'] ); ?>
            </span>
        <?php endif; ?> 
    </div>

    <div class="gs-teca-venue-content">

        <?php
        $split_location_address = ! empty( $venue_props['split_location_address'] );
        $address_parts          = array();
        $location_parts         = array();

        if ( $venue_props['address'] && ! empty( $venue['address'] ) ) {
            $address_parts[] = trim( (string) $venue['address'] );
        }

        if ( $venue_props['city'] && ! empty( $venue['city'] ) ) {
            $location_parts[] = trim( (string) $venue['city'] );
        }

        if ( $venue_props['state'] && ! empty( $venue['state'] ) ) {
            $location_parts[] = trim( (string) $venue['state'] );
        }

        if ( $venue_props['zip'] && ! empty( $venue['zip'] ) ) {
            $location_parts[] = trim( (string) $venue['zip'] );
        }

        if ( $venue_props['country'] && ! empty( $venue['country'] ) ) {
            $location_parts[] = trim( (string) $venue['country'] );
        }

        if ( ! $split_location_address ) {
            $address_parts = array_merge( $address_parts, $location_parts );
            $location_parts = array();
        }
        ?>

        <?php if ( ! empty( $address_parts ) ) : ?>
            <span class="gs-teca-venue-address<?php echo $split_location_address ? ' teca-popup-detail-address' : ''; ?>">
                <?php echo esc_html( implode( ', ', $address_parts ) ); ?>
            </span>
        <?php endif; ?>

        <?php if ( ! empty( $location_parts ) ) : ?>
            <span class="gs-teca-venue-location teca-popup-detail-location">
                <?php echo esc_html( implode( ', ', $location_parts ) ); ?>
            </span>
        <?php endif; ?>

    </div>

</div>
<?php endif; ?>