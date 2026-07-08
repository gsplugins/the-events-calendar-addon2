<?php
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$event = $event ?? array();

teca_render_table_venue( $event );
