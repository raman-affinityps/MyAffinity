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
 * Form management page.
 *
 * @category GuiForm
 * @package Render
 *
 * @since 1.0
 */
class GuiForm_Render_Forms extends WP_List_Table {
	const NAME = __CLASS__;
	var $_screen;
	var $_columns;
	var $_message = false;
	var $tab;
	var $navigation;

	/**
	* Constructor, we override the parent to pass our own arguments
	* We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
	*/
	function __construct($screen, $columns = array()) {
		
		$this->navigation = new GuiForm_Module_Navigation();
		$this->navigation->init(GuiForm_Render_Forms::NAME);
		
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
		
		$this->process_bulk_action();
		add_action( 'admin_enqueue_scripts', array( &$this, 'enqueueScripts' ));
	}
	
	public function enqueueScripts($hook_suffix){
		
		if(($hook_suffix == 'toplevel_page_'. GuiForm_Plugin::NAME && !isset($_GET['form'])) || $hook_suffix == GuiForm_Plugin::NAME .'_page_guiform_entry'){
			global $guiform;
			
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-widget');
			
			wp_enqueue_style('guifbox', $guiform->assets('css/guifbox.css'), false, GuiForm_Plugin::VERSION, 'all');
			wp_enqueue_style('guiform-style', $guiform->assets('css/style.css'), false, GuiForm_Plugin::VERSION, 'all');	
			
			
			wp_enqueue_script('guifbox', $guiform->assets('js/guifbox.js'), array('jquery'), GuiForm_Plugin::VERSION, true);
		 	wp_enqueue_script('guiform-script', $guiform->assets('js/script.js'), array('jquery'), GuiForm_Plugin::VERSION, true);
			wp_localize_script('guiform-script', 'guiform', array(
				'nonce'  => wp_create_nonce( "guiform_nonce" ),
				'assets' => $guiform->assets(),
				'type'   => 'forms',
			));
		}
	}
	
	public function show_message(){
		if($this->_message){
			
			switch( $this->current_action() ) {
				case 'delete-permanently' :
					echo '<div id="message" class="updated">
						        <p><strong>Delete form successful.</strong></p>
						    </div>';
					$this->_message = false;
				break;
			}
		}
	}
	
	public function toHTML(){
		if(isset($_REQUEST['form'])){
			$this->tab = (isset($_REQUEST['tab'])) ? $_REQUEST['tab'] : 'settings';
			$tabs = array('settings', 'confirmation', 'notification');
		?>
		
			<div id="guiform-manager" class="wrap form-<?php echo $this->tab; ?> metabox-holder">
				<?php $this->navigation->toHTML(); ?>
				<?php self::header($this->tab); ?>
				<?php (in_array($this->tab, $tabs)) ? $this->_toHTML() : do_action('guiform_'. $this->tab .'_form_settings_content'); ?>
			</div>
			
		<?php
		}
		else{
			$this->show_message();		
			echo '<div id="guiform-manager" class="wrap forms metabox-holder">';
			$this->navigation->toHTML();
			$this->prepare_items();
			echo "<form id='forms-filter' method='post' action='$this->admin_url'>";
			$this->search_box( 'search', 'search_id' );		
			$this->display();
			$this->get_column_info();
			echo '</form>';
			echo '</div>';
		}
	} 
	
	public function header(){
		
		$current = (isset($_REQUEST['tab'])) ? $_REQUEST['tab'] : 'settings';
		
		$tabs = array('settings'      => 'Settings',
							  	'confirmation'  => 'Confirmation',
									'notification'  => 'Notification'							
									); 
									
		if(has_filter('guiform_form_settings_tabs')) {
			$tabs = apply_filters('guiform_form_settings_tabs', $tabs);
		}
			
	  echo '<h2 class="nav-tab-wrapper">';
	  foreach( $tabs as $tab => $name ){
			$url = add_query_arg(
						  	array('action' => false, 
						  				'setup' => false, 
						  				'tab' => $tab, 
						  				'orderby' => false, 
						  				'order' => false,
						  				'id' => false
						  				)
						  );
						  
			$class = ( $tab == $current ) ? ' nav-tab-active' : '';
			echo "<a class='nav-tab$class' href='{$url}'>$name</a>";
	  }
	  echo '</h2>';
	}
	
	protected function _toHTML() {}

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

		echo '<tr id="'.$item->id.'"' . $row_class . '>';
		$this->single_row_columns( $item );
		echo '</tr>';
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
	public function row_actions( $actions, $always_visible = false ) {
		$action_count = count( $actions );
		$i = 0;

		if ( !$action_count )
			return '';


		$out = '<div class="' . ( $always_visible ? 'row-actions-visible' : 'row-actions' ) . '">';
		foreach ( $actions as $action => $link ) {
			++$i;
			( $i == $action_count ) ? $sep = '' : $sep = ' | ';
			$action = ($action == "trash") ? 'guif-action' : $action;
			$out .= "<span class='$action'>$link$sep</span>";
		}
		$out .= '</div>';

		return $out;
	}
	
	/**
	 * Get a list of sortable columns. The format is:
	 * 'internal-name' => 'orderby'
	 * or
	 * 'internal-name' => array( 'orderby', true )
	 *
	 * The second format will make the initial sorting order be descending
	 *
	 * @since 1.0
	 * @access protected
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return $sortable = array(
			'id'         => array('id', false ),
			'new'        => array('new', true),
			'unread'     => array('unread', true),
			'total'      => array('total', true),
			'form_title' => array('title', true),
			'last_update'=> array('last_update', false),
			'created'    => array('created', false )
		);
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
	   // parent::search_box( $text, $input_id );
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
	function get_columns() {
		return $columns = array(
			'cb' 			    => "<input type='checkbox' />",
			'id'          => __('Form ID'),
			'new'         => __('Today'),
			'unread'      => __('Unread'),
			'total'       => __('Total'),
			'form_title'  => __('Form Title'),
			'last_update' => __('Last Update'),
			'created'     => __('Date Created')
		);
	}
	
	function column_total( $item ){
		global $wpdb;
		$size = $wpdb->get_var("SELECT COUNT(id) FROM $wpdb->guiform_form$item->id");
		return "<div>". number_format($size) ."</div>";
	}
	
	function column_new( $item ){
		global $wpdb;
		$size = $wpdb->get_var("SELECT COUNT(id) FROM $wpdb->guiform_form$item->id WHERE guiform_date_submitted >= curdate()");
		return "<div style='font-weight: bold;'>". number_format($size) ."</div>";
	}
	
	function column_unread( $item ){
		global $wpdb;
		$size = $wpdb->get_var("SELECT COUNT(id) FROM $wpdb->guiform_form$item->id WHERE guiform_read = 0");
		return "<div style='font-weight: bold;'>". number_format($size) ."</div>";
	}
	
	function column_cb( $item ){
		return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item->id );
	}
	
	function column_status_icon( $item ){
		return "<span class='status-icon' title='status'></span>";
	}
	
	
	function column_form_title($item) {
		global $guiform;
		$entries_url = add_query_arg(array('page'=>'guiform-entry', 'form'=> $item->id));
		$preview_url = $guiform->permalink($item->id);
	  $actions = array(
		  					'edit'       => sprintf('<a href="?page=guiform-builder&form=%d">Edit</a>', $item->id),
		  					'settings'   => sprintf("<a href='?page=". GuiForm_Plugin::NAME ."&form=%d'>Settings</a>", $item->id),
		  					'duplicate'  => '<a class="duplicate" href="javascript:void(0)">Duplicate</a>',
		            'view'       => sprintf("<a target='_blank' href='%s'>Preview</a>", $preview_url),
		            'entries'    => sprintf("<a href='%s'>Entries</a>", $entries_url),
		            'delete'     => '<a class="delete" href="javascript:void(0)">Delete</a>'
		            );
	  
	  if($item->save_entry == false) unset($actions['entries']);	
	  $title = "<a style='display: block;' href='admin.php?page=guiform-builder&form=$item->id'>$item->title</a>";
	  return sprintf('%1$s %2$s', $title, $this->row_actions($actions) );
	}
	
	function column_created($item) {
	 return sprintf('%1$s', date("Y F d, g:i a", strtotime($item->created)));
	}
	
	function column_last_update($item) {
	 return sprintf('%1$s', date("Y F d, g:i a", strtotime($item->last_update)));
	}
	
	function process_bulk_action() {
		global $wpdb;
		
		if(!isset($_POST['action']) && count($_POST['wp_list_text_link']) == 0){
			return;
		}
		
		switch( $this->current_action() ) {
			case 'delete-permanently' :
			if(sizeof($_POST['wp_list_text_link']) > 0){
				foreach ($_POST['wp_list_text_link'] as $id ) {
					$table = $wpdb->guiform_form.$id;
					$wpdb->delete($wpdb->guiform, array('id' => $id), array('%d' ));
					$wpdb->delete($wpdb->guiform_options, array('name' => $id, 'type' => 'notification'), array('%s', '%s'));
					$wpdb->delete($wpdb->guiform_options, array('name' => $id, 'type' => 'confirmation'), array('%s', '%s'));
					$wpdb->delete($wpdb->guiform_options, array('name' => $id, 'type' => 'smtp'), array('%s', '%s'));
					$wpdb->query("DROP TABLE IF EXISTS $table");
					
					$tpl_path = GUIFORM_ABSPATH . DIRECTORY_SEPARATOR ."template";
			    $source = $tpl_path . DIRECTORY_SEPARATOR . $id;
					GuiForm_Render_Settings_General::deleteDirectory($source);
					
				}
				$this->_message = true;
			}
			break;
		}
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
	public function get_bulk_actions() {
	  $actions = array(
	    'delete-permanently'    => 'Delete'
	  );
	  return $actions;
	}
	
	/**
	 * Message to be displayed when there are no items
	 *
	 * @since 1.0
	 * @access public
	 */
	function no_items() {
	  _e( 'No form available.' );
	}
	
	function column_default( $item, $column_name ){
		return $item->$column_name;
	}
	
	/**
	 * Prepares the list of items for displaying.
	 * @uses WP_List_Table::set_pagination_args()
	 *
	 * @since 1.0
	 * @access public
	 * @abstract
	 */
	public function prepare_items() {
		global $wpdb, $_wp_column_headers;
				
		// get the current user ID
		$user = get_current_user_id();

		// get the current admin screen
		$screen = get_current_screen();

		// retrieve the "per_page" option
		$screen_option = $screen->get_option( 'per_page', 'option' );

		$perpage = get_user_meta( $user, $screen_option, true );
		
		// get the default value if none is set
		if (empty($perpage) || $perpage < 1 ){
			$perpage = $screen->get_option( 'per_page', 'default' );
		}
		
		
	// Get entries search terms
		$search = ( !empty( $_REQUEST['s'] ) ) ? trim( $_REQUEST['s'] ) : '';
	  
	  if(!empty($search)){
	  	$search_query = " WHERE title LIKE '%$search%' ";
	  }
		 
		/* -- Preparing your query -- */
		$query = "SELECT id, title, status, save_entry, last_update, created FROM $wpdb->guiform ". $search_query;
		
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
    
    $this->items = $wpdb->get_results($query);

	/* -- Register the pagination -- */
		$this->set_pagination_args( array(
			"total_items" => $totalitems,
			"total_pages" => $totalpages,
			"per_page" => $perpage,
		));
		//The pagination links are automatically built according to those parameters
	
		/* — Register the Columns — */
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);
	}

}