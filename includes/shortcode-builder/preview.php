<?php
if ( ! defined( 'ABSPATH' ) ) exit; 
?>
<html class="no-js" <?php language_attributes(); ?>>

<head>

    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php wp_head(); ?>

</head>

<!-- <body <?php // body_class(); 
            ?>> -->

<body class="gs-teca-shortcode-preview--page">

    <div class="gs-shortcode-preview--container">

        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

                <div class="gs-shortcode-preview--wrapper shortcode-found">

                    <?php echo do_shortcode( get_the_content() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Shortcode output is escaped inside the shortcode renderer and may include safe inline style tags. ?>

                </div>

            <?php endwhile;
        else : ?>

            <div class="gs-shortcode-preview--wrapper something-wrong">

                <h2><?php esc_html_e('Something went wrong!', 'the-events-calendar-addon'); ?></h2>
                <p><?php esc_html_e('Data not found for preview, probably it\'s a bug, contact with plugin author', 'the-events-calendar-addon'); ?></p>

            </div>

        <?php endif; ?>

    </div>

    <?php wp_footer();
    ?>
    
</body>

</html>