<?php

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'td_block' ) ) {
	return;
}

/**
 * tagDiv Composer block for rendering saved TECA shortcodes.
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- tagDiv block class naming convention is required for tagDiv Composer integration.
class td_teca_tagdiv_events extends td_block {

	/**
	 * Render tagDiv block output.
	 *
	 * @param array<string, mixed>|string $atts Block attributes.
	 * @param string|null                 $content Block content.
	 * @return string
	 */
	public function render( $atts, $content = null ) {
		parent::render( $atts, $content );

		$atts = shortcode_atts(
			[
				'shortcode_id' => $this->get_default_shortcode_id(),
			],
			(array) $atts
		);

		$shortcode_id      = ! empty( $atts['shortcode_id'] ) ? absint( $atts['shortcode_id'] ) : 0;
		$allow_placeholder = $this->is_tagdiv_editor_context();
		$output            = $this->get_block_css();
		$output           .= '<div class="wpb_wrapper td_teca_tagdiv_events_block ' . esc_attr( $this->get_wrapper_class() ) . ' ' . esc_attr( $this->get_block_classes() ) . ' teca-tagdiv-widget-wrap" data-teca-tagdiv-shortcode="' . esc_attr( (string) $shortcode_id ) . '">';

		if ( ! $shortcode_id ) {
			if ( $allow_placeholder ) {
				$output .= '<div class="teca-tagdiv-placeholder">' . esc_html__( 'Please select a TECA shortcode.', 'the-events-calendar-addon2' ) . '</div>';
			}

			$output .= '</div>';

			return $output;
		}

		if ( ! GS_TECA\teca_shortcode_exists( $shortcode_id ) ) {
			if ( $allow_placeholder ) {
				$output .= '<div class="teca-tagdiv-placeholder">' . esc_html__( 'Selected TECA shortcode was not found.', 'the-events-calendar-addon2' ) . '</div>';
			}

			$output .= '</div>';

			return $output;
		}

		$output .= GS_TECA\teca_render_saved_shortcode( $shortcode_id );
		$output .= '</div>';

		return $output;
	}

	/**
	 * Whether the block is rendering inside tagDiv Composer.
	 *
	 * @return bool
	 */
	protected function is_tagdiv_editor_context() {
		global $load_in_composer_iframe;

		if ( ! empty( $load_in_composer_iframe ) ) {
			return true;
		}

		if ( class_exists( 'tdc_state' ) && method_exists( 'tdc_state', 'is_live_editor_iframe' ) && tdc_state::is_live_editor_iframe() ) {
			return true;
		}

		return false;
	}

	/**
	 * Default shortcode ID.
	 *
	 * @return int|string
	 */
	protected function get_default_shortcode_id() {
		$values = GS_TECA\teca_get_saved_shortcodes_for_tagdiv();

		if ( empty( $values ) ) {
			return '';
		}

		$ids = array_values( $values );

		return absint( reset( $ids ) );
	}
}
