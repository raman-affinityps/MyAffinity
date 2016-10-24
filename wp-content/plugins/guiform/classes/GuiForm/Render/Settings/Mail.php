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
 * Renders mail settings page.
 *
 * @category GuiForm
 * @package Render
 * @subpackage Settings
 *
 * @since 1.0
 */
 

class GuiForm_Render_Settings_Mail extends WP_List_Table {
	
	var $_screen;
	var $_columns;
	var $_message = false;
	
	/**
	* Constructor, we override the parent to pass our own arguments
	* We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
	*/
	function __construct($screen, $columns = array()) {
	 	
		if ( is_string( $screen ) )
			$screen = convert_to_screen( $screen );

		$this->_screen = $screen;

		if ( !empty( $columns ) ) {
			$this->_columns = $columns;
			add_filter( 'manage_' . $screen->id . '_columns', array( &$this, 'get_columns' ), 0 );
		}
		
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
		GuiForm_Render_Settings::header("mail");
		echo '<div id="guiform-manager" class="wrap mail">';
		$url = add_query_arg('','');
		self::process_bulk_action();
		self::show_message();
		echo "<form id='mail-filter' method='post' action='$url'>";
		echo '<div><a id="add-mail" class="button-primary" title="Add New Email" href="javascript:void(0)">Add New Mail</a></div>';
		self::views();
		self::prepare_items();
		self::display();
		self::get_column_info();
		echo "</form>";
		echo '</div>';
		echo "</div>";
	} 
	
	
	function enqueueScripts($hook_suffix){
		if($hook_suffix == GuiForm_Plugin::NAME .'_page_guiform-settings'){
			global $guiform;
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-widget');
			
			wp_enqueue_script('guifbox', $guiform->assets('js/guifbox.js'), array('jquery'), GuiForm_Plugin::VERSION, true);
		 	wp_enqueue_style('guifbox', $guiform->assets('css/guifbox.css'), false, GuiForm_Plugin::VERSION, 'all');
			
			wp_enqueue_style('guiform-style', $guiform->assets('css/style.css'), false, GuiForm_Plugin::VERSION, 'all');
			wp_enqueue_script('guiform-settings', $guiform->assets('js/script.js'), array('jquery'), GuiForm_Plugin::VERSION, true);
			
			wp_localize_script('guiform-settings', 'guiform', array(
			  'nonce' => wp_create_nonce( "guiform_nonce" ),
				'assets' => $guiform->assets(),
				'type' => 'mail'
			));
		}
	}
	
	/**
	 * Generates content for a single row of the table
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @param object $item The current item
	 */
	function single_row( $item ) {
		
		static $row_class = 'class="row-item"';
		$row_class = ( $row_class == 'class="row-item"' ? ' class="alternate row-item"' : 'class="row-item"' );

		echo '<tr id="'.$item['id'].'"' . $row_class . '>';
		$this->single_row_columns( $item );
		echo '</tr>';
	}	
	
	/**
	 * Get an associative array ( option_name => option_title ) with the list
	 * of bulk actions available on this table.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @return array
	 */
	function get_bulk_actions() {
	  $actions = array(
	    'delete-permanently' => __("Delete", GuiForm_Plugin::NAME)
	  );
	  return $actions;
	}
	
	function process_bulk_action() {
		global $wpdb;
	
		switch( $this->current_action() ) {
			case 'delete-permanently' :
			if(sizeof($_POST['wp_list_text_link']) > 0){
				foreach ($_POST['wp_list_text_link'] as $id ) {
					$wpdb->delete($wpdb->guiform_options, array('id' => $id), array('%d' ));
				}
				$this->_message = true;
			}
			break;
		}
	}
	
	function show_message(){
		if($this->_message){
			switch( $this->current_action() ) {
				case 'delete-permanently' :
					echo '<div id="message" class="updated">
						    	<p><strong>'. __("Delete mail successful.", GuiForm_Plugin::NAME) .'</strong></p>
						    </div>';
					$this->_message = false;
				break;
			}
		}
	}
	
	/**
	 * Get a list of all, hidden and sortable columns, with filter applied
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @return array
	 */
	function get_column_info(){
		$columns  = get_column_headers( $this->_screen );
		$hidden   = get_hidden_columns( $this->_screen );
		$sortable = $this->get_sortable_columns();

		return array( $columns, $hidden, $sortable );
	}
	
	/* or
	 * 'internal-name' => array( 'orderby', true )
	 *
	 * The second format will make the initial sorting order be descending
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return $sortable = array(
			'mail'=> array('mail', true )
		);
	}
	
	function column_mail($item) {
		
	  $actions = array(
	  					'quick_edit' => sprintf('<a class="mail-quick-edit" id="item-%d" href="javascript:void(0)">Quick Edit</a>',$item['id']),
	            'delete'     => sprintf('<a class="delete" data-type="mail" id="item-%d" href="javascript:void(0)">Delete</a>', $item['id'])
	            );
	 
	  $title = "<a style='display: block;' href='javascript:void(0)'>".$item['name']."</a>";
	  return sprintf('%1$s %2$s', $title, $this->row_actions($actions) );
	}
	
	
	
	/**
	 * Display the search box.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @param string $text The search button text
	 * @param string $input_id The search input id
	 */
	function search_box( $text, $input_id ) {
	    parent::search_box( $text, $input_id );
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
			'cb' 			    => "<input type='checkbox' />",
			'mail'        => __('Mail', GuiForm_Plugin::NAME),
			'test_button' => __('Test Mail', GuiForm_Plugin::NAME),
			'mail_name'   => __('Name', GuiForm_Plugin::NAME),
			'protocol'    => __('Mail Protocol', GuiForm_Plugin::NAME)
		);
	}
	
	
	function column_cb( $item ){
		return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item['id'] );
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
	  _e( 'No item available.', GuiForm_Plugin::NAME);
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
		global $wpdb;
		
				// get the current user ID
			$user = get_current_user_id();
	
			// get the current admin screen
			$screen = get_current_screen();
	
			// retrieve the "per_page" option
			$screen_option = $screen->get_option( 'per_page', 'option' );
	
			// retrieve the value of the option stored for the current user
			$perpage = get_user_meta( $user, $screen_option, true );
	
			// get the default value if none is set
			if ( empty ( $perpage) || $perpage < 1 ){
				$perpage = $screen->get_option( 'per_page', 'default' );
			}
			
		  
			$table = $wpdb->guiform_options;
			
			
			/* -- Preparing your query -- */
			$query = "SELECT id, name, value FROM $table WHERE type = 'mail'";
			//echo $query;
			/* -- Ordering parameters -- */
			//Parameters that are going to be used to order the result
			$orderby = !empty($_GET["orderby"]) ? esc_sql($_GET["orderby"]) : 'ASC';
			$order = !empty($_GET["order"]) ? esc_sql($_GET["order"]) : '';
			if(!empty($orderby) & !empty($order)){ 
				$query.=' ORDER BY '.$orderby.' '.$order; 
			}
			else{
				$query.=' ORDER BY name DESC';
			}	
				
				
		/* -- Pagination parameters -- */
		  //Number of elements in your table?
		  $totalitems = $wpdb->query($query); //return the total number of affected rows
		
		  //How many to display per page?
		  //echo "<h1>$totalitems</h1>";
		  
			// retrieve the "per_page" option
		  //Which page is this?
		  $paged = !empty($_GET["paged"]) ? esc_sql($_GET["paged"]) : '';
		  //Page Number
		  if(empty($paged) || !is_numeric($paged) || $paged<=0 ){ $paged=1; }
		  //How many pages do we have in total?
		  
		  $totalpages = ceil($totalitems / $perpage);
		  //adjust the query to take pagination into account
	    if(!empty($paged) && !empty($perpage)){
		    $offset=($paged-1)*$perpage;
	  		$query.=' LIMIT '.(int)$offset.','.(int)$perpage;
	    }
	    
	    
	    $result = $wpdb->get_results($query);
	    $items = array();
	    
	    foreach($result as $value){
	    	$row = unserialize($value->value);
	    	if($row['status'] == 1)
	    		$input = "<input type='button' value='". __("Send Test Mail", GuiForm_Plugin::NAME) ."' class='button-primary test-mail-button'>";
	    	else
	    		$input = "<input type='button' value='". __("Send Verification Code", GuiForm_Plugin::NAME) ."' class='button-primary activation-key-button'>";
	    		
	    	$items[] =
				array(
					'id'          => $value->id,
					'name'        => $value->name,
					'test_button' => $input,
					'mail_name'   => $row['name'],
					'protocol'    => strtoupper($row['protocol'])
				);
	    }
	     
	     
	    $this->items = $items;
	    
	
		/* -- Register the pagination -- */
			$this->set_pagination_args( array(
				"total_items" => $totalitems,
				"total_pages" => $totalpages,
				"per_page"    => $perpage,
			) );
			//The pagination links are automatically built according to those parameters
		
			/* — Register the Columns — */
			$columns = $this->get_columns();
			$hidden = array();
			$sortable = $this->get_sortable_columns();
			$this->_column_headers = array($columns, $hidden, $sortable);
	}

}