<?php

namespace GS_TECA;

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Integration_Gutenberg {

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static $_instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return self
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Register hooks.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_block' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_assets' ], 5 );
		add_action( 'enqueue_block_editor_assets', [ $this, 'finalize_block_editor_scripts' ], 100 );
	}

	/**
	 * Register the TECA Gutenberg block.
	 *
	 * @return void
	 */
	public function register_block() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		$block_json = GS_TECA_PLUGIN_DIR . 'includes/integration/gutenberg/block.json';

		if ( ! file_exists( $block_json ) ) {
			return;
		}

		$editor_script = 'gs-teca-gutenberg-block';
		$editor_style  = 'gs-teca-gutenberg-block-editor';

		wp_register_script(
			$editor_script,
			GS_TECA_PLUGIN_URI . 'includes/integration/gutenberg/index.js',
			[
				'wp-blocks',
				'wp-element',
				'wp-block-editor',
				'wp-components',
				'wp-i18n',
				'wp-server-side-render',
			],
			GS_TECA_VERSION,
			true
		);

		wp_localize_script( $editor_script, 'gs_teca_block', $this->get_editor_script_data() );

		$editor_css = GS_TECA_PLUGIN_DIR . 'includes/integration/gutenberg/editor.css';

		if ( file_exists( $editor_css ) ) {
			wp_register_style(
				$editor_style,
				GS_TECA_PLUGIN_URI . 'includes/integration/gutenberg/editor.css',
				[],
				GS_TECA_VERSION
			);
		}

		$block_args = [
			'editor_script'   => $editor_script,
			'render_callback' => [ $this, 'render_block' ],
		];

		if ( file_exists( $editor_css ) ) {
			$block_args['editor_style'] = $editor_style;
		}

		register_block_type( $block_json, $block_args );
	}

	/**
	 * Localized editor strings and shortcode list.
	 *
	 * @return array<string, mixed>
	 */
	protected function get_editor_script_data() {
		return [
			'select_shortcode'        => __( 'Select Shortcode', 'the-events-calendar-addon' ),
			'no_shortcode_selected'   => __( 'Please select a TECA shortcode.', 'the-events-calendar-addon' ),
			'no_shortcodes_available' => __( 'No TECA shortcodes found. Create one first.', 'the-events-calendar-addon' ),
			'shortcode_missing'       => __( 'The selected TECA shortcode no longer exists.', 'the-events-calendar-addon' ),
			'edit_description_text'   => __( 'Edit this shortcode', 'the-events-calendar-addon' ),
			'edit_link_text'          => __( 'Edit', 'the-events-calendar-addon' ),
			'create_description_text' => __( 'Create new shortcode', 'the-events-calendar-addon' ),
			'create_link_text'        => __( 'Create', 'the-events-calendar-addon' ),
			'edit_link'               => admin_url( 'admin.php?page=gs-the-events-calendar-addon#/shortcode/' ),
			'create_link'             => admin_url( 'admin.php?page=gs-the-events-calendar-addon#/shortcode' ),
			'shortcodes'              => $this->get_shortcode_list(),
		];
	}

	/**
	 * Inline editor styles for the block toolbar.
	 *
	 * @return void
	 */
	public function enqueue_block_editor_assets() {
		wp_add_inline_style( 'wp-block-editor', $this->get_block_css() );
	}

	/**
	 * Ensure frontend preview assets load in the block editor.
	 *
	 * @return void
	 */
	public function finalize_block_editor_scripts() {
		global $wp_scripts;

		if ( empty( $wp_scripts->registered['gs-teca-gutenberg-block'] ) ) {
			return;
		}

		if ( function_exists( __NAMESPACE__ . '\\gsTecaAssetGenerator' ) ) {
			gsTecaAssetGenerator()->enqueue_builder_preview_assets();
		}

		$wp_scripts->registered['gs-teca-gutenberg-block']->deps = array_values(
			array_unique(
				array_merge(
					$wp_scripts->registered['gs-teca-gutenberg-block']->deps,
					[ 'gs-teca-public' ]
				)
			)
		);
	}

	/**
	 * Render the selected TECA shortcode on the frontend.
	 *
	 * @param array<string, mixed> $block_attributes Block attributes.
	 * @return string
	 */
	public function render_block( $block_attributes ) {
		$shortcode_id = 0;

		if ( is_array( $block_attributes ) && ! empty( $block_attributes['shortcode'] ) ) {
			$shortcode_id = absint( $block_attributes['shortcode'] );
		}

		if ( ! $shortcode_id ) {
			return '';
		}

		if ( ! teca_shortcode_exists( $shortcode_id ) ) {
			return '';
		}

		return do_shortcode( sprintf( '[gs-teca id="%d"]', $shortcode_id ) );
	}

	/**
	 * Toolbar styles for the block editor panel.
	 *
	 * @return string
	 */
	public function get_block_css() {
		ob_start();
		?>
		.teca--toolbar {
			padding: 20px;
			border: 1px solid #1f1f1f;
			border-radius: 2px;
		}

		.teca--toolbar label {
			display: block;
			margin-bottom: 6px;
			margin-top: -6px;
		}

		.teca--toolbar select {
			width: 250px;
			max-width: 100% !important;
			line-height: 42px !important;
		}

		.teca--toolbar .gs-teca-block--des {
			margin: 10px 0 0;
			font-size: 16px;
		}

		.teca--toolbar .gs-teca-block--des span {
			display: block;
		}

		.teca--toolbar p.gs-teca-block--des a {
			margin-left: 4px;
		}

		.teca--block-placeholder {
			padding: 16px;
			border: 1px dashed #c3c4c7;
			border-radius: 4px;
			color: #50575e;
			background: #f6f7f7;
		}
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Get saved TECA shortcodes for the editor dropdown.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	protected function get_shortcode_list() {
		$shortcodes = get_shortcodes();

		return is_array( $shortcodes ) ? $shortcodes : [];
	}

	/**
	 * Default shortcode ID for new blocks.
	 *
	 * @return string
	 */
	protected function get_default_item() {
		$shortcodes = $this->get_shortcode_list();

		if ( ! empty( $shortcodes ) && isset( $shortcodes[0]['id'] ) ) {
			return (string) absint( $shortcodes[0]['id'] );
		}

		return '';
	}
}
