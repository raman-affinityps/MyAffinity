<?php

// +----------------------------------------------------------------------+
// | Copyright 2013 GuiForm (email : info@guiform.com)                    |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or modify |
// | it under the terms of the GNU General Public License, version 2, as  |
// | published by the Free Software Foundation.                           |
// |                                                                      |
// | This program is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
// | GNU General Public License for more details.                         |
// |                                                                      |
// | You should have received a copy of the GNU General Public License    |
// | along with this program; if not, write to the Free Software          |
// | Foundation, Inc., 51 Franklin St, Fifth Floor, Boston,               |
// | MA 02110-1301 USA                                                    |
// +----------------------------------------------------------------------+
// | Author: Russell Pabon <russellpabon@guiform.com>                     |
// +----------------------------------------------------------------------+

/**
 * General module license checker.
 *
 * @category GuiForm
 * @package Module
 *
 * @since 1.0
 */
class GuiForm_Module_License extends GuiForm_Module {

	const NAME = __CLASS__;
	
	/**
	 * Constructor.
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @param GuiForm_Plugin $plugin The instance of the plugin.
	 */
	public function __construct( GuiForm_Plugin $plugin ) {
		parent::__construct( $plugin );
		global $guiform;
	}
	
	public function activateLicense($param){
		global $guiform;
		
		// data to send in our API request
		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $param['license'],
			'sslverify'  => false,
			'item_name'  => urlencode($param['name']), // the name of our product
			'url'        => urlencode(home_url())
		);
		
		// Call the custom API.
		$response = wp_safe_remote_post(GuiForm_Plugin::SERVER, array( 'timeout' => 90, 'sslverify' => false, 'body' => $api_params ) );
	
		// make sure the response came back okay
		if (is_wp_error( $response ) )	return false;
		 
		$data = json_decode( wp_remote_retrieve_body( $response ) );
		$data->expires = date('F d, Y', strtotime($data->expires));
		
		if($data->license == 'valid'){
			$guiform->updateOption('license_status_'. $param['package'], $data->license, 'settings');
		  $guiform->updateOption('license_key_'. $param['package'], $param['license'], 'settings');
			$guiform->updateOption('license_expires_'. $param['package'], $data->expires, 'settings');
			$output = GuiForm_Module_License::api();
		}
		
		return $data;
	}
	
	public function deactivateLicense($param){
		global $guiform;
		
		// data to send in our API request
		$api_params = array(
			'edd_action' => 'deactivate_license',
			'license'    => $param['license'],
			'sslverify'  => false,
			'item_name'  => urlencode($param['name']), // the name of our product
			'url'        => urlencode(home_url())
		);
		
		// Call the custom API.
		$response = wp_safe_remote_post(GuiForm_Plugin::SERVER, array( 'timeout' => 90, 'sslverify' => false, 'body' => $api_params ) );
		
		// make sure the response came back okay
		if (is_wp_error( $response ) )	return false;
		 
		$data = json_decode( wp_remote_retrieve_body( $response ) );
		$data->expires = date('F d, Y', strtotime($data->expires));
		
		if($data->license != 'failed'){
			$guiform->updateOption('license_status_'. $param['package'], $data->license, 'settings');
		  $guiform->updateOption('license_expires_'. $param['package'], '', 'settings');
		  $guiform->updateOption('license_key_'. $param['package'], '', 'settings');
		  delete_transient($param['license']);
		}
		
		return $data;
	}
	
	public function checkLicense($param){
		// data to send in our API request
		$api_params = array(
			'edd_action' => 'check_license',
			'license'    => $param['license'],
			'sslverify'  => true,
			'item_name'  => urlencode(GuiForm_Plugin::NAME), // the name of our product in EDD,
			'url'        => urlencode(home_url())
		);
		
		// Call the custom API.
		$response = wp_safe_remote_post(GuiForm_Plugin::SERVER, array( 'timeout' => 90, 'sslverify' => true, 'body' => $api_params ) );

		// make sure the response came back okay
		if (is_wp_error( $response ) )	return false;
		 
		$data = json_decode( wp_remote_retrieve_body( $response ) );
		
		return $data;
	}
	
	public function api(){
		global $guiform;
		
		// data to send in our API request		
		$api_params = array(
			'license'    => $guiform->getOption('license_key_'. GuiForm_Plugin::NAME)->value,
			'key'        => isset($_REQUEST['key']) ? esc_sql($_REQUEST['key']) : false,
			'sslverify'  => true,
			'version'    => GuiForm_Plugin::VERSION        
		);
		
		// Call the custom API.
		$response = wp_safe_remote_post(GuiForm_Plugin::SERVER ."?api=key&", array( 'timeout' => 90, 'sslverify' => true, 'body' => $api_params ) );

		// make sure the response came back okay
		if (is_wp_error( $response ) )	return false;
		 
		$data =  wp_remote_retrieve_body( $response ) ;
		
		return $data;
	}
	
	public function getScript(){
		
		global $guiform;
		
		$key = $guiform->getOption('license_key_'. GuiForm_Plugin::NAME)->value;
    
    if(isset($_REQUEST['key'])) delete_transient($key);
    
    $output = get_transient($key);
    
    if ( !$output ) {
    	
    	$status = array('empty_license'      => __("The license key was empty.", GuiForm_Plugin::NAME), 
    	                'invalid'            => __("Invalid license key.", GuiForm_Plugin::NAME),
    	                'expired'            => __("This license has expired.", GuiForm_Plugin::NAME), 
    	                'inactive'           => __("This license is not active.", GuiForm_Plugin::NAME), 
    	                'disabled'           => __("License key disabled.", GuiForm_Plugin::NAME), 
    	                'site_inactive'      => __("Inactive domain for this license.", GuiForm_Plugin::NAME), 
    	                'item_name_mismatch' => __("Item names don't match.", GuiForm_Plugin::NAME));
    	
    	$output = self::api();
      if(!in_array($output, array_keys($status))){
      	$output = trim($output);
      	if(!isset($_REQUEST['key']) && !empty($output)) set_transient($key, $output, 60*10 );
  		}
  		else{
  			$output = "
  			var $ = jQuery;
  			$('<div>').guifbox({
					title: 'error: $output', 
					status: 'error',
					opacity: .5,
					width: 350,
					body: '<p style=\'padding: 10px; font-size: 14px;\'>". esc_sql($status[$output]) ."</p>',
					overlay: true
				});
				
				$('#GuiForm div').show();
				
				";
  			
  			delete_transient($key);
  		}
    }
    
    return $output;
	}
}