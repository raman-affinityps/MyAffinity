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
 * Renders options settings page.
 *
 * @category GuiForm
 * @package Render
 * @subpackage Settings
 *
 * @since 1.0
 */
 

class GuiForm_Render_Settings_License extends WP_List_Table {
	
	var $_screen;
	var $_columns;
	var $_message = false;
	
	/**
	* Constructor, we override the parent to pass our own arguments
	* We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
	*/
	function __construct($screen, $columns = array()) {
	 	
	

		parent::__construct( array(
			'singular'=> 'wp_list_text_link', //Singular label
			'plural' => 'wp_list_test_links', //plural label, also this well be one of the table css class
			'ajax'	=> false //We won't support Ajax for this table
			) 
		);
		
		add_action( 'admin_enqueue_scripts', array( &$this, 'enqueueScripts' ));
		
	}
	
	public function toHTML(){
		echo "<div id='settings'>";
		GuiForm_Render_Settings::header("license");
		echo '<div id="guiform-manager" class="wrap license">';
		self::prepare_items();
		self::display();
		echo '</div>';
		echo "</div>";
	} 
	
	
	function enqueueScripts($hook_suffix){
		if($hook_suffix == 'toplevel_page_guiform-settings' || GuiForm_Plugin::NAME .'_page_guiform-setting'){
			global $guiform;
			
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-widget');
			wp_enqueue_script('jquery-ui-sortable');
			wp_enqueue_script('jquery-ui-draggable');
			
			wp_enqueue_script('guifbox', $guiform->assets('js/guifbox.js'), array('jquery'), GuiForm_Plugin::VERSION, true);
		 	wp_enqueue_style('guifbox', $guiform->assets('css/guifbox.css'), false, GuiForm_Plugin::VERSION, 'all');
			
			wp_enqueue_style('guiform-style', $guiform->assets('css/style.css'), false, GuiForm_Plugin::VERSION, 'all');
			wp_enqueue_script('guiform-settings', $guiform->assets('js/script.js'), array('jquery'), GuiForm_Plugin::VERSION, true);
			
			wp_localize_script('guiform-settings', 'guiform', array(
			  'nonce'  => wp_create_nonce( "guiform_nonce" ),
				'assets' => $guiform->assets(),
				'type'   => 'license',
			));
		}
	}
	
	
	/**
	 * Get a list of columns. The format is:
	 * 'internal-name' => 'Title'
	 *
	 * @since 1.0
	 * @access public
	 * @abstract
	 *
	 * @return array
	 */
	function get_columns(){
		return $columns = array(
			'name'       => __('Name', GuiForm_Plugin::NAME),
			'version'    => __('Version', GuiForm_Plugin::NAME),
			'expiration' => __('Expiration', GuiForm_Plugin::NAME),
			'status'     => __('Status', GuiForm_Plugin::NAME),
			'license'    => __('License Key', GuiForm_Plugin::NAME),
			'action'     => __('Action', GuiForm_Plugin::NAME)
		);
	}

	
	function column_default( $item, $column_name ){
		return $item[$column_name];
	}
	
	/**
	 * Message to be displayed when there are no items
	 *
	 * @since 1.0
	 * @access public
	 */
	function no_items() {
	  _e( 'Install and activate GuiForm extension first.', GuiForm_Plugin::NAME);
	}
	
	/**
	 * Prepares the list of items for displaying.
	 * @uses WP_List_Table::set_pagination_args()
	 *
	 * @since 1.0
	 * @access public
	 * @abstract
	 */
	function prepare_items() {
		global $wpdb, $guiform;
		
    $items = array();
    $active = get_option('active_plugins');
 		
 		$plugins = get_plugins();
 
    foreach($active as $plugin){
    	$raw = explode('-', $plugin);
    	$prefix = $raw[0];
    	$raw = explode('/', $plugin);
    	$package = $raw[0];
    	
    	if($prefix == 'guiform' && isset($raw[1])){
    		
    		$data = array('name'    => $plugins[$plugin]['Name'],
    									'version' => $plugins[$plugin]['Version'],
    									'status'  => $guiform->getOption('license_status_'. $package)->value,
    									'license' => '<input type="text" name="license" value="">
    									 			        <input type="hidden" name="package" value="'. $package .'">
    									 			        <input type="hidden" name="name" value="'. $plugins[$plugin]['Name'] .'">'							 
    		 							);
    		
    		if($guiform->getOption('license_status_'. $package)->value != 'valid'){
					$data['action']  = '<a class="button-primary activate-license" href="javascript:void(0)" title="'. __('Activate License', GuiForm_Plugin::NAME) .'">'. __('Activate License', GuiForm_Plugin::NAME) .'</a>';
    			$data['expiration'] = '';
    		}
    		else{
    			$data['action']  = '<a class="button-secondary deactivate-license" href="javascript:void(0)" title="'. __('Deactivate License', GuiForm_Plugin::NAME) .'">'. __('Deactivate License', GuiForm_Plugin::NAME) .'</a>';
    			$data['expiration']  = date('F d, Y', strtotime($guiform->getOption('license_expires_'. $package)->value));
    		}
    		
    		$items[] = $data;								
    	}
    	
    }
     
    $this->items = $items;
    

	}

}