<?php

namespace GS_TECA;


do_action( 'gs_teca_before_navigation' );

$gs_teca_nxt_prev = getoption( 'gs_teca_nxt_prev', 'on' );

if ( 'on' ==  $gs_teca_nxt_prev ) : ?>
    
    <div class="prev-next-navigation">
        <?php previous_post_link( '<div class="previous">%link</div>', '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="12px" height="20px"><path fill-rule="evenodd" fill="rgb(204 204 204)" d="M11.414,18.485 L10.000,19.899 L0.100,10.000 L1.515,8.585 L1.515,8.585 L10.000,0.100 L11.414,1.514 L2.929,10.000 L11.414,18.485 Z"/></svg>%title' ); ?>
        <div></div> <!-- Empty div is important -->
        <?php next_post_link( '<div class="next">%link</div>', '%title<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="12px" height="20px"><path fill-rule="evenodd" fill="rgb(204 204 204)" d="M11.899,10.000 L2.000,19.899 L0.586,18.485 L9.071,10.000 L0.586,1.514 L2.000,0.100 L10.485,8.585 L10.485,8.585 L11.899,10.000 Z"/></svg>' ); ?>
    </div>


<?php endif;

do_action( 'gs_teca_after_navigation' );