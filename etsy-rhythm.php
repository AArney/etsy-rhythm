<?php
/**
 * The WordPress Plugin Boilerplate.
 *
 * A foundation off of which to build well-documented WordPress plugins that
 * also follow WordPress Coding Standards and PHP best practices.
 *
 * Etsy Rhythm
 *
 * @package   Etsy_Rhythm
 * @author    Aaron Arney <aaron.arney@ocular-rhythm.com>
 * @license   GPL-2.0+
 * @link      http://www.ocular-rhythm.com
 * @copyright 2013 Aaron Arney
 *
 *
 * @etsy-rhythm
 * Plugin Name:       Etsy Rhythm
 * Plugin URI:        https://github.com/AArney/etsy-rhythm
 * Description:       A plug-in that retrieves and displays shop listings from Etsy
 * Version:           1.0.4
 * Author:            Aaron Arney
 * Author URI:        https://github.com/AArney
 * Text Domain:       etsy-rhtyhm-locale
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

require_once( plugin_dir_path( __FILE__ ) . '/public/class-etsy-rhythm.php' );
require_once( plugin_dir_path( __FILE__ ) . '/admin/class-etsy-rhythm-admin.php' );


register_activation_hook( __FILE__, array( 'Etsy_Rhythm', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Etsy_Rhythm', 'deactivate' ) );


add_action( 'plugins_loaded', array( 'Etsy_Rhythm', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

	require_once( plugin_dir_path( __FILE__ ) . '/admin/class-etsy-rhythm-admin.php' );
	add_action( 'plugins_loaded', array( 'Etsy_Rhythm_Admin', 'get_instance' ) );

}
