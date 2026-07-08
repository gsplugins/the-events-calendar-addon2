<?php

namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Accordion_Renderer {

	public static function get_accordion_themes() {
		return array(
			'gs-teca-accordion-1',
			'gs-teca-accordion-2',
			'gs-teca-accordion-3',
		);
	}

	public static function is_accordion_theme( $theme ) {
		return in_array( (string) $theme, self::get_accordion_themes(), true );
	}

	public static function get_template_file( $theme ) {
		$map = array(
			'gs-teca-accordion-1' => 'accordion/accordion-1.php',
			'gs-teca-accordion-2' => 'accordion/accordion-2.php',
			'gs-teca-accordion-3' => 'accordion/accordion-3.php',
		);

		return $map[ (string) $theme ] ?? '';
	}

	public static function get_accordion_layout_class( $theme ) {
		$map = array(
			'gs-teca-accordion-1' => 'teca-accordion-1',
			'gs-teca-accordion-2' => 'teca-accordion-2',
			'gs-teca-accordion-3' => 'teca-accordion-3',
		);

		return $map[ (string) $theme ] ?? '';
	}
}
