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
 * View entry page.
 *
 * @category GuiForm
 * @package Render
 *
 * @since 1.0
 */
class GuiForm_Render_View extends GuiForm_Module  {
	
	const NAME = __CLASS__;
		
	var $entry;
	var $data;
	var $form;
	var $pagehook;
	var $server;
	var $field;
	var $navigation;
	
	public function __construct($screen) {
		global $guiform;
		
		$screen = get_current_screen();
		
		$this->entry    = esc_html($_GET['entry']);
		$this->form     = $guiform->form($_GET['form']);
		$this->field    = $guiform->entry($_GET['form'], $this->entry);
		$this->data     = $guiform->form($_GET['form'], 'data');
		$this->server   = $guiform->server($_GET['form']);
		$this->navigation = new GuiForm_Module_Navigation();
		$this->navigation->init(GuiForm_Render_View::NAME, $_REQUEST['form'], $this->entry);
		
		$this->pagehook = $screen->id;
		$this->_addMetaBox('guiform-metaboxes-details', 'Details', 'metabox_details', $this->pagehook, 'side', 'high');
		$this->_addMetaBox('guiform-metaboxes-page', 'Page', 'metabox_page',  $this->pagehook, 'side', 'core');
		$this->_addMetaBox('guiform-metaboxes-entry', 'Information', 'metabox_entry', $this->pagehook, 'normal','core');
		$this->_addAction('admin_enqueue_scripts', 'scripts');
	}
	
	/**
	 * Load necessary javascript library and css.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function scripts($hook_suffix){
		global $wpdb;
		
		if( $hook_suffix == GuiForm_Plugin::NAME .'_page_guiform-entry' || $hook_suffix == GuiForm_Plugin::NAME .'_page_guiform-entry_view'){
			global $guiform;
			
			wp_enqueue_script('jquery-ui-sortable');
			wp_enqueue_script('postbox');
			
			wp_enqueue_style('guiform-style', $guiform->assets('css/style.css'), false, GuiForm_Plugin::VERSION, 'all');
			wp_enqueue_style('guifbox', $guiform->assets('css/guifbox.css'), false, GuiForm_Plugin::VERSION, 'all');
			wp_enqueue_style('lightbox', $guiform->assets('css/lightbox.css'), false, GuiForm_Plugin::VERSION, 'all');
			
			wp_enqueue_script('guifbox', $guiform->assets('js/guifbox.js'), array('jquery'), GuiForm_Plugin::VERSION, true);
		 	wp_enqueue_script('lightbox', $guiform->assets('js/lightbox.js'), false, GuiForm_Plugin::VERSION, true);
		 	wp_enqueue_script('guiform-script', $guiform->assets('js/script.js'), array('jquery'), GuiForm_Plugin::VERSION, true);
			wp_localize_script('guiform-script', 'guiform', array(
			  'nonce'       => wp_create_nonce( "guiform_nonce" ),
				'plugins_url' => plugins_url('guiform/'),
				'post_url'    => admin_url('admin-post.php'),
				'form'        => esc_sql($_REQUEST['form']),
				'entry'       => esc_sql($_REQUEST['entry']),
				'type'        => 'view',
				'entries_url' => add_query_arg(array('entry' => false))
			));
		}
	}
	
	
	public function metabox_details($data){
		
		$date_submitted = sprintf('%1$s', date("Y M d, g:i a", strtotime($data['guiform_date_submitted'])));
		echo'<div id="misc-publishing-actions">
					 <div class="misc-pub-section">
					 	<span><strong>'. __('Entry Id' , 'guiform') .' : </strong>'. $data['id'] .'</span>
					 </div>
					 <div class="misc-pub-section">
					 	<span><strong>'.  __('Date Submitted' , 'guiform') .' : </strong>'. $date_submitted .'</span>
					 </div>
					 <div class="misc-pub-section">
					 	<span><strong>'. __('Database Server' , 'guiform') .' : </strong>'. $this->server['host'] .'</span>
					 </div>
					 <div class="misc-pub-section">
					 	<span><strong>'. __('IP Address' , 'guiform') .' : </strong>'. $data['guiform_ip'] .'</span>
					 </div>
					 <div class="misc-pub-section">
					 	<span><strong>'. __('OS' , 'guiform') .' : </strong>'. $data['guiform_os'] .'</span>
					 </div>
					 <div class="misc-pub-section">
					 	<span><strong>'. __('Browser' , 'guiform') .' : </strong>'. $data['guiform_browser'] .'</span>
					 </div>
				 </div>';
		
	}
	
	/**
	 * Check array key column and value.
	 *
	 * @since 1.0
	 * @access public
	 * @return $db array
	 */
	public function arrayColumn($array, $colName, $key){
	  $results=array();
	  
	  
	  if(!is_array($array)) return $results;
	  foreach($array as $child){
	  	$childKey = str_replace('[]', '', $child[$key]);
      if(!is_array($child)) continue;
    	if(!is_array($colName)){
        $results[$childKey] = $child[$colName];
      }
      else{
      	foreach($colName as $name){
      		$results[$childKey][] = $child[$name];
      	}
      }
	  }
	  return $results;
  }
	
	public function metabox_entry(){
		global $guiform;
		
		unset($this->field['id']);
		unset($this->field['guiform_read']);
		unset($this->field['guiform_status']);
		unset($this->field['guiform_ip']);
		unset($this->field['guiform_date_submitted']);
		unset($this->field['guiform_browser']);
		unset($this->field['guiform_os']);
		$labels = self::arrayColumn($this->data, array('label', 'type'), 'name');

				
		$html = "<table class='widefat'><tbody>";
		
		if(count($this->field) > 0){
			foreach($this->field as $key => $value){
				$value = stripslashes_deep($value);
				$label = is_array($labels[$key]) ? $labels[$key][0] : $key;
			
				if($guiform->isSerial($value)){
					$index = 0;
					$value = unserialize($value);
					$html .= "<tr class='label'><td>$label :</td></tr><tr class='value'><td><ul class='post-image'>";
					foreach($value as $file => $row){
					
						if($labels[$key][1] == "f_file"){
							$keyFile = key($row);
							$file = $guiform->uploadFolder($keyFile, $row[$keyFile]['path'], true);
							$size = $guiform->bytesToSize($row[$keyFile]['size']);
							$types = array('gif','jpeg','jpg','png','tif','tiff','wbmp','ico','jng','bmp','svg','svgz','webp');
							
							if(in_array($row[$keyFile]['type'], $types)){
								if($row[$keyFile]['name'] != ''){
									$html .= "<li><strong><a target='_blank' href='$file'>". $row[$keyFile]['name'] ."</a></strong> <i>(". $size .")</i> <span class='download' data-index='". $index ."' data-field='". $key ."'>Download File</span></li>";
								}
							}
							else{
								if($row[$keyFile]['name'] != ''){
									$html .= "<li><strong>". $row[$keyFile]['name'] ."</strong> <i>(". $size .")</i> <span class='download' data-index='". $index ."' data-field='". $key ."'>Download File</span></li>";
								}
							}
							
							$index++;
						}
						else{
							$html .= "<li>$row</li>";
						}
					}
					$html .= "</ul></td></tr>";
				}
				else{
					$label = is_array($label) ? $label[0] : $label;
					if($labels[$key][1] == 'f_textarea'){
						$value = str_replace(array("\\r\\n", "\\t"), array("\r\n", "\t"), "$value");
						$value = stripcslashes($value);
						$value = (!empty($value)) ? "<textarea readonly='readonly' style='width: 97%; height: 394px; resize: vertical;'>$value</textarea>" : '';
						$html .= "<tr class='label'><td>$label :</td></tr><tr class='value'><td>$value</td></tr>";
					}
					else{
						$value = stripslashes($value);
						$html .= "<tr class='label'><td>$label :</td></tr><tr class='value'><td>$value</td></tr>";
					}
				}
			}
		}
		else{
			$html .= "<tr class='value'><td>No entry found.</td></tr>";
		}
		
		$html .= "</tbody></table>";
		echo $html;
		
	}
	
	public function metabox_page(){
		global $wpdb;
		
		$entry = esc_sql(trim($_REQUEST['entry']));
		$form = esc_sql(trim($_REQUEST['form']));
		$table = $wpdb->guiform_form.$form;
		
		$status = (isset($_REQUEST['entry_status'])) ? $_REQUEST['entry_status'] : 'all';
	
		if($status === 'all') $status = 1;
		if($status === 'trash') $status = 0;
		if($status === 'spam') $status = 2;
		  
		$sql = "SELECT(SELECT id FROM $table WHERE id < %d AND guiform_status = %d ORDER BY id DESC LIMIT 1) AS previd,
						(SELECT id FROM $table WHERE id > %d AND guiform_status = %d ORDER BY id ASC LIMIT 1) AS nextid";
						
		$sql_size = "SELECT COUNT(*) FROM $table WHERE guiform_status = %d";	
		$sql_pos = "SELECT COUNT(*) FROM $table WHERE id <= %d AND guiform_status = %d";			
  
  	$wpdb->update($table, array('guiform_read' => 1), array('id' => $entry), array('%d'), array('%d'));
		
  	$size = $wpdb->get_var($wpdb->prepare($sql_size, array($status)));
	 	$pos  = $wpdb->get_var($wpdb->prepare($sql_pos, array($entry, $status)));
		$row  = $wpdb->get_row($wpdb->prepare($sql, array($entry, $status, $entry, $status)), ARRAY_A);
     
		$prev_url = add_query_arg(array('entry' => $row['previd'])); 
		$prev_visibility = ''; 
		if(empty($row['previd'])){
			$prev_url = "javascript:void(0)"; 
			$prev_visibility = ' style="display: none;" ';
		}  
		  
		$next_url = add_query_arg(array('entry' => $row['nextid']));  
		$next_visibility = '';
		if(empty($row['nextid'])){
			$next_url = "javascript:void(0)"; 
			$next_visibility = ' style="display: none;" ';
		}   
		
    echo "<div class='button-wrap'> 
            <span class='guif-size'>Page <strong>$pos</strong> of <strong>$size</strong></span>
				  	<a $prev_visibility href='$prev_url'><span class='button fa fa-chevron-left' title='". __('Preview', GuiForm_Plugin::NAME) ."'></span></a>
				  	<a $next_visibility href='$next_url'><span class='button fa fa-chevron-right' title='". __('Next', GuiForm_Plugin::NAME) ."'></span></a>
			    </div>";
	}
	
	public function toHTML(){
		global $screen_layout_columns;
		$data = $this->field;		
		
		?>
		<div id="guiform-manager" class="wrap view-entry metabox-holder">
	
			<h2><?php echo $this->form->title; ?></h2>
	
			<form action="admin-post.php" method="post">
				<?php wp_nonce_field('guiform-metaboxes-general'); ?>
				<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false); ?>
				<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false); ?>
				<input type="hidden" id="form" value="<?php echo $_GET['form']; ?>">
				<div id="guiform" class="metabox-holder columns-<?php echo $screen_layout_columns; ?>">
					<div class="postbox-container" id="container-1">
						<?php do_meta_boxes($this->pagehook, 'advance', $data); ?>
					</div>
					<div class="postbox-container" id="container-2">
						<?php do_meta_boxes($this->pagehook, 'normal', $data); ?>
					</div>
					<div class="postbox-container" id="container-3">
						<?php do_meta_boxes($this->pagehook, 'side', $data); ?>
					</div>
				</div>	
			</form>
			<script type="text/javascript">
				//<![CDATA[
				jQuery(document).ready( function($) {
					$(document).on('click', '.columns-prefs label', function(){
						var column = $(this).attr('class').split('-')[2];
						$('#guiform.metabox-holder').attr('class', 'metabox-holder columns-'+ column);
					})
				});
				//]]>
			</script>
		</div>
		<?php
	}
}