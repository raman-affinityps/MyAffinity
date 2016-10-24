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
 * Base class for form settings.
 *
 * @category GuiForm
 * @package Render
 * @subpackage Form
 *
 * @since 1.0
 */
class GuiForm_Render_Forms_Settings extends GuiForm_Render_Forms {
	
	const NAME = __CLASS__;
	
	var $form;
	
	var $navigation;
	
	var $display;
	
	private $_errors = array();
	
	private $_data;
	
	/**
	 * Constructor.
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @param array $data The data what has to be associated with this render.
	 */
	public function __construct( ) {
		global $guiform;
		
		$this->form = esc_sql($_REQUEST['form']);
		
		$this->_data = (object) array_merge((array) $guiform->form($this->form), (array) $guiform->getOption($this->form, false, 'smtp')->value);
		
												 
		$this->navigation = new GuiForm_Module_Navigation();
		$this->navigation->init(GuiForm_Render_Forms_Settings::NAME, $this->form);
		if(isset($_POST['submit'])) self::_save();	
		add_action( 'admin_enqueue_scripts', array( &$this, 'scripts' ));
	}
	
	public function scripts($hook_suffix){
		global $guiform;
		
		if($hook_suffix == 'toplevel_page_'. GuiForm_Plugin::NAME){
			wp_enqueue_style('guiform-style', $guiform->assets('css/style.css'), false, GuiForm_Plugin::VERSION, 'all');	
			wp_enqueue_script('guiform-script', $guiform->assets('js/script.js'), array('jquery'), GuiForm_Plugin::VERSION, true);
			wp_localize_script('guiform-script', 'guiform', array(
			  'nonce'  => wp_create_nonce( "guiform_nonce" ),
				'assets' => $guiform->assets(),
				'type'   => 'form-settings',
				'caret'  => false,
				'form'   => $_REQUEST['form']
			));
		}
	}
	
	private function _save(){
		global $wpdb, $guiform;
		
		$title = esc_html($_POST['title']);
		$display = esc_sql($_POST['display']);
		$saveEntries = $_POST['save_entry'];
		$smtpHost = esc_sql($_POST['smtp_host']);
		$smtpPort = $_POST['smtp_port'];
		
		if ( ! wp_verify_nonce( $_POST['guiform_nonce'], 'guiform_nonce' )) {
  		$this->errors['nonce'] = __('<div class="error"><p>Security check, Invalid nonce!</p></div>', GuiForm_Plugin::NAME); 
		}
		
		
		$wpdb->update( 
			$wpdb->guiform, 
			array( 
				'title'       => $title,
				'display'     => 'frontend',
				'save_entry'  => $saveEntries
			), 
			array( 'id' => $this->form ), 
			array( '%s', '%s', '%d'	), 
			array( '%d' ) 
		);
		
		$this->_data = (object) array_merge((array) $guiform->form($this->form), (array) $guiform->getOption($this->form, false, 'smtp')->value);
		
	}

	public function _toHTML(){
		global $guiform;
		
		if(isset($_POST['submit'])){
			if(sizeof($this->errors)){
				echo "<div class='error'>";
				foreach($this->errors as $error){
					echo '<p>'. $error .'</p>';
				}
				echo "</div>";
			}
			else{
				echo '<div class="updated below-h2" id="message"><p>Update settings successful.</p></div>';
			}
		}
		
		?>
		<form id="mainform" enctype="multipart/form-data" action="" method="post">
			<?php wp_nonce_field('guiform_nonce','guiform_nonce'); ?>
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th class="titledesc" scope="row">
							<label for="title"><?php _e("Form Title", GuiForm_Plugin::NAME); ?></label>
						</th>
						<td>
							<label for="title">
								<input type="text" style="width: 300px;" value="<?php echo $this->_data->title; ?>" name="title" id="title">
							</label>                     
						</td>
					</tr>
					<tr valign="top">
						<th class="titledesc" scope="row">
							<label for="save-entry"><?php _e("Save Entries", GuiForm_Plugin::NAME); ?></label>
						</th>
						<td>
							<label for="save-entry">
								<input <?php echo ($this->_data->save_entry ? " checked='checked' " : ''); ?> type="checkbox" value="1" name="save_entry" id="save-entry"> 
								<?php _e('Save entry to database.', GuiForm_Plugin::NAME); ?>
							</label>                     
						</td>
					</tr>
				</tbody>
			</table>
		<?php submit_button(); ?>		
		</form>
		<?php
	}
}