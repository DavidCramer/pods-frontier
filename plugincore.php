<?php
/**
 * @package   Pods_Frontier_Template_Editor
 * @author    David Cramer <david@digilab.co.za>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 David Cramer
 *
 * @wordpress-plugin
 * Plugin Name: Pods Frontier Template Editor
 * Plugin URI:  
 * Description: With auto-complete, magic tags and field reference.
 * Version:     1.00
 * Author:      David Cramer
 * Author URI:  
 * Text Domain: pods-frontier-template-editor
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once( plugin_dir_path( __FILE__ ) . 'class-pods-frontier-template-editor.php' );
require_once( plugin_dir_path( __FILE__ ) . '/includes/functions-view_template.php' );
require_once( plugin_dir_path( __FILE__ ) . '/includes/functions-pod_reference.php' );


// Register hooks that are fired when the plugin is activated or deactivated.
// When the plugin is deleted, the uninstall.php file is loaded.
register_activation_hook( __FILE__, array( 'Pods_Frontier_Template_Editor', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Pods_Frontier_Template_Editor', 'deactivate' ) );

// Load instance
//add_action( 'plugins_loaded', array( 'Pods_Frontier_Template_Editor', 'get_instance' ) );
Pods_Frontier_Template_Editor::get_instance();
?>