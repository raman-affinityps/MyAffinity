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
 * Base class update module, Check plugin information and new version in GuiForm server.
 *
 * @category GuiForm
 * @package Module
 *
 * @since 1.0
 */
class GuiForm_Module_Update extends GuiForm_Module {

	const NAME = __CLASS__;
	
	private $api_url  = '';
	private $api_data = array();
	private $name     = '';
	private $slug     = '';

	/**
	 * Class constructor.
	 *
	 * @uses plugin_basename()
	 * @uses hook()
	 *
	 * @param string $_api_url The URL pointing to the custom API endpoint.
	 * @param string $_plugin_file Path to the plugin file.
	 * @param array $_api_data Optional data to send with API calls.
	 * @return void
	 */
	 public function __construct( GuiForm_Plugin $plugin ){
		parent::__construct( $plugin );
	}

	/**
	 * Set up Wordpress filters to hook into WP's update process.
	 *
	 * @uses add_filter()
	 *
	 * @return void
	 */
	public function init(){
		global $guiform;
		
		$_api_data = array( 
			'version' 	=> GuiForm_Plugin::VERSION, 				// current version number
			'license' 	=> $guiform->getOption('license_key_'. GuiForm_Plugin::NAME)->value, 		// license key (used get_option above to retrieve from DB)
			'item_name' => GuiForm_Plugin::PACKAGE, 	// name of this plugin
			'author' 	  => GuiForm_Plugin::AUTHOR  // author of this plugin
		);
		
		$this->api_url  = trailingslashit(GuiForm_Plugin::SERVER);
		$this->api_data = $_api_data;
		$this->name     = GuiForm_Plugin::SLUG;
		$this->slug     = GuiForm_Plugin::NAME;
		$this->version  = GuiForm_Plugin::VERSION;
		
    // define the alternative API for updating checking
    $this->_addFilter('pre_set_site_transient_update_plugins', 'pre_set_site_transient_update_plugins_filter');

    // Define the alternative response for information checking
    $this->_addFilter('plugins_api', 'plugins_api_filter', 10, 3);
    
    $this->_addFilter('http_request_args', 'http_request_args', 10, 3);
    
  }

	/**
	 * Check for Updates at the defined API endpoint and modify the update array.
	 *
	 * This function dives into the update api just when Wordpress creates its update array,
	 * then adds a custom API call and injects the custom plugin data retrieved from the API.
	 * It is reassembled from parts of the native Wordpress plugin update code.
	 * See wp-includes/update.php line 121 for the original wp_update_plugins() function.
	 *
	 * @uses _requestApi()
	 *
	 * @param array $_transient_data Update array build by Wordpress.
	 * @return array Modified update array with custom plugin data.
	 */
	public function pre_set_site_transient_update_plugins_filter( $_transient_data ) {
	
		if( empty( $_transient_data ) ) return $_transient_data;

		$to_send = array( 'slug' => $this->slug );

		$api_response = $this->_requestApi( 'plugin_latest_version', $to_send );
		
		if( false !== $api_response && is_object( $api_response ) && isset( $api_response->new_version ) ) {
	
			if( version_compare( $this->version, $api_response->new_version, '<' ) )
				$_transient_data->response[$this->name] = $api_response;
		}
		
		return $_transient_data;
	}


	/**
	 * Updates information on the "View version x.x details" page with custom data.
	 *
	 * @uses _requestApi()
	 *
	 * @param mixed $_data
	 * @param string $_action
	 * @param object $_args
	 * @return object $_data
	 */
	public function plugins_api_filter( $_data, $_action = '', $_args = null ) {
		if ( ( $_action != 'plugin_information' ) || !isset( $_args->slug ) || ( $_args->slug != $this->slug ) ) return $_data;

		$to_send = array( 'slug' => $this->slug );

		$api_response = $this->_requestApi( 'plugin_information', $to_send );
		if ( false !== $api_response ) $_data = $api_response;

		return $_data;
	}


	/**
	 * Disable SSL verification in order to prevent download update failures
	 *
	 * @param array $args
	 * @param string $url
	 * @return object $array
	 */
	public function http_request_args( $args, $url ) {
		// If it is an https request and we are performing a package download, disable ssl verification
		if( strpos( $url, 'https://' ) !== false && strpos( $url, 'edd_action=package_download' ) ) {
			$args['sslverify'] = false;
		}
		return $args;
	}

	/**
	 * Calls the API and, if successfull, returns the object delivered by the API.
	 *
	 * @uses get_bloginfo()
	 * @uses wp_remote_post()
	 * @uses is_wp_error()
	 *
	 * @param string $_action The requested action.
	 * @param array $_data Parameters for the API action.
	 * @return false||object
	 */
	private function _requestApi( $_action, $_data ) {

		global $wp_version;

		$data = array_merge( $this->api_data, $_data );

		if( $data['slug'] != $this->slug )
			return;

		if( empty( $data['license'] ) )
			return;

		$api_params = array(
			'edd_action' => 'get_version',
			'license' 	 => $data['license'],
			'name' 			 => $data['item_name'],
			'slug' 			 => $this->slug,
			'author'		 => $data['author'],
			'url'        => home_url()
		);
		
		
		$request = wp_remote_post( $this->api_url, array( 'timeout' => 15, 'sslverify' => true, 'body' => $api_params ) );

		if ( ! is_wp_error( $request ) ):
			$request = json_decode( wp_remote_retrieve_body( $request ) );
			if( $request && isset( $request->sections ) )	$request->sections = maybe_unserialize( $request->sections );
			//print_r($request);
			$request->author = '<a href="https://www.odesk.com/users/~01ef85c3851e913703">Russell Pabon</a>';
			$request->tested = $request->tested_up_to;
      $request->version = $request->new_version;
		
			return $request;
		else:
			return false;
		endif;
	}
}