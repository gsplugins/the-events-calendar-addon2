<?php

namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) exit;

$audio = get_post_meta( get_the_ID(), 'gs_posts_audio_url', true );

if ( empty( $audio ) ) return;

if ( strpos( $audio, 'iframe' ) !== false ) {
    $audio = str_replace( 'src=', 'data-src=', $audio );
}


?>

<div class="gsp-audio-portfolio">
    <?php if ( strpos( $audio, 'iframe' ) !== false ): ?>
        <?php echo wp_kses_post($audio); ?>
    <?php else: ?>
        <iframe width="100%" height="300" scrolling="no" frameborder="no" allow="autoplay" src="<?php echo esc_url($audio); ?>"></iframe>
    <?php endif; ?>
</div>