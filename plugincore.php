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
 * Version:     1.00
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

if ( class_exists( 'Pods_Frontier_Template_Editor' ) || class_exists( 'Pods_Templates_Frontier' ) )
	return;

require_once( plugin_dir_path( __FILE__ ) . 'class-pods-frontier.php' );


// Load instance
Pods_Frontier_Template_Editor::get_instance();

