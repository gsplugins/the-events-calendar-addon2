<?php

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally kept for backward compatibility.
namespace GS_TECA;

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'ET_Builder_Module' ) ) {
	return;
}

/**
 * Divi module for rendering saved TECA shortcodes.
 */
class Teca_Divi_Events_Module extends \ET_Builder_Module {

	/**
	 * Module slug.
	 *
	 * @var string
	 */
	public $slug = 'gs_teca_events';

	/**
	 * Visual Builder support.
	 *
	 * @var string
	 */
	public $vb_support = 'on';

	/**
	 * Initialize module metadata.
	 *
	 * @return void
	 */
	public function init() {
		$this->name             = esc_html__( 'TECA Events', 'the-events-calendar-addon' );
		$this->icon_path        = GS_TECA_PLUGIN_DIR . 'assets/img/events.svg';
		$this->main_css_element = '%%order_class%%';
	}

	/**
	 * Module settings fields.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public function get_fields() {
		return [
			'shortcode_id' => [
				'label'            => esc_html__( 'Select TECA Shortcode', 'the-events-calendar-addon' ),
				'type'             => 'select',
				'option_category'  => 'basic_option',
				'description'      => $this->get_field_description(),
				'toggle_slug'      => 'main_content',
				'default'          => $this->get_default_shortcode_id(),
				'options'          => $this->get_shortcode_options(),
				'computed_affects' => [
					'__shortcode',
				],
			],
			'__shortcode'  => [
				'type'                => 'computed',
				'computed_callback'   => [ __CLASS__, 'get_shortcode_output' ],
				'computed_depends_on' => [
					'shortcode_id',
				],
				'computed_minimum'    => [
					'shortcode_id',
				],
			],
		];
	}

	/**
	 * Field description with edit/create links.
	 *
	 * @return string
	 */
	protected function get_field_description() {
		$edit_link = sprintf(
			'%s: <a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
			esc_html__( 'Edit this shortcode', 'the-events-calendar-addon' ),
			esc_url( admin_url( 'admin.php?page=gs-the-events-calendar-addon#/shortcode/' ) ),
			esc_html__( 'Edit', 'the-events-calendar-addon' )
		);

		$create_link = sprintf(
			'%s: <a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
			esc_html__( 'Create new shortcode', 'the-events-calendar-addon' ),
			esc_url( admin_url( 'admin.php?page=gs-the-events-calendar-addon#/shortcode' ) ),
			esc_html__( 'Create', 'the-events-calendar-addon' )
		);

		return implode( '<br />', [ $edit_link, $create_link ] );
	}

	/**
	 * Shortcode select options.
	 *
	 * @return array<int|string, string>
	 */
	protected function get_shortcode_options() {
		$options = teca_get_saved_shortcodes_for_builder();

		if ( ! empty( $options ) ) {
			return $options;
		}

		return [
			'' => esc_html__( 'No TECA shortcode found', 'the-events-calendar-addon' ),
		];
	}

	/**
	 * Default shortcode ID.
	 *
	 * @return string
	 */
	protected function get_default_shortcode_id() {
		$options = teca_get_saved_shortcodes_for_builder();

		if ( empty( $options ) ) {
			return '';
		}

		$ids = array_keys( $options );

		return (string) absint( reset( $ids ) );
	}

	/**
	 * Computed shortcode output for Visual Builder.
	 *
	 * @param array<string, mixed> $args Module props.
	 * @return string
	 */
	public static function get_shortcode_output( $args ) {
		$args         = wp_parse_args(
			(array) $args,
			[
				'shortcode_id' => '',
			]
		);
		$shortcode_id = absint( $args['shortcode_id'] );

		return self::render_shortcode_markup( $shortcode_id, true );
	}

	/**
	 * Render module output.
	 *
	 * @param array<string, mixed> $unprocessed_props Module props.
	 * @param string|null          $content           Module content.
	 * @param string               $render_slug       Render slug.
	 * @return string
	 */
	public function render( $unprocessed_props, $content = null, $render_slug = '' ) {
		unset( $content );

		$shortcode_id = ! empty( $this->props['shortcode_id'] ) ? absint( $this->props['shortcode_id'] ) : 0;

		if ( ! $shortcode_id ) {
			$shortcode_id = absint( $this->get_default_shortcode_id() );
		}

		$is_builder = function_exists( 'et_core_is_fb_enabled' ) && et_core_is_fb_enabled();
		$markup     = self::render_shortcode_markup( $shortcode_id, $is_builder );

		return sprintf(
			'<div id="%1$s" class="%2$s teca-divi-widget-wrap" data-teca-divi-shortcode="%3$s">%4$s</div>',
			esc_attr( $this->module_id() ),
			esc_attr( $this->module_classname( $render_slug ) ),
			esc_attr( (string) $shortcode_id ),
			$markup // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in render_shortcode_markup.
		);
	}

	/**
	 * Build shortcode HTML or builder placeholder.
	 *
	 * @param int  $shortcode_id Shortcode ID.
	 * @param bool $allow_placeholder Whether builder placeholders are allowed.
	 * @return string
	 */
	protected static function render_shortcode_markup( $shortcode_id, $allow_placeholder = false ) {
		$shortcode_id = absint( $shortcode_id );

		if ( ! $shortcode_id ) {
			return $allow_placeholder
				? '<div class="teca-divi-placeholder">' . esc_html__( 'Please select a TECA shortcode.', 'the-events-calendar-addon' ) . '</div>'
				: '';
		}

		if ( ! teca_shortcode_exists( $shortcode_id ) ) {
			return $allow_placeholder
				? '<div class="teca-divi-placeholder">' . esc_html__( 'Selected TECA shortcode was not found.', 'the-events-calendar-addon' ) . '</div>'
				: '';
		}

		return do_shortcode( sprintf( '[gs-teca id="%d"]', $shortcode_id ) );
	}
}
