<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://affinityps.com
 * @since             1.0.0
 * @package           Affinity Costco
 *
 * @wordpress-plugin
 * Plugin Name:       Affinity Costco Validation
 * Plugin URI:        http://affinityps.com/affinity-costco/
 * Description:       Plugin for validating Costco Memberships and tracking requests for open market
 * Version:           1.0.0
 * Author:            Affinity Partnerships
 * Author URI:        http://affinityps.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       affinity-costco
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-affinity-costco-activator.php
 */
function activate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-affinity-costco-activator.php';
	Affinity_Costco_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-affinity-costco-deactivator.php
 */
function deactivate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-affinity-costco-deactivator.php';
	Affinity_Costco_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_plugin_name' );
register_deactivation_hook( __FILE__, 'deactivate_plugin_name' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-affinity-costco.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/functions-affinity-costco.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_affinity_costco() {

	$plugin = new Affinity_Costco();
	$plugin->run();

}
run_affinity_costco();


/*
	shortcode formatting:
	[ap_loan_form (purchase|refinance|heloc|vet_purchase|vet_refinance)]

	example (Draw Purchase Form)
	[ap_loan_form purchase]
*/
add_shortcode('ap_costco_form', 'affinity_costco_form');


add_action('wp_ajax_apc_ajax', 'apcostco_handle_ajax_requests');
add_action('wp_ajax_nopriv_apc_ajax', 'apcostco_handle_ajax_requests');


function apcostco_handle_ajax_requests() {
	require_once plugin_dir_path( __FILE__ ) . '/includes/ajax-affinity-pricing.php';

	apcostco_ajax_requests();
}


// **************************************************************************************
// force update check
// **************************************************************************************
//
// set_site_transient('update_plugins', null);



/*
// TEMP: Enable update check on every request. Normally you don't need this! This is for testing only!
// NOTE: The 
//	if (empty($checked_data->checked))
//		return $checked_data; 
// lines will need to be commented in the check_for_plugin_update function as well.
// TEMP: Show which variables are being requested when query plugin API
add_filter('plugins_api_result', 'aaa_result', 10, 3);
function aaa_result($res, $action, $args) {
	print_r($res);
	return $res;
}
// NOTE: All variables and functions will need to be prefixed properly to allow multiple plugins to be updated
*/


$api_url = 'http://secure.affinityps.com/wp/';
$plugin_slug = basename(dirname(__FILE__));

// Take over the update check
add_filter('pre_set_site_transient_update_plugins', 'apcostco_check_for_plugin_update');

function apcostco_check_for_plugin_update($checked_data) {
	global $api_url, $plugin_slug, $wp_version;

	
	//Comment out these two lines during testing.
//	if (empty($checked_data->checked))
//		return $checked_data;
	
	$args = array(
		'slug' => $plugin_slug,
		'version' => $checked_data->checked[$plugin_slug .'/'. $plugin_slug .'.php'],
	);
	$request_string = array(
		'body' => array(
			'action' => 'basic_check', 
			'request' => serialize($args),
			'api-key' => md5(get_bloginfo('url'))
		),
		'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
	);
	
	// Start checking for an update
	$raw_response = wp_remote_post($api_url . $plugin_slug, $request_string);

	if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200))
		$response = unserialize($raw_response['body']);
	
	if (is_object($response) && !empty($response)) // Feed the update data into WP updater
		$checked_data->response[$plugin_slug .'/'. $plugin_slug .'.php'] = $response;
	
	return $checked_data;
}


// Take over the Plugin info screen
add_filter('plugins_api', 'apcostco_plugin_api_call', 10, 3);

function apcostco_plugin_api_call($def, $action, $args) {
	global $plugin_slug, $api_url, $wp_version;
	
	if (!isset($args->slug) || ($args->slug != $plugin_slug))
		return false;
	
	// Get the current version
	$plugin_info = get_site_transient('update_plugins');
	$current_version = $plugin_info->checked[$plugin_slug .'/'. $plugin_slug .'.php'];
	$args->version = $current_version;
	
	$request_string = array(
			'body' => array(
				'action' => $action, 
				'request' => serialize($args),
				'api-key' => md5(get_bloginfo('url'))
			),
			'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
		);
	
	$request = wp_remote_post($api_url, $request_string);
	
	if (is_wp_error($request)) {
		$res = new WP_Error('plugins_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>'), $request->get_error_message());
	} else {
		$res = unserialize($request['body']);
		
		if ($res === false)
			$res = new WP_Error('plugins_api_failed', __('An unknown error occurred'), $request['body']);
	}
	
	return $res;
}