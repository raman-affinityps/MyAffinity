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
class GuiForm_Render_Export extends GuiForm_Render{
	
  const NAME = __CLASS__;
  
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
		$this->navigation = new GuiForm_Module_Navigation();
		$this->navigation->init(GuiForm_Render_Forms::NAME);
		add_action('admin_enqueue_scripts', array($this, 'scripts'));
	}
	
	public function scripts($hook_suffix){
		global $guiform;
		if($hook_suffix == GuiForm_Plugin::NAME .'_page_guiform-export'){
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-widget');
			wp_enqueue_script('jquery-ui-datepicker');
			wp_enqueue_script('jquery-ui-accordion');
			wp_enqueue_style('guiform-settings',$guiform->assets('css/style.css'), false, GuiForm_Plugin::VERSION, 'all');
			wp_enqueue_style('jquery-ui-theme', $guiform->vendor('jquery-ui/css/jquery-ui.min.css'), false, GuiForm_Plugin::VERSION, 'all');
			wp_enqueue_script('guiform-script', $guiform->assets('js/script.js'), false, GuiForm_Plugin::VERSION, true);
			wp_localize_script('guiform-script', 'guiform', array(
			  'nonce'  => wp_create_nonce( "guiform_nonce" ),
				'assets' => $guiform->assets(),
				'type'   => 'export'
			));
		}
	}
	
	protected function _toHTML() {}

	public function toHTML(){
		global $wpdb, $guiform;
		
		$cols = $wpdb->get_results( "SELECT id, title, save_entry FROM $wpdb->guiform ORDER BY id ASC" );
	
		$formHTML = '';
		foreach ( $cols as $form ) {
			if($form->save_entry){
				$formHTML .= "<option value='$form->id'>#$form->id : $form->title</option>";
			}
		}
		
		?>
    <div id="guiform-manager" class="wrap export metabox-holder">
    	<?php $this->navigation->toHTML(); ?>
			<div id="accordion">
			  <h3><?php _e('Export Form', GuiForm_Plugin::NAME); ?></h3>
				<div>
					<form id="export-form" method="POST" name="form" action="<?php echo admin_url('admin-post.php'); ?>">
						<?php  wp_nonce_field('guiform_nonce','guiform_nonce'); ?>
						<table class="form-table">
							<tbody>
								<tr valign="top">
									<th scope="row" style="width: 15%;"><label for="format"><?php _e('Format', GuiForm_Plugin::NAME); ?></label></th>
									<td style="width:27%;">
										<select name="action">
											<option value="guiform-export-form-sql"><?php _e('SQL', GuiForm_Plugin::NAME); ?></option>
											<option value="guiform-export-form-php"><?php _e('PHP array for import', GuiForm_Plugin::NAME); ?></option>
										</select>	
									</td>
									<td rowspan="5" style="vertical-align: top;">
										<div class="display-fields">
											<ul>
												<?php
													$i = 0;
													foreach ( $cols as $form ) {
														echo '<li><label for="field-'. $i .'"><input type="checkbox" name="forms[]" value="'. $form->id .'" checked="checked" id="field-'. $i .'"> #'. $form->id .' '. $form->title .' </label></li>';
															$i++;
													}
												?>
											</ul>
										</div>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row"><label for="fields"><?php _e('Include', GuiForm_Plugin::NAME); ?></label></th>
									<td>
										<label><input type="checkbox" name="schema" value="1"><span class="description"><?php _e('Table Schema', GuiForm_Plugin::NAME); ?></span></label>
										<label><input type="checkbox" name="drop_table" value="1"><span class="description"><?php _e('Add DROP TABLE', GuiForm_Plugin::NAME); ?></span></label>
										<label><input type="checkbox" name="entry" value="1"><span class="description"><?php _e('Form Entry', GuiForm_Plugin::NAME); ?></span></label>
										<label><input type="checkbox" name="zip" value="1"><span class="description"><?php _e('ZIP Compression', GuiForm_Plugin::NAME); ?></span></label>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row"><label for="fields"><?php _e('Function to use when dumping data', GuiForm_Plugin::NAME); ?></label></th>
									<td>
										<select name="entry_function">
											<option value="insert"><?php _e('INSERT', GuiForm_Plugin::NAME); ?></option>
											<option value="replace"><?php _e('REPLACE', GuiForm_Plugin::NAME); ?></option>
										</select>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row"></th>
									<td>
										<?php submit_button(__('Download File', GuiForm_Plugin::NAME)); ?>
									</td>
								</tr>
							</tbody>
						</table>
					</form>	
				</div>
				<h3><?php _e('Export Entry', GuiForm_Plugin::NAME); ?></h3>
				<div>
					<form id="export-entry" method="POST" name="form" action="<?php echo admin_url('admin-post.php'); ?>">
						<?php  wp_nonce_field('guiform_nonce','guiform_nonce'); ?>
						<table class="form-table">
							<tbody>
								<tr valign="top">
									<th scope="row" style="width: 15%;"><label for="format"><?php _e('Format', GuiForm_Plugin::NAME); ?></label></th>
									<td style="width:27%;">
										<select name="action">
											<option value="guiform-export-entry-csv"><?php _e('CSV', GuiForm_Plugin::NAME); ?></option>
											<option value="guiform-export-entry-sql"><?php _e('SQL', GuiForm_Plugin::NAME); ?></option>
										</select>	
									</td>
									<td rowspan="5" style="vertical-align: top;">
										<div class="display-fields"><img src="<?php echo $guiform->assets('images/save-loader.gif'); ?>"></div>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row"><label for="form"><?php _e('Select Form', GuiForm_Plugin::NAME); ?></label></th>
									<td>
										<select name="form">
											<option value=""><?php _e('Select', GuiForm_Plugin::NAME); ?></option>
											<?php echo $formHTML; ?>
										</select>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row"><label for="format"><?php _e('Date Range', GuiForm_Plugin::NAME); ?></label></th>
									<td>
										<input style="width: 48%;" placeholder="<?php _e('From:', GuiForm_Plugin::NAME); ?>" type="text" class="regular-text ltr" value="" id="from" name="from">
										<input style="width: 48%;" placeholder="<?php _e('TO:', GuiForm_Plugin::NAME); ?>" type="text" class="regular-text ltr" value="" id="to" name="to">
										<p class="description"><?php _e('Leave this field blank to select all entry.', GuiForm_Plugin::NAME); ?></p>
									</td>
								</tr>
								<tr valign="top" id="table-field">
									<th scope="row"><label for="fields"><?php _e('Use table field name', GuiForm_Plugin::NAME); ?></label></th>
									<td>
										<label><input type="checkbox" name="field_name" value="1"><span class="description"><?php _e('enable/disable', GuiForm_Plugin::NAME); ?></span></label>
									</td>
								</tr>
								<tr class="row-button" valign="top">
									<th scope="row"></th>
									<td>
										<?php submit_button(__('Download File', GuiForm_Plugin::NAME)); ?>		
									</td>
								</tr>
							</tbody>
						</table>
					</form>
				</div>
				<h3><?php _e('Import Data', GuiForm_Plugin::NAME); ?></h3>
				<div>
					<form enctype="multipart/form-data" id="import-data" method="POST" name="form" action="<?php echo admin_url('admin-post.php'); ?>">
						<?php  wp_nonce_field('guiform_nonce','guiform_nonce'); ?>
						<input type="hidden" name="action" value="guiform-import-form-php">
						<table class="form-table">
							<tbody>
								<tr valign="top">
									<th scope="row"><label for="fields"><?php _e('Upload PHP Array file', GuiForm_Plugin::NAME); ?></label></th>
									<td>
										<label>
											<input type="file" name="file" value="">
											<input type="submit" value="<?php _e('Upload File', GuiForm_Plugin::NAME); ?>" class="button button-primary" id="submit" name="submit">
											<img src="<?php echo $guiform->assets('images/save-loader.gif'); ?>">
										</label>
									</td>
								</tr>
							</tbody>
						</table>
					</form>
				</div>
			</div>
		</div>
		<?php
	}
	
	
}