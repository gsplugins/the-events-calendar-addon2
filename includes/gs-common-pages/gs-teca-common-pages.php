<?php

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedNamespaceFound -- Existing plugin namespace is intentionally kept for backward compatibility.
namespace GS_TECA;
if ( ! defined( 'ABSPATH' ) ) exit;
require_once plugin_dir_path( __FILE__ ) . 'gs-plugins-common-pages.php';

add_action(
	'init',
	function () {
		new GS_Plugins_Common_Pages(
			array(
				'parent_slug'     => 'gs-the-events-calendar-addon',
				'lite_page_title' => __( 'Lite Plugins by GS Plugins', 'the-events-calendar-addon2' ),
				'pro_page_title'  => __( 'Premium Plugins by GS Plugins', 'the-events-calendar-addon2' ),
				'help_page_title' => __( 'Support & Documentation by GS Plugins', 'the-events-calendar-addon2' ),
				'lite_page_slug'  => 'the-events-calendar-addon2',
				'pro_page_slug'   => 'the-events-calendar-addon-pro',
				'help_page_slug'  => 'the-events-calendar-addon-help',
				'links'           => array(
					'docs_link'   => 'https://docs.gsplugins.com/woocommerce-product-slider/',
					'rating_link' => 'https://wordpress.org/support/plugin/gs-woocommerce-products-slider/reviews/#new-post',
				),
			)
		);
	},
	1
);
