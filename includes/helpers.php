<?php

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally kept for backward compatibility.
namespace GS_TECA;

if ( ! defined( 'ABSPATH' ) ) exit;


class Helpers {
	
	static function is_visible( $field, $device = '' ) {
		if ( ! is_array( $field ) ) return false;

		$is_true = function( $val ) {
			return $val === true || $val === 'true' || $val === 1 || $val === '1';
		};

		if ( empty( $device ) ) {
			return $is_true( $field['desktop'] ?? false ) || 
				$is_true( $field['tablet'] ?? false ) || 
				$is_true( $field['mobile_landscape'] ?? false ) || 
				$is_true( $field['mobile'] ?? false );
		}

		if ( in_array( $device, ['desktop', 'tablet', 'mobile_landscape', 'mobile'] ) ) {
			return isset( $field[ $device ] ) ? $is_true( $field[ $device ] ) : false;
		}

		return false;
	}

	static function get_visible_classes( $field, $additional_class = '' ) {

		$devices = [
			'desktop'          => 'gs-teca--hide-md',
			'tablet'           => 'gs-teca--hide-sm',
			'mobile_landscape' => 'gs-teca--hide-xs',
			'mobile'           => 'gs-teca--hide-xxs',
		];

		$classes = [];

		if ( !empty( $additional_class ) ) {
			$classes[] = $additional_class;
		}

		foreach ( $devices as $device => $class ) {
			if ( ! self::is_visible( $field, $device ) ) {
				$classes[] = $class;
			}
		}

		return $classes;
	}

	static function print_visible_classes( $field, $additional_class = '' ) {
		$classes = self::get_visible_classes( $field, $additional_class );
		echo esc_attr( implode( ' ', $classes ) );
	}

	static function trim_event_details( $text, $length_type = 'words', $length = 25 ) {

		$length = absint( $length );

		if ( $length <= 0 || empty( $text ) ) {
			return $text;
		}

		$text = wp_strip_all_tags( $text );
		$text = trim( preg_replace( '/\s+/', ' ', $text ) );

		if ( $length_type === 'letter' ) {
			if ( mb_strlen( $text ) > $length ) {
				return mb_substr( $text, 0, $length ) . '...';
			}
			return $text;
		}

		return wp_trim_words( $text, $length, '...' );
	}
	

}