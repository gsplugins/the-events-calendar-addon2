<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing namespace is kept for backward compatibility with reused popup partials.
namespace GS_POSTS_GRID;

if ( ! defined( 'ABSPATH' ) ) exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and may be shared with included partial templates.

$video = get_post_meta( get_the_ID(), 'gs_posts_video_url', true );


if ( empty( $video ) ) return;

if ( strpos( $video, 'iframe' ) === false && strpos($video, 'embed') === false ) {
    $video = str_replace( 'watch?v=', 'embed/', $video );
}

if ( strpos( $video, 'iframe' ) !== false ) {
    $video = str_replace( 'src=', 'data-src=', $video );
}

?>

<div class="gsp-video-portfolio">
    <div class="gsp-video">
        <?php if ( strpos( $video, 'iframe' ) !== false ) : ?>
            <?php echo wp_kses_post($video); ?>
        <?php else: ?>
            <iframe width="100%" height="315" src="<?php echo esc_url( $video ); ?>" frameborder="0" allow="fullscreen; accelerometer; encrypted-media; gyroscope; picture-in-picture"></iframe>
        <?php endif; ?>
    </div>
</div>

<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
