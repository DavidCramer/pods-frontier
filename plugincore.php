<?php
/**
 * @package   Pods_Frontier
 * @author    David Cramer <david@digilab.co.za>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 David Cramer
 *
 * @wordpress-plugin
 * Plugin Name: Pods Frontier
 * Plugin URI:  
 * Description: A suite of advanced templating features for Pods.
 * Version:     1.000
 * Author:      David Cramer
 * Author URI:  
 * Text Domain: pods-frontier
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'FRONTIER_URL', plugin_dir_url( __FILE__ ) );
define( 'FRONTIER_DIR', plugin_dir_path( __FILE__ ) );

register_activation_hook( __FILE__, 'frontier_toggle' );
register_deactivation_hook( __FILE__, 'frontier_toggle' );

// hook-in to get frontier in the menus on activation.
function frontier_toggle($a){
	delete_transient( 'pods_components' );
}


if ( class_exists( 'Pods_Frontier_Template_Editor' ) || class_exists( 'Pods_Templates_Frontier' ) )
	return;

require_once( plugin_dir_path( __FILE__ ) . 'pods-frontier-templates.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/frontier-grid.php' );
require_once( plugin_dir_path( __FILE__ ) . 'elements/frontier_forms.php' );

// Load instance of template editor overide
Pods_Frontier_Template_Editor::get_instance();