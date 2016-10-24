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
 * Export page.
 *
 * @category GuiForm
 * @package Render
 *
 * @since 1.0
 * @abstract
 */
class GuiForm_Render_Extension extends GuiForm_Render{
	
  const NAME = __CLASS__;
  
  private $_module;
  
  var $navigation;
	
	/**
	 * Constructor.
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @param array $data The data what has to be associated with this render.
	 */
	public function __construct() {
		add_action('admin_enqueue_scripts', array($this, 'scripts'));
		$this->navigation = new GuiForm_Module_Navigation();
		$this->navigation->init(GuiForm_Render_Forms::NAME);
	}
	
	public function scripts($hook_suffix){
		global $guiform;
		if($hook_suffix == GuiForm_Plugin::NAME .'_page_guiform-extension'){
			wp_enqueue_style('style',$guiform->assets('css/style.css'), false, GuiForm_Plugin::VERSION, 'all');
		}
	}
	
	protected function _toHTML() {}

	public function toHTML(){
		global $wpdb, $guiform;
		
		echo '<div id="guiform-manager" class="wrap extension clearfix metabox-holder">';
		// data to send in our API request	
		$this->navigation->toHTML();	
		
		$output = get_transient('guiform_extension_content');
		
		if ( !$output ) {
			
			$api_params = array(
				'content'    => true,
				'sslverify'  => true,
				'package'    => GuiForm_Plugin::PACKAGE,
				'version'    => GuiForm_Plugin::VERSION,
				'content'    => 'extensions'       
			);
		
			// Call the custom API.
			$response = wp_safe_remote_post(GuiForm_Plugin::SERVER ."?api&", array( 'timeout' => 30, 'sslverify' => true, 'body' => $api_params ) );
			if (is_wp_error( $response ) ){
				delete_transient('guiform_extension_content');
		  }
			else{
				$data =  wp_remote_retrieve_body( $response ) ;
				set_transient('guiform_extension_content', $data, HOUR_IN_SECONDS );
				echo $data;
			}
		}
		else{
			echo $output;
		}
		
		echo '</div>';
	}
	
}