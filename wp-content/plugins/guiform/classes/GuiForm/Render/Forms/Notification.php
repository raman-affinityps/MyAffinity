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
 * Form notification settigns management.
 *
 * @category GuiForm
 * @package Render
 * @subpackage Form
 *
 * @since 1.0
 */
class GuiForm_Render_Forms_Notification extends WP_List_Table {
	
	const NAME = __CLASS__;
	
	var $form;
	
	var $id;
	
	var $_screen;
	
	var $_columns;
	
	var $_message = false;
	
	var $module;
	
	var $errors = array();
	
	var $var = array();
	
	var $navigation;
	
	/**
	* Constructor, we override the parent to pass our own arguments
	* We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
	*/
	function __construct($screen) {
		
	 	if ( is_string( $screen ) ) $screen = convert_to_screen( $screen );
	 
		$this->form = esc_sql($_REQUEST['form']);
		$this->id = esc_sql($_REQUEST['id']);
		
		$this->navigation = new GuiForm_Module_Navigation();
		$this->navigation->init(GuiForm_Render_Forms_Notification::NAME, $this->form);
		
		$this->_screen = $screen;
		
		if ( !empty( $columns ) ) {
			$this->_columns = $columns;
			add_filter( 'manage_' . $this->_screen->id . '_columns', array( &$this, 'get_columns' ), 0 );
		}
		
		parent::__construct( array(
			'singular'=> 'wp_list_text_link', //Singular label
			'plural' => 'wp_list_test_links', //plural label, also this well be one of the table css class
			'ajax'	=> false //We won't support Ajax for this table
			) 
		);
		
		add_action( 'admin_enqueue_scripts', array( &$this, 'scripts' ));
		add_filter( 'mce_buttons_2', array( &$this, 'wpex_mce_buttons' ) );
		add_filter( 'tiny_mce_before_init', array( &$this, 'mytheme_tinymce_settings' ));
	}
	
	public function scripts($hook_suffix){
		
		if($hook_suffix == 'toplevel_page_'. GuiForm_Plugin::NAME && $_REQUEST['tab'] == 'notification'){
			global $guiform;
			
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-widget');
			wp_enqueue_script('jquery-effects-core');
			
			wp_enqueue_style('bootstrap', $guiform->vendor('bootstrap/css/bootstrap.min.css'), false, GuiForm_Plugin::VERSION, 'all');
			wp_enqueue_style('guiform-guifbox', $guiform->assets('css/guifbox.css'), false, GuiForm_Plugin::VERSION, 'all');
			wp_enqueue_style('guiform-switchbutton', $guiform->assets('css/jquery.switchButton.css'), false, GuiForm_Plugin::VERSION, 'all');
			wp_enqueue_style('guiform-style', $guiform->assets('css/style.css'), false, GuiForm_Plugin::VERSION, 'all');
			
			wp_enqueue_script('guiform-guifbox', $guiform->assets('js/guifbox.js'), array('jquery'), GuiForm_Plugin::VERSION, true);
			wp_enqueue_script('bootstrap', $guiform->vendor('bootstrap/js/bootstrap.min.js'), GuiForm_Plugin::VERSION, true);
			wp_enqueue_script('guiform-caret', $guiform->assets('js/jquery.caret.min.js'), false, GuiForm_Plugin::VERSION, 'all');
		 	wp_enqueue_script('guiform-switchbutton', $guiform->assets('js/jquery.switchButton.js'), false, GuiForm_Plugin::VERSION, 'all');
			wp_enqueue_script('guiform-script', $guiform->assets('js/script.js'), false, GuiForm_Plugin::VERSION, true);
			wp_localize_script('guiform-script', 'guiform', array(
			  'nonce'  => wp_create_nonce( "guiform_nonce" ),
				'assets' => $guiform->assets(),
				'type'   => 'notification',
				'caret'  => false,
				'form'   => $_REQUEST['form']
			));
		}
	}
	
	public function wpex_mce_buttons( $buttons ) {
		array_unshift( $buttons, 'fontselect' ); // Add Font Select
		array_unshift( $buttons, 'fontsizeselect' ); // Add Font Size Select
		array_unshift( $buttons, 'preview' ); // Add Font Size Select
		return $buttons;
	}
	
	public function mytheme_tinymce_settings($settings) {
		unset($settings['content_css']);
		return $settings;
	}
	
	function wpse24113_tiny_mce_before_init( $initArray ){
	    $initArray['setup'] = "(function(ed){
	    	ed.onClick.add(function(ed, e) {
	    		guiform.caret = 'tinymce';
		    });
	    })";
	    return $initArray;
	}
	
	/**
	 * The Editor is really a filter, but happens to be our most convenient hook to set everything up
	 *
	 * @param string $editor
	 * @return string Editor fields
	 */
	public function theEditor( $editor ) {
		global $tinymce_version, $guiform;

		if ( version_compare( $tinymce_version, '400', '<' ) ) {
			add_filter( 'mce_external_plugins', array( &$this, 'mcePlugins_3_8' ) );
			add_filter( 'mce_buttons_3', array( &$this, 'mceButtons_3_8' ) );
			wp_register_style( 'mce-table-buttons', $guiform->vendor('tinymce3-assets/mce-table-buttons.css' ));
			wp_print_styles( 'mce-table-buttons' );
		} else {
			add_filter( 'mce_external_plugins', array( &$this, 'mcePlugins_3_9' ) );
			add_filter( 'mce_buttons_2', array( &$this, 'mceButtons_3_9' ) );
		}

		remove_filter( 'theEditor', array( &$this, 'the_editor' ) ); // only needs to run once

		return $editor;
	}

	/**
	 * Initialize TinyMCE 3.x table plugin and custom TinyMCE plugin for third editor row
	 *
	 * @param array $plugin_array Array of TinyMCE plugins
	 * @return array Array of TinyMCE plugins
	 */
	public function mcePlugins_3_8( $plugin_array ) {
		global $guiform;
		$plugin_array['table'] = $guiform->vendor('tinymce3-table/editor_plugin.js');
		$plugin_array['mcetablebuttons'] = $guiform->vendor('tinymce3-assets/mce-table-buttons.js');
		return $plugin_array;
	}

	/**
	 * Add TinyMCE 3.x table control buttons to a third row of editor buttons
	 *
	 * @param array $buttons Buttons for the third row
	 * @return array Buttons for the third row
	 */
	public function mceButtons_3_8( $buttons ) {
		array_push( $buttons, 'tablecontrols' );
		return $buttons;
	}

	/**
	 * Initialize TinyMCE 4.x table plugin
	 *
	 * @param array $plugin_array Array of TinyMCE plugins
	 * @return array Array of TinyMCE plugins
	 */
	public function mcePlugins_3_9( $plugin_array ) {
		global $guiform;
		$variant = ( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ) ? '' : '.min';
		$plugin_array['table'] = $guiform->vendor('tinymce4-table/plugin' . $variant . '.js');
   	return $plugin_array;
	}

	/**
	 * Add TinyMCE 4.x table control to the second row, after other formatting controls
	 *
	 * @param array $buttons Buttons for the second row
	 * @return array Buttons for the second row
	 */
	public function mceButtons_3_9( $buttons ) {
		if ( ! $pos = array_search( 'undo', $buttons ) ) {
			array_push( $buttons, 'table' );
			return $buttons;
		}

		return array_merge( array_slice( $buttons, 0, $pos ), array( 'table' ), array_slice( $buttons, $pos ) );
	}

	public function preSave( $content ) {
		if ( substr( $content, -8 ) == '</table>' )
			$content .= "\n<br />";
		return $content;
	}

	public function toHTML() {
		?>
		<div id="guiform-manager" class="wrap form-notification guiform metabox-holder">
			<?php $this->navigation->toHTML(); ?>
			<?php GuiForm_Render_Forms::header(); ?>
			<?php ($_REQUEST['action'] == "add") ? self::_add() : self::_toHTML(); ?>
		</div>
		<?php
	}
	
	private function _selectEntry(){
  	global $guiform;
  	
		$fields = $guiform->form($this->form, 'entry');
		
		$html = '<li data-key="id"><a href="javascript:void(0);">ID</a></li>
						 <li data-key="guiform_ip"><a href="javascript:void(0);">IP Address</a></li>
						 <li data-key="guiform_browser"><a href="javascript:void(0);">Browser</a></li>
						 <li data-key="guiform_os"><a href="javascript:void(0);">Operating System</a></li>
				     <li class="divider"></li>';
		
		foreach($fields as $key => $value){
			$key = str_ireplace("[]", "", $key);
			if(substr($key, 0, strlen("guiform_")) != "guiform_" && $key != "id" && $key != "recaptcha_response_field" && $key != "g-recaptcha-response-clone"){
				$name = ' {'.$key.'}';
				$html .= "<li data-key='".$key."'><a href='javascript:void(0);'>".$value[0] . $name ."</a></li>";
			}
		}
		
  	return $html;
  }
	
	private function _save(){
		global $guiform, $wpdb;
		
		unset($_POST['submit']);
		
		$this->var = $_POST;
		
		unset($this->var['id']);
		
		$this->var['message'] = stripslashes($_POST['message']);
		
		if(empty($this->var['from'])){
			$this->errors['from'] =  __('<strong>From:</strong> Field is empty.');
		}
		
		if(empty($this->var['subject'])){
			$this->errors['subject'] =  __('<strong>Subject:</strong> Field is empty.');
		}
		
		if(empty($this->var['to'])){
			$this->errors['to'] =  __('<strong>To:</strong> Field is empty.');
		}
		else{
			self::_validateEmail($this->var['to'], 'to');
		}
		
		self::_validateEmail($this->var['cc'], 'cc');
		self::_validateEmail($this->var['bcc'], 'bcc');
		self::_validateEmail($this->var['reply-to'], 'reply-to');
		
		if(sizeof($this->errors)){
			self::_printError();
		}
		else{
			if($_POST['id'] > 0){
				$guiform->updateOption($this->form, $this->var, 'notification', $this->id);
				$this->var['id'] = trim($_POST['id']);
			}
			else{
				$this->var['status'] = 1;
				$guiform->addOption($this->form, $this->var, 'notification');
				$this->var['id'] = $wpdb->insert_id;
			}
			
			echo '<div class="updated"><p><strong>Save succesful.</strong></p></div>';
		}
	}	
	
	private function _validateEmail($emails, $type){
		
		if(empty($emails)) return;
		
		$emails = explode(',', $emails);
		$emails = array_map('trim', $emails);
		$emails = array_map('strtolower', $emails);
		$emails = array_map('esc_html', $emails);
		foreach($emails as $email){
			if(!preg_match('/^[{]{1}+([a-z0-9_])+[}]{1}$/', $email) && is_email($email) == false){
				$this->errors[$type] = __('<strong>'. ucfirst($type) .':</strong> Contain invalid email address or short code.');
			}
		}
	}
	
	private function _printError(){
		echo "<div class='error'>";
		foreach($this->errors as $error){
			echo '<p>'. $error .'</p>';
		}
		echo "</div>";
	}
	
	private function _add(){
		global $tinymce_version, $guiform;
	
		if(isset($_POST['submit'])){
			self::_save();
		}
		else if(isset($_GET['id'])){
			$this->var = $guiform->getOption($this->id, $_GET['id'])->value;
		}
		
		

		//print_r($this->var["rules"]);
		add_filter( 'the_editor', array( &$this, 'theEditor' ) ); 
		add_action( 'content_save_pre', array( &$this, 'preSave'), 100 );
		add_filter( 'tiny_mce_before_init', array( &$this, 'wpse24113_tiny_mce_before_init' ));
		?>
		
			<form method="POST">
				<input type="hidden" value="<?php echo isset($_GET['id']) ? $_GET['id'] : $this->var['id']; ?>" name="id">
				<input type="hidden" value="<?php echo empty($this->var['status']) ? 0 : $this->var['status']; ?>" name="status">
				<table class="table-settings" width="100%" border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td rowspan="10" style="width: 200px; vertical-align: top; background: none repeat scroll 0 0 #F1F1F1;">
							<?php echo self::_displayEntry(); ?>
						</td>
					</tr>	
					<tr>
						<td class="border-left column-right" colspan="1">From</td>
						<td class="column-left" colspan="2">
						  <select name="from" class="form-control <?php if(!empty($this->errors['from'])) echo 'error'; ?>">
								<option value=""></option>
								<?php
									if(sizeof($guiform->getOptions('mail'))){
										foreach($guiform->getOptions('mail') as $mail){
											$option = unserialize($mail->value);
											if($option['status']){
												echo "<option ". selected($mail->name, $this->var['from']) ." value='$mail->name'>$mail->name</option>";
											}
										}
									}
									
									foreach($guiform->form($this->form, 'data') as $mail){
										if($mail['type'] == 'f_email' || $mail['validation']['format']['type'] == 'emailAddress'){
											$email = '{'.$mail['name'].'}';
											echo "<option ". selected($email, $this->var['from']) ." value='".$email."'>".$mail['label']."</option>";
										}
									}
								?>
							</select>
						</td>
						<td class="column-attachment" rowspan="4">
							<div id="attachment-item" class="panel panel-info">
					      <div class="panel-heading">
					        <h3 class="panel-title">File Attachment</h3>
					      </div>
					      <div class="panel-body" style="text-align: center;">
					        <span style="color: rgb(213, 78, 33); font-size: 40px;" class="fa fa-lock"></span>
									<h3>Unlock this with <a href="">GuiForm Pro</a>.</h3>
					      </div>
					    </div>	
						</td>
					</tr>
					<tr>
						<td class="border-left column-right">To</td>
						<td class="column-left" colspan="2">
							<input class="form-control <?php if(!empty($this->errors['to'])) echo 'error'; ?>" type="text" value="<?php echo $this->var['to']; ?>" name="to">
						</td>
					</tr>
					<tr class="<?php if(empty($this->var['cc'])) echo 'hide'; ?>"> 
						<td class="border-left col-remove">
							<a id="button-cc" class="btn btn-danger remove" role="button" href="javascript:void(0);"> <span class="fa fa-minus"></span></span></a>
						</td>
						<td class="column-right" colspan="1">Cc</td>
						<td class="column-left">
							<input class="form-control <?php if(!empty($this->errors['cc'])) echo 'error'; ?>" type="text" value="<?php echo $this->var['cc']; ?>" name="cc">
						</td>
					</tr>
					<tr class="<?php if(empty($this->var['bcc'])) echo 'hide'; ?>"> 
						<td class="border-left col-remove">
							<a id="button-bcc" class="btn btn-danger remove" role="button" href="javascript:void(0);"> <span class="fa fa-minus"></span></span></a>
						</td>
						<td class="column-right" colspan="1">Bcc</td>
						<td class="column-left">
							<input class="form-control <?php if(!empty($this->errors['bcc'])) echo 'error'; ?>" type="text" value="<?php echo $this->var['bcc']; ?>" name="bcc">
						</td>
					</tr>
					<tr class="<?php if(empty($this->var['reply-to'])) echo 'hide'; ?>"> 
						<td class="border-left col-remove">
							<a id="button-replyto" class="btn btn-danger remove" role="button" href="javascript:void(0);"> <span class="fa fa-minus"></span></span></a>
						</td>
						<td class="column-right" colspan="1">Reply-To</td>
						<td class="column-left">
							<input class="form-control <?php if(!empty($this->errors['reply-to'])) echo 'error'; ?>" type="text" value="<?php echo $this->var['reply-to']; ?>" name="reply-to">
						</td>
					</tr>
					<tr class="<?php if(!empty($this->var['cc']) && !empty($this->var['bcc']) && !empty($this->var['reply-to'])) echo 'hide'; ?>"> 
						<td class="border-left column-left" colspan="3">
							<a id="add-cc" class="btn btn-primary add <?php if(!empty($this->var['cc'])) echo ' hide'; ?>" role="button" href="javascript:void(0);"> <span class="fa fa-plus"></span> Add CC </span></a>
							<a id="add-bcc" class="btn btn-primary add <?php if(!empty($this->var['bcc'])) echo ' hide'; ?>" role="button" href="javascript:void(0);"> <span class="fa fa-plus"></span> Add BCC </span></a>
							<a id="add-replyto" class="btn btn-primary add <?php if(!empty($this->var['reply-to'])) echo ' hide'; ?>" role="button" href="javascript:void(0);"> <span class="fa fa-plus"></span> Add Reply-To </span></a>
						</td>
					</tr>
					<tr>
						<td class="border-left column-right">Subject</td>
						<td class="column-left" colspan="2">
							<input class="form-control <?php if(!empty($this->errors['subject'])) echo 'error'; ?>" type="text" value="<?php echo $this->var['subject']; ?>" name="subject">
						</td>
					</tr>	
					<tr>
						<td class="border-left column-left" colspan="4">
						<?php wp_editor(self::_mceContent(), 'message', array('textarea_rows' => 20)); ?>
						</td>
					</tr>
					<tr>
						<td class="border-top border-left column-left" colspan="4">
							<div class="input-group">
			          <span class="input-group-addon">
			            <span style="color: rgb(213, 78, 33);" class="fa fa-lock" title="Upgrade to GuiForm Pro to unlock"></span>
			          </span>
			          <span class="form-control" disabled="disabled"> Add conditional logic? </span>
			        </div>
						</td>
					</tr>
					<tr>
						<td class="border-bottom border-left column-left" colspan="4">
						<?php submit_button('Save Settings'); ?>
						</td>
					</tr>
				</table>
			</form>
	<?php
	}
	
	private function _mceContent(){
		$message = trim($this->var['message']);
		if(empty($message))
			$message = self::_mcePrintEntry();
		else
			$message = stripcslashes(html_entity_decode($message));
		
		return $message;
	}
	
	private function _mcePrintEntry(){
		global $guiform;
		$fields = $guiform->form($this->form, 'entry');
  	$html = "<span data-mce-style='font-size: 12pt;'><strong> Entry Details:</strong></span>";
  	$html .= "<table style='width: 60%;' cellspacing='0' cellpadding='8' >";
		foreach($fields as $key => $value){
			$key = str_ireplace("[]", "", $key);
			$html .= "<tr>";
			if($key != 'guiform_date_submitted' && $key != "recaptcha_response_field" && $key != "g-recaptcha-response-clone") $html .= "<td>$value[0]</td><td>{{$key}}</td>";
			$html .= "</tr>";
		}
		$html .= "</table>";
		
  	return $html;
	}
	
	private function _displayEntry(){
  	global $guiform;
  	
  	$fields = $guiform->form($this->form, 'entry');
  	
  	$html = "<div class='border-bottom border-left float-list'><h3>Form Fields</h3>";
		$html .= "<ul>";
		foreach($fields as $key => $value){
			$key = str_ireplace("[]", "", $key);
			if($key != 'guiform_date_submitted' && $key != "recaptcha_response_field" && $key != "g-recaptcha-response-clone") $html .= "<li data-field='{{$key}}'> $value[0]</li>";
		}
		$html .= "</ul>";
		$html .= "</div>";
		
  	return $html;
  }
  
  
	
	protected function _toHTML(){
		echo "<div class='wp-list-table'>";
		$url = add_query_arg(array('id' => false, 'action' => 'add'));
		self::process_bulk_action();
		self::show_message();
		echo "<form id='notification-filter' method='post' action='$url'>";
		self::views();
		self::prepare_items();
		self::display();
		self::get_column_info();
		echo "</form>";
		echo '</div>';
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
	    'delete-permanently'    => __("Delete")
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
						    	<p><strong>'. __("Delete mail successful.") .'</strong></p>
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
			'status'  => array('status', false ),
			'subject' => array('subject', true ),
			'from'    => array('from', true ),
			'to'      => array('to', true )
		);
	}
	
	function column_subject($item) {
		$edit_url = add_query_arg(array('action' => 'add', 'id'=> $item['id']));
	  $actions = array(
	  					'edit'       => sprintf("<a href='%s'>Edit</a>", $edit_url),
	            'delete'     => sprintf('<a class="delete" data-type="notification" id="item-%d" href="javascript:void(0)">Delete</a>', $item['id'])
	            );
	 
	  $title = "<a style='display: block;' href='javascript:void(0)'>".$item['subject']."</a>";
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
			'cb' 		  => "<input type='checkbox' />",
			'status'  => __('Status'),
			'subject' => __('Subject'),
			'from'    => __('From'),
			'to'      => __('To')
		);
	}
	
	function column_status( $item ){
		$status = ($item['status']) ? 'checked' : '';
		return sprintf( '<input type="checkbox" class="switch-button" name="status" value="%1$d" %2$s />', $item['id'], $status );
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
	  _e( 'No item available.' );
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
			$query = "SELECT id, name, value FROM $table WHERE type = 'notification' AND name = ". $this->form;
			//echo $query;
			/* -- Ordering parameters -- */
			
				
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
	    		$input = "<input type='button' value='". __("Send Test Mail") ."' class='button-primary test-mail-button'>";
	    	else
	    		$input = "<input type='button' value='". __("Send Verification Code") ."' class='button-primary activation-key-button'>";
	    		
	    	$items[] =
				array(
					'id'      => $value->id,
					'status'  => $row['status'],
					'subject' => $row['subject'],
					'from'    => $row['from'],
					'to'      => $row['to']
				);
	    }
	    
	    //Parameters that are going to be used to order the result
			$orderby = isset($_GET['orderby']) ? $_GET['orderby'] : '';
			$order = ($_GET['order'] == 'desc') ? SORT_DESC : SORT_ASC;
			
			$this->items = $items;
			
			if(isset($_GET['orderby']) && !empty($orderby)){
				$this->items = self::array_sort($this->items, $orderby, $order);
			}
				
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
	
	function array_sort($array, $on, $order = SORT_ASC){
			$new_array = array();
	    $sortable_array = array();
	
	    if (count($array) > 0) {
	        foreach ($array as $k => $v) {
	        	  $v = array_map('strtolower', $v);
	            if (is_array($v)) {
	                foreach ($v as $k2 => $v2) {
	                    if ($k2 == $on) {
	                        $sortable_array[$k] = $v2;
	                    }
	                }
	            } else {
	                $sortable_array[$k] = $v;
	            }
	        }
	
	        switch ($order) {
	            case SORT_ASC:
	                asort($sortable_array);
	                break;
	            case SORT_DESC:
	                arsort($sortable_array);
	                break;
	        }
	
	        foreach ($sortable_array as $k => $v) {
	            $new_array[$k] = $array[$k];
	        }
	    }
	
	    return $new_array;
	}
}