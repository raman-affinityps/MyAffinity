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
 * Entry management page.
 *
 * @category GuiForm
 * @package Render
 *
 * @since 1.0
 */
class GuiForm_Render_Entry extends WP_List_Table {
	const NAME = __CLASS__;
	var $_screen;
	var $_columns;
	var $_message = false;
  var $current_user;
  var $id;
  var $fields;
  var $table;
  var $navigation;
  
	/**
	* Constructor, we override the parent to pass our own arguments
	* We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
	*/
	function __construct($screen, $columns = array()) {
		global $wpdb;
		
		$this->current_user = get_current_user_id();
		$this->current_filter_action();
		$this->id = isset($_REQUEST['form']) ? esc_sql($_REQUEST['form']) : get_user_meta(get_current_user_id(), 'guiform_form_selected', true);
		$this->id = (!empty($this->id)) ? $this->id : 1;
		$this->fields = $this->_fields();
		$this->table = $wpdb->guiform_form.$this->id;
		
		$this->navigation = new GuiForm_Module_Navigation();
		$this->navigation->init(GuiForm_Render_Entry::NAME, $this->id);
		
		if ( is_string( $screen ) )	$screen = convert_to_screen( $screen );
			
		$screen->id =	$screen->id .'_'. $this->id;
		
		$this->_screen = $screen;
		
		
		if ( !empty( $columns ) ) {
			$this->_columns = $columns;
			add_filter( 'manage_' . $screen->id . '_columns', array( &$this, 'get_columns' ), 0 );
		}
		
		parent::__construct( array(
			'singular' => 'wp_list_text_link', //Singular label
			'plural' => 'wp_list_test_links', //plural label, also this well be one of the table css class
			'ajax'	=> false //We won't support Ajax for this table
			) 
		);
		
		$this->process_bulk_action();
		
		add_action('admin_enqueue_scripts', array(&$this, 'scripts'));
	}
	
	function scripts($hook_suffix){
		
		if($hook_suffix == 'toplevel_page_'. GuiForm_Plugin::NAME || $hook_suffix == GuiForm_Plugin::NAME .'_page_guiform-entry' && !isset($_REQUEST['entry'])){
			global $guiform;
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-widget');
			
			wp_enqueue_style('guiform-style', $guiform->assets('css/style.css'), false, GuiForm_Plugin::VERSION, 'all');
			wp_enqueue_style('guifbox', $guiform->assets('css/guifbox.css'), false, GuiForm_Plugin::VERSION, 'all');
			wp_enqueue_style('lightbox', $guiform->assets('css/lightbox.css'), false, GuiForm_Plugin::VERSION, 'all');
					
			wp_enqueue_script('guifbox', $guiform->assets('js/guifbox.js'), array('jquery'), GuiForm_Plugin::VERSION, true);
		 	wp_enqueue_script('lightbox', $guiform->assets('js/lightbox.js'), false, GuiForm_Plugin::VERSION, true);
		 	
		 	wp_enqueue_script('guiform-script', $guiform->assets('js/script.js'), false, GuiForm_Plugin::VERSION, true);
			wp_localize_script('guiform-script', 'guiform', array(
			  'nonce'  => wp_create_nonce( "guiform_nonce" ),
				'assets' => $guiform->assets(),
				'form'   => $this->id,
				'type'   => 'entry'
			));
		}
	}
	
	function _fields(){
		global $guiform;
		
		$entry = $guiform->form($this->id, 'entry');
		
		$fields = array();
	  
	  if(!empty($entry)){
			foreach($entry as $key => $value){
				if($value[1] == "true") $fields[$key] = $value[0];
			}
		}
		
		return $fields ;
	}
	
	function show_message(){
		if($this->_message){
			switch( $this->current_action() ) {
				case 'delete' :
					echo '<div id="message" class="updated">
						        <p><strong>'. __('Delete entries successful.', GuiForm_Plugin::NAME) .'</strong></p>
						    </div>';
					$this->_message = false;
				break;
			}
			
		}
	}
	
	public function toHTML(){
		global $guiform;
		
		self::show_message();
		$preview_url = $guiform->permalink($this->id, '', false);
		echo '<div id="guiform-manager" class="wrap entry-manager metabox-holder">';
		$this->navigation->toHTML();
		echo '<div class="wp-list-table">';
		self::views();
		self::prepare_items();
		echo "<form id='forms-filter' method='post' action='". admin_url('admin.php?page=guiform-entry') ."'>";
		self::search_box( 'search', 'search_id' );		
		self::display();
		self::get_column_info();
		echo '</form></div></div>';
		
	} 

	
	/**
	 * Get a list of all, hidden and sortable columns, with filter applied
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @return array
	 */
	public function get_column_info() {
		$columns = get_column_headers( $this->_screen );
		$hidden = get_hidden_columns( $this->_screen );
		$sortable = $this->get_sortable_columns();
	
		return array( $columns, $hidden, $sortable );
	}
	
	function date_filter() {
		global $wpdb, $wp_locale;

	  	
		$status = ( !empty($_REQUEST['entry_status']) ? $_REQUEST['entry_status'] : 'all');
		
		if($status == 'all') $status = 'guiform_status = 1 ';
		if($status == 'trash') $status = 'guiform_status = 0 ';
		if($status == 'spam') $status = 'guiform_status = 2 ';
		if($status == 'unread') $status = 'guiform_read = 0 ';
			
		$date = $wpdb->get_results( "
			SELECT DISTINCT YEAR(guiform_date_submitted) AS year, MONTH(guiform_date_submitted ) AS month
			FROM $this->table
			WHERE $status
			ORDER BY guiform_date_submitted DESC
		");

		$size = count($date);
		$output = '';
		if ($size > 0 || !empty($size)){
			 
			$output .= "<select name='date'>
				<option value='0'>Show all dates</option>";		
	
			foreach ( $date as $row ) {
				$month = zeroise( $row->month, 2 );
				$year = $row->year;
				$output .= "<option ". selected($this->date_selected(), $year.$month, false ) ." value='". $row->year . $month. "'>". $year .' '. $wp_locale->get_month( $month ) ."</option>";		
			}
	
			$output .= "</select>";
	
			
			print $output;
		}

	}
	
	/**
	 * Extra controls to be displayed between bulk actions and pagination
	 *
	 * @since 1.0
	 * @access public
	 */
 	public function extra_tablenav( $which ) {
		if ( $which == "top" ){
			global $wpdb;
			
			$cols = $wpdb->get_results( "SELECT id, title FROM $wpdb->guiform WHERE save_entry = 1 ORDER BY id ASC" );
	
			if ( 'top' == $which ) {
				echo '<div class="alignleft actions">';
				$this->date_filter();
				echo '<select id="form" name="form">';
					
				foreach ( $cols as $form ) {
					echo '<option value="' . $form->id . '"' . selected( $this->form_selected(), $form->id ) . '>' . $form->id .' : '. $form->title . '</option>';
				}
	
				echo '</select>
					<input type="submit" value="' . __( 'Filter' , GuiForm_Plugin::NAME) . '" class="button-secondary" />
					</div>';
			}
		}
		if ( $which == "bottom" ){
		}
	}
	
	function current_filter_action() {
		
		if($_REQUEST['entry_status'] != get_user_meta($this->current_user, 'guiform_entry_status', true)){
			update_user_meta($this->current_user, 'guiform_date_selected', 0);
			update_user_meta($this->current_user, 'guiform_entry_status', $_REQUEST['entry_status']);
		}
		
		if(isset($_REQUEST['date'])){
			update_user_meta($this->current_user, 'guiform_date_selected', $_REQUEST['date']);
		}
		
		if(isset($_REQUEST['form']) && $_REQUEST['form'] != $this->form_selected()){
			update_user_meta($this->current_user, 'guiform_date_selected', 0);
		}
		
		if(isset($_REQUEST['form'])){
			update_user_meta($this->current_user, 'guiform_form_selected', $_REQUEST['form']);
		}
	}
	
	function form_selected(){
		return get_user_meta($this->current_user, 'guiform_form_selected', true);
	}
	
	function date_selected(){
		return get_user_meta($this->current_user, 'guiform_date_selected', true);
	}
	
	/**
	* Decide which columns to activate the sorting functionality on
	* @return array $sortable, the array of columns that can be sorted by the user
	*/
	public function get_sortable_columns() {
		foreach($this->fields as $key => $value){
			$data[$key] = array($key, false);
		}
		return $data;
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
	public function search_box( $text, $input_id ) {
	    //parent::search_box( $text, $input_id );
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
	public function get_columns() {
		$screen = get_current_screen();
		$data['cb'] = '<input type="checkbox" />';
		
		foreach($this->fields as $key => $value){
			$data[$key] = $value;
		}
	
		if($screen->id == GuiForm_Plugin::NAME .'_page_guiform-entry_'. $this->id  &&  !isset($_REQUEST['entry'])) return $data;
	}
	
	
	function column_cb( $item ){
		return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item->id );
	}
	
	function column_guiform_ip($item) {
		$form_id = $this->form_selected();
		$view = add_query_arg(array('page' => 'guiform-entry', 'form' => $form_id, 'entry' => $item->id));
	  $actions = array(
	            'view'    => sprintf("<a href='%s'>View</a>", $view),
	            'restore' => sprintf('<a data-action="restore-entry" class="quick-action" data-id="%d" href="javascript:void(0)">Restore</a>', $item->id),
	            'trash'   => sprintf('<a data-action="trash-entry" class="quick-action" data-id="%d" href="javascript:void(0)">Trash</a>', $item->id),
	            'delete'  => sprintf('<a data-action="delete-entry" class="quick-action" data-id="%d" href="javascript:void(0)">Delete</a>', $item->id)
	            );
	  
	  if($_REQUEST["entry_status"] == 'trash') unset($actions["trash"]);
	  if(!isset($_REQUEST["entry_status"]) || $_REQUEST["entry_status"] == 'unread') unset($actions["restore"]);
	  
	  $title = "<a style='display: block;' href='$view'>$item->guiform_ip</a>";
	  return sprintf('%1$s %2$s', $title, $this->row_actions($actions) );
	}
	
	/**
	 * Generate row actions div
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @param array $actions The list of actions
	 * @param bool $always_visible Whether the actions should be always visible
	 * @return string
	 */
	function row_actions( $actions, $always_visible = false ) {
		$action_count = count( $actions );
		$i = 0;

		if ( !$action_count )
			return '';

		$out = '<div class="' . ( $always_visible ? 'row-actions-visible' : 'row-actions' ) . '">';
		foreach ( $actions as $action => $link ) {
			++$i;
			( $i == $action_count ) ? $sep = '' : $sep = ' | ';
			$out .= "<span class='$action'>$link$sep</span>";
		}
		$out .= '</div>';

		return $out;
	}
	
	function column_guiform_date_submitted($item) {
	 return sprintf('%1$s', date("Y F d, g:i a", strtotime($item->guiform_date_submitted)));
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
		
		$entry_status = ( !empty($_REQUEST['entry_status']) ? $_REQUEST['entry_status'] : 'all');
		
		if($entry_status == 'all'){
			$actions = array(
		    'spam'    => 'Mark as Spam',
		    'unread'  => 'Mark as Unread',
		    'read'    => 'Mark as Read',
		    'trash'   => 'Move to Trash',
		    'delete-permanently'  => 'Delete Permanently',
		  );
		}
		
		if($entry_status == 'unread'){
			$actions = array(
			  'read'    => 'Mark as Read',
		    'spam'    => 'Mark as Spam',
		    'trash'   => 'Move to Trash',
		    'delete-permanently'  => 'Delete Permanently'
		  );
		}
		
		if($entry_status == 'trash'){
			$actions = array(
		    'restore' => 'Restore',
		    'delete-permanently'  => 'Delete Permanently'
		  );
		}
		
		if($entry_status == 'spam'){
			$actions = array(
				'restore' => 'Restore',
		    'trash'   => 'Move to Trash',
		    'delete-permanently'  => 'Delete Permanently',
		  );
		}
		
	  return $actions;
	}
	
	function process_bulk_action() {
		global $wpdb;
		
		if(sizeof($_POST['wp_list_text_link']) == 0)
			return;
		
		switch( $this->current_action() ) {
			case 'delete-permanently' :
				foreach ($_POST['wp_list_text_link'] as $id ) {
					$wpdb->delete($this->table, array('id' => $id), array('%d'));
				}
			break;
			
			case 'trash' :
				foreach ($_POST['wp_list_text_link'] as $id ) {
					$wpdb->update($this->table, array('guiform_status' => 0), array('id' => $id), array('%d'), array('%d'));
				}
				
				$this->_message = true;
			break;
			
			case 'unread' :
				foreach ($_POST['wp_list_text_link'] as $id ) {
					$wpdb->update($this->table, array('guiform_read' => 0), array('id' => $id), array('%d'), array('%d'));
				}
			break;
			
			case 'read' :
				foreach ($_POST['wp_list_text_link'] as $id ) {
					$wpdb->update($this->table, array('guiform_read' => 1), array('id' => $id), array('%d'), array('%d'));
				}
				
				$this->_message = true;
			break;
			
			case 'restore' :
				foreach ($_POST['wp_list_text_link'] as $id ) {
					$wpdb->update($this->table, array('guiform_status' => 1), array('id' => $id), array('%d'), array('%d'));
				}
			break;
			
			case 'spam' :
				foreach ($_POST['wp_list_text_link'] as $id ) {
					$wpdb->update($this->table, array('guiform_status' => 2), array('id' => $id), array('%d'), array('%d'));
				}
			break;
		}
		
		$this->_message = true;
	}
	
	/**
	 * Message to be displayed when there are no items
	 *
	 * @since 1.0
	 * @access public
	 */
	function no_items() {
	  _e( 'No entry available.' );
	}
	
	function column_default( $item, $column_name ){
	
		return $item->$column_name;
	}
	
	/**
	 * Get an associative array ( id => link ) with the list
	 * of views available on this table.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @return array
	 */
	function get_views() {
		
		global $wpdb;
		
		$views = array();
		$current = (!empty($_REQUEST['entry_status']) ? $_REQUEST['entry_status'] : 'all');
		
		//All link
		$all_size = $wpdb->get_var("SELECT COUNT(*) FROM $this->table WHERE guiform_status = 1");
		$all_size = (!empty($all_size) ? $all_size : '0');
		$class = ($current == 'all' ? ' class="current"' :'');
		$all_url = remove_query_arg(array('entry_status', 'order', 'orderby'));
		$views['all'] = "<a href='{$all_url}' {$class} >All <span class='count'>(<span class='pending-count'>$all_size</span>)</span></a>";
		
		//Unread link
		$unread_size = $wpdb->get_var("SELECT COUNT(*) FROM $this->table WHERE guiform_read = 0 AND guiform_status = 1");
		$unread_size = (!empty($unread_size) ? $unread_size : '0');
		$class = ($current == 'unread' ? ' class="current"' :'');
		$unread_url = add_query_arg('entry_status','unread');
		$views['unread'] = "<a href='{$unread_url}' {$class} >Unread <span class='count'>(<span class='pending-count'>$unread_size</span>)</span></a>";
		
		
		//Spam link
		$spam_size = $wpdb->get_var("SELECT COUNT(*) FROM $this->table WHERE guiform_status = 2");
		$spam_size = (!empty($spam_size) ? $spam_size : '0');
		$spam_url = add_query_arg('entry_status','spam');
		$class = ($current == 'spam' ? ' class="current"' :'');
		$views['spam'] = "<a href='{$spam_url}' {$class} >Spam <span class='count'>(<span class='pending-count'>$spam_size</span>)</span></a>";
		
		//Trash link
		$trash_size = $wpdb->get_var("SELECT COUNT(*) FROM $this->table WHERE guiform_status = 0");
		$trash_size = (!empty($trash_size) ? $trash_size : '0');
		$trash_url = add_query_arg('entry_status','trash');
		$class = ($current == 'trash' ? ' class="current"' :'');
		$views['trash'] = "<a href='{$trash_url}' {$class} >Trash <span class='count'>(<span class='pending-count'>$trash_size</span>)</span></a>";
		
		return $views;
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
		
	
		if($wpdb->query("DESCRIBE $this->table")){
			
			
		
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
			
			
		// Get entries search terms
			$search = ( !empty( $_REQUEST['s'] ) ) ? trim( $_REQUEST['s'] ) : '';
			$status = ( !empty($_REQUEST['entry_status']) ? $_REQUEST['entry_status'] : 'all');
		
			if($status == 'all') $status = ' guiform_status = 1 ';
			if($status == 'trash') $status = ' guiform_status = 0 ';
			if($status == 'spam') $status = ' guiform_status = 2 ';
		  if($status == 'unread') $status = ' guiform_read = 0 AND guiform_status = 1 ';
		  
		  if(!empty($search) || !empty($status)){
		  	$WHERE = " WHERE ";
		  }
		  
		  if(!empty($search)){
		  	$WHERE .= " title LIKE '%$search%' ";
		  }
		  
		  if(!empty($search) && !empty($status)){
		  	$WHERE .= " AND ";
		  }
		  
		  if(!empty($status)){
		  	$WHERE .= $status;
		  }
		  
		  $date =  $this->date_selected();
		  
		 
			// Parse month/year and build the clause
			if ( $date > 0) {
				$year = substr( $date, 0, 4 );
				$month = substr( $date, -2 );
			
				$WHERE .= " AND YEAR(guiform_date_submitted) = $year AND MONTH(guiform_date_submitted) = $month ";
			}
		  
			$field = implode(', ', array_keys($this->fields));
			
			/* -- Preparing your query -- */
			$query = "SELECT $field, guiform_read FROM $this->table ". $WHERE;
			
			/* -- Ordering parameters -- */
			//Parameters that are going to be used to order the result
			$orderby = !empty($_GET["orderby"]) ? esc_sql($_GET["orderby"]) : 'ASC';
			$order = !empty($_GET["order"]) ? esc_sql($_GET["order"]) : '';
			if(!empty($orderby) & !empty($order)){ 
				$query.=' ORDER BY '.$orderby.' '.$order; 
			}
			else{
				$query.=' ORDER BY id DESC';
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
	    
	    $items = array();
	    
	    $rows = $wpdb->get_results($query);
	    $labels = GuiForm_Render_View::arrayColumn($guiform->form($this->id, 'data'), array('label', 'type'), 'name');
	    
	    foreach($rows as $row){
	    	$column = array();
	    	foreach($row as $key => $value){
	    		
	    		$key = str_replace("[]", '', $key);
	    		$output = '';
	    		$value = stripslashes_deep($value);
	    		
	    		if($guiform->isSerial($value)){
	    			
	    			$value = unserialize($value);
	    			
	    			$output = '<ul class="post-image">';
		    		
		    		foreach($value as $file => $row){
		    			
							if($labels[$key][1] == "f_file"){
								$index = key($row);
								$file = $guiform->uploadFolder($index, $row[$index]['path'], true);
								$output .= "<li><a title='". $row[$index]['name'] ."' href='$file'>". $row[$index]['name'] ."</a></li>";
							}
							else{
								$output .= "<li>$row</li>";
							}
						}
		    		
		    		$output .= '</ul>';
		    	}
		    	else{
		    		if($labels[$key][1] == 'f_textarea'){
		    			$output = (!empty($value)) ? "<textarea readonly='readonly' style='width: 100%; height: 150px; resize: vertical;'>$value</textarea>" : '';
		    		}
		    		else{
		    			$output = $value;
		    		}
		    	}
	    		
	    		$column[$key] = $output;
	    	}
	    	
	    	$items[] = (Object)$column;
	    
	    }
	    	
	    	

	    $this->items = $items;
	
		/* -- Register the pagination -- */
			$this->set_pagination_args( array(
				"total_items" => $totalitems,
				"total_pages" => $totalpages,
				"per_page" => $perpage,
			) );
			//The pagination links are automatically built according to those parameters
		
			/* — Register the Columns — */
			$columns = $this->get_columns();
			$hidden = array();
			$sortable = $this->get_sortable_columns();
			$this->_column_headers = array($columns, $hidden, $sortable);
		}
	}

	/**
	 * Display the pagination.
	 *
	 * @since 1.0
	 * @access public
	 */
	function pagination( $which ) {
		if ( empty( $this->_pagination_args ) )
			return;

		extract( $this->_pagination_args, EXTR_SKIP );

		$output = '<span class="displaying-num">' . sprintf( _n( '1 entry', '%s entries', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

		$current = $this->get_pagenum();

		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );

		$current_url = remove_query_arg( array( 'hotkeys_highlight_last', 'hotkeys_highlight_first' ), $current_url );

		$page_links = array();

		$disable_first = $disable_last = '';
		if ( $current == 1 )
			$disable_first = ' disabled';
		if ( $current == $total_pages )
			$disable_last = ' disabled';

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'first-page' . $disable_first,
			esc_attr__( 'Go to the first page' ),
			esc_url( remove_query_arg( 'paged', $current_url ) ),
			'&laquo;'
		);

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'prev-page' . $disable_first,
			esc_attr__( 'Go to the previous page' ),
			esc_url( add_query_arg( 'paged', max( 1, $current-1 ), $current_url ) ),
			'&lsaquo;'
		);

		if ( 'bottom' == $which )
			$html_current_page = $current;
		else
			$html_current_page = sprintf( "<input class='current-page' title='%s' type='text' name='paged' value='%s' size='%d' />",
				esc_attr__( 'Current page' ),
				$current,
				strlen( $total_pages )
			);

		$html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
		$page_links[] = '<span class="paging-input">' . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . '</span>';

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'next-page' . $disable_last,
			esc_attr__( 'Go to the next page' ),
			esc_url( add_query_arg( 'paged', min( $total_pages, $current+1 ), $current_url ) ),
			'&rsaquo;'
		);

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'last-page' . $disable_last,
			esc_attr__( 'Go to the last page' ),
			esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ),
			'&raquo;'
		);

		$pagination_links_class = 'pagination-links';
		if ( ! empty( $infinite_scroll ) )
			$pagination_links_class = ' hide-if-js';
		$output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

		if ( $total_pages )
			$page_class = $total_pages < 2 ? ' one-page' : '';
		else
			$page_class = ' no-pages';

		$this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

		echo $this->_pagination;
	}
	
	/**
	 * Generates content for a single row of the table
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @param object $item The current item
	 */
	function single_row($item) {
		static $row_class = '';
		$class = array();
		
		if($row_class == ''){
			$class[] = 'alternate';
			$class[] = 'row';
			$row_class = 'alternate';
		}
		else{
			$row_class = '';
			$class[] = 'row';
		}
		
		if($item->guiform_read == 0){
			$class[] = 'unread';
		}
		
		$class = implode(' ', $class);

    echo '<tr id="'. $item->id .'" class="'. $class .'">';
		echo $this->single_row_columns($item);
		echo '</tr>';
	}
}