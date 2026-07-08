<?php
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	tribe_the_notices();
	teca_include_single_layout_template();
endwhile;

get_footer();
