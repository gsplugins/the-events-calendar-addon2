<?php

/**
 *
 * @package   the-events-calendar-addon
 * @author    GS Plugins <hello@gsplugins.com>
 * @license   GPL-2.0+
 * @link      https://www.gsplugins.com/
 * @copyright 2015 GS Plugins
 *
 * @wordpress-plugin
 * Plugin Name:			The Events Calendar Addon
 * Plugin URI:			https://www.gsplugins.com/product/events/
 * Description:       	The Events Calendar Addon is a clean, flexible events calendar plugin that makes creating, managing, and displaying events effortless. Simple. Reliable. Beautiful.. Use shortcodes such as [gs-teca id="1"] to place grids anywhere. Check <a href="https://the-events-calendar-addon.gsplugins.com/">GS Post Grid PRO Demo</a> and <a href="https://docs.gsplugins.com/wordpress-posts-grid/">Documentation</a>.
 * Version:           	1.0.0
 * Author:       		GS Plugins
 * Author URI:       	https://www.gsplugins.com/
 * Text Domain:       	the-events-calendar-addon
 * License:           	GPL-2.0+
 * License URI:       	http://www.gnu.org/licenses/gpl-2.0.txt
 * Requires Plugins:    the-events-calendar
 */

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Defining constants
 */
if ( ! defined( 'GS_TECA_VERSION' ) )
    define( 'GS_TECA_VERSION', '1.0.0' );

if ( ! defined( 'GS_TECA_MENU_POSITION' ) )
    define( 'GS_TECA_MENU_POSITION', 6 );

if ( ! defined( 'GS_TECA_PLUGIN_FILE' ) )
    define( 'GS_TECA_PLUGIN_FILE', __FILE__ );

if ( ! defined( 'GS_TECA_PLUGIN_DIR' ) )
    define( 'GS_TECA_PLUGIN_DIR', trailingslashit( plugin_dir_path( GS_TECA_PLUGIN_FILE ) ) );

if ( ! defined( 'GS_TECA_PLUGIN_URI' ) )
    define( 'GS_TECA_PLUGIN_URI', trailingslashit( plugins_url( '', GS_TECA_PLUGIN_FILE ) ) );

if ( ! defined('GS_TECA_PLUGIN_URL') ) {
    define( 'GS_TECA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

require_once GS_TECA_PLUGIN_DIR . 'includes/autoloader.php';
require_once GS_TECA_PLUGIN_DIR . 'includes/functions.php';
require_once GS_TECA_PLUGIN_DIR . 'includes/init.php';



