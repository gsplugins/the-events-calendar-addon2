<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing namespace is kept for backward compatibility with reused popup partials.
namespace GS_POSTS_GRID;

if ( ! defined( 'ABSPATH' ) ) exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and may be shared with included partial templates.

$popup_media = get_popup_media( get_the_ID() );

if ( $popup_media === 'video' ) {
    include Template_Loader::locate_template( 'popups/partials/gsp-video.php' );
} elseif ( $popup_media === 'audio' ) {
    include Template_Loader::locate_template( 'popups/partials/gsp-audio.php' );
} elseif ( $popup_media === 'gallery' ) {
    include Template_Loader::locate_template( 'popups/partials/gsp-gallery-carousel.php' );
} else {
    include Template_Loader::locate_template( 'popups/partials/gsp-thumbnail.php' );
}

// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
