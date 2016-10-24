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
 * Form confirmation settings.
 *
 * @category GuiForm
 * @package Render
 * @subpackage Form
 *
 * @since 1.0
 */
class GuiForm_Render_Forms_Confirmation extends GuiForm_Render_Forms{
	
	const NAME = __CLASS__;
	
	var $form;
	var $var;
	var $navigation;
	var $styles;
	
	/**
	 * Constructor.
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @param array $data The data what has to be associated with this render.
	 */
	public function __construct($screen, $nav = array()) { 
		global $guiform;
		
		$this->form = esc_sql($_REQUEST['form']);
		$this->navigation = new GuiForm_Module_Navigation();
		$this->navigation->init(GuiForm_Render_Forms_Confirmation::NAME, $this->form);
		
		$this->var = $guiform->getOption($this->form, false, 'confirmation')->value;
		
		add_action( 'admin_enqueue_scripts', array( &$this, 'scripts' ));
		add_filter( 'tiny_mce_before_init', array( &$this, 'wpse24113_tiny_mce_before_init' ));
		add_filter( 'the_editor', array( &$this, 'theEditor' ) ); 
		add_filter( 'mce_buttons_2', array( &$this, 'wpex_mce_buttons' ) );
		add_action( 'content_save_pre', array( &$this, 'preSave'), 100 );
		add_filter( 'tiny_mce_before_init', array( &$this, 'mytheme_tinymce_settings' ));
		
		if(isset($_POST['submit'])){
			self::_save();
		}
		
	}
	
	public function wpex_mce_buttons( $buttons ) {
		array_unshift( $buttons, 'fontselect' ); // Add Font Select
		array_unshift( $buttons, 'fontsizeselect' ); // Add Font Size Select
		return $buttons;
	}
	
	public function mytheme_tinymce_settings($settings) {
		unset($settings['content_css']);
		return $settings;
	}
	
	public function scripts($hook_suffix){
		if($hook_suffix == 'toplevel_page_'. GuiForm_Plugin::NAME){
			global $guiform;
				
			wp_enqueue_script('guiform-caret', $guiform->assets('js/jquery.caret.min.js'), false, GuiForm_Plugin::VERSION, 'all');
			
			wp_enqueue_style('guiform-style', $guiform->assets('css/style.css'), false, GuiForm_Plugin::VERSION, 'all');
			wp_enqueue_script('guiform-script', $guiform->assets('js/script.js'), array('jquery'), GuiForm_Plugin::VERSION, true);
			
			wp_localize_script('guiform-script', 'guiform', array(
			  'nonce'  => wp_create_nonce( "guiform_nonce" ),
				'assets' => $guiform->assets(),
				'type'   => 'confirmation',
				'caret'  => $this->var['type']
			));
		}
	}
	
	public function wpse24113_tiny_mce_before_init( $initArray ){
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
	
	private function _displayEntry(){
  	global $guiform;
  	
  	$fields = $guiform->form($this->form, 'entry');
  	
  	$html = "<div class='border-bottom border-left float-list'><h3>Form Fields</h3>";
		$html .= "<ul>";
		$x = 0;
		foreach($fields as $key => $value){
			$key = str_ireplace("[]", "", $key);
			if($key != 'guiform_date_submitted') $html .= "<li data-field='{{$key}}'> $value[0]</li>";
		}
		$html .= "</ul>";
		$html .= "</div>";
		
  	return $html;
  }
  
  private function _save(){
		global $guiform;
		unset($_POST['submit']);
		$this->var = array_map('trim', $_POST);
		$this->var = array_map('esc_sql', $this->var);
		$this->var['custom'] = stripslashes($_POST['custom']);
		$guiform->updateOption($this->form, $this->var, 'confirmation');
	}	

	protected function _toHTML() {
		global $tinymce_version, $guiform;
		
		$types = array('default' => 'Default Message',
									 'redirect' => 'Page Redirect',
									 'custom' => 'Custom Message');
		
		?>
		
		
		<form method="POST">
			<table class="table-settings" width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td rowspan="7" style="width: 200px; vertical-align: top; background: none repeat scroll 0% 0% #F1F1F1;">
						<?php echo self::_displayEntry(); ?>
					</td>
				</tr>
				<tr>
					<td style="height: 80px;" class="border-left column-right">Select Type</td>
					<td class="column-left">
					  <select name="type">
					  	<?php foreach( $types as $key => $value ) { ?>
			          <option <?php selected($this->var['type'], $key) ?> value="<?php echo $key; ?>">
			              <?php echo $value; ?> 
			          </option>
				      <?php } ?>
						</select>
					</td>
				</tr>
				<tr id="row-default"  class="<?php echo ($this->var['type'] == 'default') ? '' : 'hide'; ?> row-item">
					<td style="height: 80px;" class="border-left column-left" colspan="2">
						<h3>Default confirmation message</h3>
						<p class="description">After successful form submission, display default message.</p>
					</td>
				</tr>
				<tr id="row-redirect" class="<?php echo ($this->var['type'] == 'redirect') ? '' : 'hide'; ?> row-item">
					<td class="border-left column-left" colspan="2">
						<h3>Custom Page Url</h3>
						<p class="description">After successful form submission, redirect to a custom url.</p>
						<textarea placeholder="http://domain-name.com/thankyou?entry={entry_id}" name="url"><?php echo $this->var['url']; ?></textarea>
					</td>
				</tr>		
				<tr id="row-custom" class="<?php echo ($this->var['type'] == 'custom') ? '' : 'hide'; ?> row-item">
					<td class="border-left column-left" colspan="2">
						<?php wp_editor(stripcslashes(html_entity_decode($this->var['custom'])), 'custom', array('textarea_rows' => 20)); ?>
					</td>
				</tr>
				<tr>
					<td class="border-left border-bottom column-left" colspan="2">
						<?php submit_button('Save Settings'); ?>
					</td>
				</tr>
			</table>
		</form>
		
	<?php
	}
}