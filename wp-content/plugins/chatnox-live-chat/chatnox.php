<?php
/*
Plugin Name: ChatNox Chat
Plugin URI: http://www.chatnox.com
Description: ChatNox is an easy to use Live Chat Software for Wordpress websites. With ChatNox, you can see who's online, send targeted proactive messages and chat with website visitors!
Version: 1.0
Author: ChatNox
Author URI: http://www.chatnox.com
License: GPL2
*/
?>
<?php

// ChatNox Plugin Constants 
define('CHATNOX_DOMAIN_URL', "http://app.chatnox.com/");
define('CHATNOX_LOGIN_URL', CHATNOX_DOMAIN_URL."pluginsAuth");
define('CHATNOX_DASHBOARD_LINK', CHATNOX_DOMAIN_URL."?utm_source=wp&utm_medium=link&utm_campaign=wp%2Bdashboard");
define('CHATNOX_SMALL_LOGO',  "https://www.chatnox.com/assets/plugins/chatnox-wordpress-icon-3.png");

// ChatNox slot id
define('CHATNOX_DB_SLOT_NAME', "chatnoxUserSlotId");
define('CHATNOX_DB_USER_NAME', "chatnoxUserName");
define('CHATNOX_DB_USER_ACCOUNT_ID', "chatnoxUserAccountId");

require_once dirname( __FILE__ ) . '/chatnoxconfig.php';

function load_chatnox_style() {	
	wp_register_style('chatnox_style', plugins_url('chatnox.css', __FILE__));
	wp_enqueue_style('chatnox_style');
}

add_action('admin_enqueue_scripts', 'load_chatnox_style');

// Save slot id
function chatnox_livechat_save_options($name,  $val ) {
	return update_option( $name, $val );
}
//  Get slot id
function chatnox_livechat_get_options($name) {
	$optionVal = get_option( $name );
	return $optionVal;
}

// Remove dbs while Uninstalling 
function chatnox_livechat_uninstall() {
	// Delete all options for db
	delete_option( CHATNOX_DB_SLOT_NAME );
	delete_option( CHATNOX_DB_USER_NAME );
	delete_option( CHATNOX_DB_USER_ACCOUNT_ID );
	
}

function chatnox_widget_add_scripts() {

	$slotId = chatnox_livechat_get_options(CHATNOX_DB_SLOT_NAME);

	// If null   
    if(strlen($slotId) == 0)
		return;

    wp_enqueue_script('chatnox_script_insert', plugins_url('/chatnox.js', __FILE__), array('jquery'), '1.0.1',true);
	?>
	<!-- ChatNox Widget -->
	<script  type="text/javascript">
	var _chatnox = _chatnox || [];_chatnox.setAccount = '<?php echo $slotId ?>';	
	</script>
	<!-- ChatNox Widget Ends -->
<?php
}
/******************* ChatNox Live-Chat Widget Install *******************************************/
if ( ! is_admin() )
add_action("wp_print_scripts", "chatnox_widget_add_scripts");
register_deactivation_hook(__FILE__, 'chatnox_livechat_uninstall');
/******************* End of ChatNox Live-Chat Widget Install ************************************/

/********************* Start of Menu Options ***********/				

// create custom plugin menu
add_action('admin_menu', 'chatnox_create_menu');   

//create admin menu
function chatnox_create_menu() {
   //create new top-level menu
   add_menu_page('ChatNox Configuration', 'ChatNox Chat', 'administrator', 'chatnox_configuration', 'chatnox_configuration',CHATNOX_SMALL_LOGO);
}

function chatnox_post_request($url, $data){

	$response = wp_remote_post( $url, array(
	'method' => 'POST',
	'headers' => array(),
	'body' => $data,
	'cookies' => array()
    )
);

return $response['body'];

}

?>