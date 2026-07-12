<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally GS_TECA.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are intentionally local and may be shared with included partial templates.

$event_id = 0;

if ( isset( $event['event_id'] ) && $event['event_id'] > 0 ) {
    $event_id = (int) $event['event_id'];
}

if ( ! $event_id ) {
    $post = get_post();
    if ( $post instanceof \WP_Post ) {
        $event_id = $post->ID;
    }
}



$has_link = $gs_teca_name_is_linked;

$link_type = $gs_teca_link_type;

$shortcode_id = $atts['id'];

$data_src = '#gs_teca_popup_' . $event_id . '_' . $shortcode_id;

$data_theme = 'gs-teca-popup-' . esc_attr($popup_style);

if ($event_id) {

    $linked_thumb    = get_the_post_thumbnail( 
        $event_id, 
        'gs-square-thumb',  
    );
    
    if ($has_link == 'on') {
        
            $thumbnail    = get_the_post_thumbnail( 
                $event_id, 
                'gs-square-thumb', 
                ['class' => 'gs-popup-img'] 
            );

            if ($link_type == 'single_page') {

                $linked_thumb = sprintf('<a href="%s">%s <div class="gs-teca-img-overlay"></div></a>', get_the_permalink($event_id), $thumbnail);
            
            } else if ($link_type == 'popup') {
                

                $popup_style = empty($popup_style) ? 'default' : $popup_style;

                $data_src     = "#gs_teca_popup_{$event_id}_{$shortcode_id}";
                $data_theme   = 'gs-teca-popup-' . esc_attr($popup_style);



                $linked_thumb = sprintf(
                    '<a class="gs_teca_pop open-popup-link" data-mfp-src="%s" data-theme="%s" href="#">%s<div class="gs-teca-img-overlay"></div></a>',
                    esc_attr($data_src),
                    esc_attr($data_theme),
                    $thumbnail
                );


            }
            
    }
}

?>

<div class="gs-teca-thumbnail-wrapper">
    <?php echo wp_kses_post($linked_thumb); ?>
</div>

<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
