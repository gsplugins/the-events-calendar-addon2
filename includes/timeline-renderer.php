<?php

namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Timeline_Renderer {

	public static function get_timeline_themes() {
		return array(
			'gs-teca-timeline-1',
			'gs-teca-timeline-2',
			'gs-teca-timeline-3',
		);
	}

	public static function is_timeline_theme( $theme ) {
		return in_array( (string) $theme, self::get_timeline_themes(), true );
	}

	public static function get_template_file( $theme ) {
		$map = array(
			'gs-teca-timeline-1' => 'timeline/timeline-1.php',
			'gs-teca-timeline-2' => 'timeline/timeline-2.php',
			'gs-teca-timeline-3' => 'timeline/timeline-3.php',
		);

		return $map[ (string) $theme ] ?? '';
	}

	public static function get_timeline_layout_class( $theme ) {
		$map = array(
			'gs-teca-timeline-1' => 'teca-timeline-1',
			'gs-teca-timeline-2' => 'teca-timeline-2',
			'gs-teca-timeline-3' => 'teca-timeline-3',
		);

		return $map[ (string) $theme ] ?? '';
	}
}
