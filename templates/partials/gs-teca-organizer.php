<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally GS_TECA.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and may be shared with included partial templates.

/**
 * 🔹 Organizer Display Control
 * Set true/false as needed
 */
$organizer_props = $organizer_props ?? [
    'title' => true,
    'phone' => false,
    'email' => false,
    'url'   => false,
];

$event_id      = 0;
$organizer_ids = [];
$organizers    = [];

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
 * Step 2: Get Organizer IDs
 */
if ( $event_id > 0 ) {

    if ( function_exists( 'tribe_get_organizer_ids' ) ) {
        $organizer_ids = tribe_get_organizer_ids( $event_id );
    }

    if ( empty( $organizer_ids ) && function_exists( 'tribe_get_organizer_id' ) ) {
        $single = (int) tribe_get_organizer_id( $event_id );
        if ( $single ) {
            $organizer_ids = [ $single ];
        }
    }

    if ( empty( $organizer_ids ) ) {
        $meta = get_post_meta( $event_id, '_EventOrganizerID', false );
        if ( is_array( $meta ) ) {
            $organizer_ids = $meta;
        }
    }
}

/**
 * Step 3: Build Organizer Data
 */
if ( ! empty( $organizer_ids ) && is_array( $organizer_ids ) ) {

    foreach ( $organizer_ids as $oid ) {

        $oid = (int) $oid;
        if ( ! $oid ) continue;

        $organizers[] = [
            'title' => get_the_title( $oid ),
            'phone' => get_post_meta( $oid, '_OrganizerPhone', true ),
            'email' => get_post_meta( $oid, '_OrganizerEmail', true ),
            'url'   => get_post_meta( $oid, '_OrganizerWebsite', true ),
        ];
    }
}
?>

<?php if ( ! empty( $organizers ) ) : ?>
    <div class="gs-teca-organizers">
    <?php foreach ( $organizers as $org ) : ?>
        <div class="gs-teca-organizer">
            <?php if ( $organizer_props['title'] && ! empty( $org['title'] ) ) : ?>
                <div class="gs-teca-organizer-main">
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
                        class="gs-teca-icon gs-teca-icon-organizer"
                        >
                        <circle cx="12" cy="8" r="4"></circle>
                        <path d="M4 20c0-4 4-6 8-6s8 2 8 6"></path>
                    </svg>
                    <span class="gs-teca-organizer-name teca-popup-detail-organizer-value">
                        <?php echo esc_html( $org['title'] ); ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>

        <?php if ( $organizer_props['phone'] && ! empty( $org['phone'] ) ) : ?>
            <div class="gs-teca-organizer-item teca-popup-detail-organizer-phone">
                <a href="tel:<?php echo esc_attr( $org['phone'] ); ?>">
                    <?php echo esc_html( $org['phone'] ); ?>
                </a>
            </div>
        <?php endif; ?>

        <?php if ( $organizer_props['email'] && ! empty( $org['email'] ) ) : ?>
            <div class="gs-teca-organizer-item teca-popup-detail-organizer-email">
                <a href="mailto:<?php echo esc_attr( $org['email'] ); ?>">
                    <?php echo esc_html( $org['email'] ); ?>
                </a>
            </div>
        <?php endif; ?>

        <?php if ( $organizer_props['url'] && ! empty( $org['url'] ) ) : ?>
            <div class="gs-teca-organizer-item teca-popup-detail-organizer-website">
                <a href="<?php echo esc_url( $org['url'] ); ?>" target="_blank" rel="noopener">
                    Visit Website
                </a>
            </div>
        <?php endif; ?>

    <?php endforeach; ?>

</div>
<?php endif; ?>

<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
