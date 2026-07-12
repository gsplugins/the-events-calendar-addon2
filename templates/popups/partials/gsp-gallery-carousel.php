<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing namespace is kept for backward compatibility with reused popup partials.
namespace GS_POSTS_GRID;

if ( ! defined( 'ABSPATH' ) ) exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and may be shared with included partial templates.

$gallery_images = explode( ',', get_post_meta( get_the_ID(), 'gs_posts_gallery', true ) );

?>

<div class="gs-posts-images-gallery">

    <!-- Slider -->
    <div class="gs-posts-swiper-container">
    
        <!-- Slides -->
        <div class="swiper-wrapper">
            <?php foreach ( $gallery_images as $id ) : ?>
                <div class="swiper-slide">
                    <?php echo wp_get_attachment_image( $id, 'large' ); ?>
                </div>
            <?php endforeach; ?>
        </div>
    
    </div>
    
    <!-- Slides Thumb Controls -->
    <div class="gs-posts-swiper-container--thumb-area">
        <div class="gs-posts-swiper-container--thumb">
            <div class="swiper-wrapper">
                <?php foreach ( $gallery_images as $id ) : ?>
                    <div class="swiper-slide">
                        <?php echo wp_get_attachment_image( $id, 'thumbnail' ); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

</div>

<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
