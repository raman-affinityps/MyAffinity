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
 * Renders general settings page.
 *
 * @category GuiForm
 * @package Render
 * @subpackage Page
 *
 * @since 1.0
 */
class GuiForm_Render_Settings_General extends GuiForm_Render_Settings {

	var $errors = array();

	/**
	 * Constructor.
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @param array $data The data what has to be associated with this render.
	 */
	public function __construct( ) { 
		add_action( 'admin_enqueue_scripts', array( &$this, 'scripts' ));
	}
	
	public function scripts($hook_suffix){
		if($hook_suffix == GuiForm_Plugin::NAME .'_page_guiform-settings'){
			global $guiform;
			wp_enqueue_style('guiform-style', $guiform->assets('css/style.css'), false, GuiForm_Plugin::VERSION, 'all');
		}
	}
	
	private function validate(){
		global $guiform;
		
		if(isset($_POST['submit'])){
			
			$autosave = $_POST['autosave'];
			$autosave_time = intval($_POST['autosave_time']);
			$upload_folder = esc_html($_POST['upload_folder']);
			$selection = esc_html($_POST['selection']);
			
			if(isset($autosave)){
				$guiform->updateOption('autosave', 1, 'settings');
			}
			else{
				$guiform->updateOption('autosave', 0, 'settings');
			}
			
			if(is_int($autosave_time)){
				if($autosave_time < 120){
					$guiform->updateOption('autosave_time', 120, 'settings');
				}
				else{
					$guiform->updateOption('autosave_time', $autosave_time, 'settings');
				}
			}
			
			if(isset($upload_folder)){
				if(empty($upload_folder)){
					$guiform->updateOption('upload_folder', 'wp-content/uploads/guiform', 'settings');
				}
				else{
		      $dir = ABSPATH;
					$dir = $dir.'/'.$upload_folder;
					$current_folder = $guiform->getOption('upload_folder')->value;
					
					if($upload_folder != $current_folder){
						if(!file_exists($dir)){
						
							if (!@mkdir($dir, 0700, true)) {
								echo '<div class="error"><p><strong>'. __("Invalid Upload Folder.", GuiForm_Plugin::NAME) .'</strong></p></div>';
								$guiform->updateOption('upload_folder', 'wp-content/uploads/guiform');
							}
							else{
								$guiform->updateOption('upload_folder', $upload_folder, 'settings');
							}
							
							$source = ABSPATH.$current_folder;
							self::copyDirectory($source, $dir);
							self::deleteDirectory($source);
						}
						else{
							echo '<div class="error"><p><strong>'. __("Directory is already exist.", GuiForm_Plugin::NAME) .'</strong></p></div>';
						}
					}
				}
			}
			
			$custom = esc_html($_POST['custom']);
			if($selection == 'custom' && !empty($custom)){
				$permalink = $guiform->getOption('permalink')->value;
				$permalink['selection'] =  'custom';
				$permalink['value'] =  esc_html($_POST['custom']);
				$guiform->updateOption('permalink', $permalink, 'settings');
				GuiForm_Module_Setup::flushRewrite();
			}
			else{
				$permalink['selection'] =  'default';
				$permalink['value'] =  'form';
				$guiform->updateOption('permalink', $permalink, 'settings');
			}
			
			if(has_filter('guiform_action_settings_general_save')){
				$this->errors = apply_filters('guiform_action_settings_general_save', $_REQUEST);
			}
			
			echo '<div class="updated"><p><strong>'. __("Update settings succesful.", GuiForm_Plugin::NAME). '</strong></p></div>';
		}
	}
	
	public function deleteDirectory($source){
		return is_file($source) ?
            @unlink($source) :
            array_map(array(self, 'deleteDirectory'), glob($source.'/*')) == @rmdir($source);
  }
	
	private function copyDirectory( $source, $destination ) {
		if ( is_dir( $source ) ) {
		@mkdir( $destination );
			$directory = dir( $source );
			while ( FALSE !== ( $readdirectory = $directory->read() ) ) {
			  if ( $readdirectory == '.' || $readdirectory == '..' ) {
			      continue;
			  }
			  $PathDir = $source . '/' . $readdirectory; 
			  if ( is_dir( $PathDir ) ) {
			      self::copyDirectory( $PathDir, $destination . '/' . $readdirectory );
			      continue;
			  }
			  copy( $PathDir, $destination . '/' . $readdirectory );
			}
			
			$directory->close();
		}else {
			copy( $source, $destination );
		}
	}
	
	/**
	 * Renders page template.
	 *
	 * @since 1.0
	 *
	 * @access protected
	 */
	protected function _toHTML(){
		global $guiform, $wpdb;
		
		if(isset($_POST['submit'])) self::validate();

		$profile_url = admin_url("profile.php");
		
		$profile = sprintf( __( 'Change the admin color scheme <a href="%s">here</a>.', GuiForm_Plugin::NAME), esc_url($profile_url));
				
		?>
		<form id="mainform" enctype="multipart/form-data" action="" method="post">
		<h3 class="title"><?php _e("Common Settings", GuiForm_Plugin::NAME); ?></h3>
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th class="titledesc" scope="row">
						<label for=""><?php _e("Enable/Disable", GuiForm_Plugin::NAME); ?></label>
					</th>
					<td>
						<label for="autosave"><input id="autosave" type="checkbox" <?php echo ($guiform->getOption('autosave')->value ? " checked='checked' " : ''); ?> value="1" name="autosave" style=""> <?php _e("Enable Auto save form.", GuiForm_Plugin::NAME); ?></label>                     
					</td>
				</tr>
				<tr valign="top">
					<th class="titledesc" scope="row">
					</th>
					<td>
						<input type="text" value="<?php echo $guiform->getOption('autosave_time')->value; ?>" size="4" name="autosave_time">
						<code><?php _e("Number in seconds the auto save will trigger (Minimum value is 60 seconds).", GuiForm_Plugin::NAME); ?></code>   
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="blogname"><?php _e("Upload Folder", GuiForm_Plugin::NAME); ?></label></th>
					<td>
						<label for="upload_folder">
							<code><?php echo ABSPATH; ?></code>
							<input type="text" style="width: 300px;" value="<?php echo $guiform->getOption('upload_folder')->value; ?>" name="upload_folder" id="upload_folder">
						</label>
					</td>
				</tr>
			</tbody>
		</table>
		
		<h3 class="title"><?php _e("Form Link", GuiForm_Plugin::NAME); ?></h3>
		<table class="form-table">
			<tbody>
				<tr>
					<th><label><input type="radio" <?php echo ($guiform->permalink['selection'] == 'default') ? ' checked="checked" ' : ''; ?> value="default" name="selection"> <?php _e("Default", 'guiform'); ?></label></th>
					<td>
						<code>
							<?php
								if(get_option('permalink_structure')){
						  		echo "<code>". get_site_url() ."/form/123</code>";
						  	}
						  	else{
						  		echo "<code>". get_site_url() ."/?form=123</code>";
						  	}
							?>
						</code>
					</td>
				</tr>
				<tr>
					<th><label><input type="radio"  <?php echo ($guiform->permalink['selection'] == 'custom') ? ' checked="checked" ' : ''; ?> value="custom" name="selection"> <?php _e("Custom Link", 'guiform'); ?></label></th>
					<td>
						<?php
						$value = ($guiform->permalink['selection'] == 'custom') ? $guiform->permalink['value'] : '';
						if(get_option('permalink_structure')){
				  		echo "<code>". get_site_url() ."/</code>
							  		<input type='text' name='custom' value='". $value ."' style='width: 150px;'>
							  		<code>/123</code>";
				  	}
				  	else{
				  		echo "<code>". get_site_url() ."/?</code>
							  		<input type='text' name='custom' value='". $value ."' style='width: 150px;'>
							  		<code>=123</code>";
				  	}
						?>
					</td>
				</tr>
			</tbody>
		</table>
		
		<?php if(get_bloginfo('version') >= 3.8) : ?>
			<div style="margin: 30px 0;">
				<h3 class="title"><?php _e("Color Scheme", GuiForm_Plugin::NAME); ?></h3>
				<p><?php echo $profile; ?></p>
			</div>
		<?php endif; ?>
		
		<?php do_action('guiform_action_settings_general_render'); ?>		
		
		<?php submit_button(); ?>		
		</form>
		<?php 
	}

}