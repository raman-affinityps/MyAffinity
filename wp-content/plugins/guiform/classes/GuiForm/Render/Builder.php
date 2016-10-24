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
 * Form builder page.
 *
 * @category GuiForm
 * @package Render
 *
 * @since 1.0
 */
class GuiForm_Render_Builder extends GuiForm_Module{
	
  const NAME = __CLASS__;
	
	private $id;
	
	/**
	 * Constructor.
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @param array $data The data what has to be associated with this render.
	 */
	public function __construct($screen) { 
		$this->id = (isset($_GET['form'])) ? esc_html($_GET['form']) : '';
		
		if($screen->id == GuiForm_Plugin::NAME .'_page_guiform-builder'){
			$this->_addAction('admin_head','hideUpdate');
		}
		
		$this->_addAction( 'admin_enqueue_scripts', 'scripts' );
	  
	}
	
	public function hideUpdate(){
		remove_action( 'admin_notices', 'update_nag', 3 );
	}
	
	private function _skin(){
		
		global $_wp_admin_css_colors;
		
		$theme = get_user_option('admin_color');
		$themes = array('fresh', 'light', 'blue', 'coffee', 'ectoplasm', 'midnight', 'ocean', 'sunrise');
		$colors = $_wp_admin_css_colors[$theme];
		$color = $colors->icon_colors;
		
		if(in_array($theme, $themes)){
			switch($theme){
				case 'fresh':
				
					$bcolor[0] = $colors->colors[1]; //header color
					$bcolor[1] = $colors->colors[0]; //header hover color
					$bcolor[2] = $colors->colors[2]; //list active color
					$bcolor[3] = "#111111";          //list hover color
				 
				  $fcolor['base']    = $color['base'];
				  $fcolor['focus']   = $color['focus'];
				  $fcolor['current'] = $color['current'];
				  
				  break;
				case 'light':
				  $bcolor[0] = $colors->colors[1]; //header color
					$bcolor[1] = $colors->colors[0]; //header hover color
					$bcolor[2] = $colors->colors[2]; //list active color
					$bcolor[3] = "#333333";          //list hover color
				 
				  $fcolor['base']    = "#686868";
				  $fcolor['focus']   = "#0074A2";
				  $fcolor['current'] = $color['current'];
				  break;
				case 'blue':
				  $bcolor[0] = $colors->colors[1]; //header color
					$bcolor[1] = $colors->colors[0]; //header hover color
					$bcolor[2] = $colors->colors[2]; //list active color
					$bcolor[3] = $colors->colors[3];          //list hover color
				 
				  $fcolor['base']    = "#E2ECF1";
				  $fcolor['focus']   = "#FFFFFF";
				  $fcolor['current'] = $color['current'];
				  break;
				case 'coffee':
				  $bcolor[0] = $colors->colors[0]; //header color
					$bcolor[1] = $colors->colors[1]; //header hover color
					$bcolor[2] = $colors->colors[2]; //list active color
					$bcolor[3] = $colors->colors[3]; //list hover color
				 
				  $fcolor['base']    = $color['base'];
				  $fcolor['focus']   = $color['focus'];
				  $fcolor['current'] = $color['current'];
				  break;
				case 'ectoplasm':
				  $bcolor[0] = $colors->colors[0]; //header color
					$bcolor[1] = $colors->colors[1]; //header hover color
					$bcolor[2] = $colors->colors[2]; //list active color
					$bcolor[3] = $colors->colors[3]; //list hover color
				 
				  $fcolor['base']    = $color['base'];
				  $fcolor['focus']   = $color['focus'];
				  $fcolor['current'] = $color['current'];
				  break;
				case 'midnight':
				  $bcolor[0] = $colors->colors[0]; //header color
					$bcolor[1] = $colors->colors[1]; //header hover color
					$bcolor[2] = $colors->colors[2]; //list active color
					$bcolor[3] = $colors->colors[3]; //list hover color
				 
				  $fcolor['base']    = $color['base'];
				  $fcolor['focus']   = $color['current'];
				  $fcolor['current'] = $colors->colors[3];
				  break;
				case 'ocean':
				  $bcolor[0] = $colors->colors[0]; //header color
					$bcolor[1] = $colors->colors[1]; //header hover color
					$bcolor[2] = $colors->colors[2]; //list active color
					$bcolor[3] = $colors->colors[3]; //list hover color
				 
				  $fcolor['base']    = $color['base'];
				  $fcolor['focus']   = $colors->colors[2];
				  $fcolor['current'] = $color['current'];
				  break;
				case 'sunrise':
				 $bcolor[0]  = $colors->colors[0]; //header color
					$bcolor[1] = $colors->colors[1]; //header hover color
					$bcolor[2] = $colors->colors[2]; //list active color
					$bcolor[3] = $colors->colors[3]; //list hover color
				 
				  $fcolor['base']    = $color['base'];
				  $fcolor['focus']   = "#FFFFFF";
				  $fcolor['current'] = $color['current'];
				  break;
				default:
			}
		}
		
		$css = "/*------------------------ Base font color --------------------------------------*/
						
						#GuiForm #header span,
						#formName,
						#tools .tool span,
						#tools .tool span.icon{
						color: ".$fcolor['base'].";
						}
						
						/*------------------------ Focus font color --------------------------------------*/
						
						#GuiForm #header li:hover,
						#GuiForm #header li:hover span,
						#formName:hover,
						#tools h3.ui-accordion-header:hover,
						#tools .tool:hover span{
						color: ".$fcolor['focus'].";
						}
						
						/*------------------------ End Focus font color --------------------------------------*/
						
						
						#GuiForm #header{
						background: ".$bcolor[0].";
						border-radius: 0;	
						border: 1px solid ".$bcolor[0].";
						}
						
						#GuiForm #header ul {
						padding: 0px;
						margin: 0;
						line-height: 0;
						}
						
						#GuiForm #header li:hover{
						background-color: ".$bcolor[1].";
						}
						
						#GuiForm #header span{
						text-shadow: none;
						}
						
						#header .formName-list {
						width: 30%;
						padding: 5px !important;
						}
						
						#formName {
						margin: 0px;
						text-shadow: inherit;
						}
						
						#GuiForm .ui-resizable-e {
						border-left: 1px solid ".$bcolor[0].";
						border-right: 1px solid ".$bcolor[0].";
						background-color: ".$bcolor[0]." !important;
						}
						
						#container {
						margin-top: 45px;
						}
						
						#container {
						border: 1px solid ".$bcolor[0].";
						border-radius: 0;
						box-shadow: none;
						margin-left: 220px;
						border-top: none;
						}
						
						#canvas{
						border-radius: 0;
						}
						
						#tools {
						width: 220px;
						margin-top: 51px;
						}
						
						#tools h3.ui-accordion-header:hover{
						background-color: ".$bcolor[3].";
						}
						
						#tools h3.ui-state-active, 
						#tools h3.ui-state-active:hover{
						background: none repeat scroll 0 0 ".$bcolor[2].";
						color: ".$fcolor['current'].";
						}
						
						#tools h3{
						background: ".$bcolor[0].";
						border-radius: 0;
						margin: 0;
						border: none;
						font-weight: normal;
						padding: 8px 30px;
						font-size: 14px;
						text-shadow: none;
						border-top: 1px solid ".$bcolor[1].";
						}
						
						#tools .tool {
						background-color: ".$bcolor[1].";
						border: none;
						border-top: 1px solid ".$bcolor[0]." !important;
						}
						
						#tools .tool span {
						font-size: 13px;
						line-height: 18px;
						text-shadow: none;
						font-weight: normal;
						}
						
						#tools .tool span.icon{
						left: 20px;
						font-size: 16px;
						}
						
						#tools .ui-accordion-content {
						border-radius: 0;
						border: none;
						}
						
						#tools .ui-accordion-content-active .tool:last-of-type,
						#tools h3.ui-state-default:last-of-type, 
						#tools .ui-accordion-content:last-of-type {
						border-bottom: medium none;
						border-radius: 0;
						}
						
						#guiform-notice {
						border: 1px solid ".$bcolor[0].";
						border-radius: 0;
						color: #333333;
						}
						
						#guiform-notice h1{
						text-align: center;
						}
						
						#guiform-notice p{
						text-align: justify;
						}";

		wp_add_inline_style('default', $css);
	}
	
	public function scripts($hook_suffix){
		global $wpdb, $guiform;
	
		if($hook_suffix == GuiForm_Plugin::NAME .'_page_guiform-builder'){
			//Load all wordpress jquery necessary files
			$js = array('jquery',
			            'jquery-ui-draggable',
									'jquery-ui-droppable',
									'jquery-ui-sortable',
									'jquery-ui-core',
									'jquery-ui-widget',
									'jquery-ui-resizable',
									'jquery-ui-accordion',
									'jquery-ui-tabs',
									'jquery-ui-slider',
									'jquery-ui-mouse',
									'jquery-ui-spinner',
									'jquery-ui-button',
									'jquery-ui-datepicker',
									'jquery-ui-tooltip',
									'jquery-touch-punch',
									'jquery-effects-core',
									'jquery-color',
									'thickbox'
									);
			
			
			//Patch for WordPress version 4.1
			global $wp_version;
			if ( $wp_version >= 4.1 ) {
				 unset($js['jquery-ui-draggable']);
			   wp_enqueue_script('draggable', $guiform->assets('js/jquery.ui.draggable.min.js'), false, null);
			}

			wp_enqueue_style('thickbox');
			wp_enqueue_style('jquery-ui-theme', $guiform->vendor('jquery-ui/css/jquery-ui.min.css'), false, null);
			wp_enqueue_style('spectrum', $guiform->vendor('spectrum/spectrum.css'), false, null);
			wp_enqueue_style('custom-bootstrap', $guiform->vendor('bootstrap/css/custom-bootstrap.min.css'), false, GuiForm_Plugin::VERSION, 'all');
			wp_enqueue_style('bootstrap-colorpicker', $guiform->vendor('bootstrap-colorpicker-master/css/bootstrap-colorpicker.min.css'), false, GuiForm_Plugin::VERSION, 'all');
			wp_enqueue_style('bootstrap-theme', $guiform->vendor('bootstrap/css/bootstrap-theme.min.css'), false, GuiForm_Plugin::VERSION, 'all');
			wp_enqueue_style('font-awesome', $guiform->vendor('font-awesome/css/font-awesome.min.css'), false, GuiForm_Plugin::VERSION, 'all');
			wp_enqueue_style('default', $guiform->assets('skin/default/default.css'), false, GuiForm_Plugin::VERSION, 'all');
			wp_enqueue_style('guifbox', $guiform->assets('css/guifbox.css'), false, GuiForm_Plugin::VERSION, 'all');
			wp_enqueue_style('guiform', $guiform->assets('css/guiform.css'), false, GuiForm_Plugin::VERSION, 'all');
			
			
		  wp_enqueue_script('maskedinput', $guiform->assets('js/jquery.maskedinput.min.js'), false, null);
			wp_enqueue_script('mousewheel', $guiform->assets('js/jquery.mousewheel.js'), false, null);
			wp_enqueue_script('spectrum', $guiform->vendor('spectrum/spectrum.js'), false, null);
			wp_enqueue_script('bootstrap', $guiform->vendor('bootstrap/js/bootstrap.min.js'), false, null);
			wp_enqueue_script('bootstrap-colorpicker', $guiform->vendor('bootstrap-colorpicker-master/js/bootstrap-colorpicker.min.js'), false, null);
			wp_enqueue_script('switch-button', $guiform->assets('js/jquery.switchButton.js'), false, null);
			wp_enqueue_script('guifbox', $guiform->assets('js/guifbox.js'), $js, GuiForm_Plugin::VERSION);
			wp_enqueue_script('guiform', $guiform->assets('js/guiform.min.js'), false, GuiForm_Plugin::VERSION, true);
			
			
			self::_skin();
			
			if(!empty($this->id)){
				$data = $guiform->form($this->id);
			}
			
			$tools = apply_filters('guiform_filter_add_tools', array());
			$forms = apply_filters('guiform_filter_add_forms', array());
			
			wp_localize_script('guifbox', 'guiBuilder', array(
			  'id'          => $this->id,
			  'title'       => $data->title == null ?  __('Untitled Form', GuiForm_Plugin::NAME) : $data->title,
			  'database'    => $data->database,
			  'canvas'      => $data->canvas,
			  'nonce'       => wp_create_nonce( "guiform_nonce" ),
			  'emails'      => $guiform->get_mail(),
				'version'     => GuiForm_Plugin::VERSION,
				'request_time'=> $guiform->request_time,
				'server'      => $guiform->server,
				'domain'      => get_site_url(),
				'guiform_url' => admin_url('admin.php?page='. GuiForm_Plugin::NAME),
				'admin_url'		=> admin_url('admin.php'),
				'site_name'   => get_bloginfo('name'),
				'plugins_url' => plugins_url(GuiForm_Plugin::NAME .'/'),
				'preview_url' => $guiform->permalink(0),
				'settings'    => $wpdb->get_results("SELECT name, value FROM $wpdb->guiform_options WHERE type = 'settings' AND NOT( name LIKE 'license_%')", OBJECT_K),
				'options'     => self::_options(),
				'Tools'       => $tools,
				'Forms'       => $forms
			));
			
			wp_localize_script('guifbox', 'I18n', array(
			  'guifbox' => GuiForm_Module_I18n::lang('guifbox'),
			  'guiform' => GuiForm_Module_I18n::lang('guiform')
			));
		}
		
	}
	
	private function _options(){
		global $wpdb;
		$results = $wpdb->get_results("SELECT name, value FROM $wpdb->guiform_options WHERE type = 'options' AND name != 'license_key' ORDER BY name ASC", OBJECT_K);
		$items = array();
		
		foreach($results as $row){
			$items[$row->name] = unserialize($row->value);
		}
		
		return $items;
	}
	
	public function help(){
		$screen = get_current_screen();
		$user = wp_get_current_user();
		
		$screen->add_help_tab( array(
			'id' => 'guiform-help-tab-form-builder-navigation',
			'title' => __('Navigation', GuiForm_Plugin::NAME),
			'content' => '<p>'. __('<strong>Save Form</strong> - Save or update form.', GuiForm_Plugin::NAME) .'</p>
										<p>'. __('<strong>Create New Form</strong> - Empty workspace for new form.', GuiForm_Plugin::NAME) .'</p>
										<p>'. __('<strong>Form Preview</strong> - Display form output.', GuiForm_Plugin::NAME) .'</p>
										<p>'. __('<strong>Form Style</strong> - Display form styling options such as colors fonts and costume CSS.', GuiForm_Plugin::NAME) .'</p>
										<p>'. __('<strong>Required</strong> - Add required validation in your field.', GuiForm_Plugin::NAME) .'</p>
										<p>'. __('<strong>Remove Field</strong> - Remove selected field.', GuiForm_Plugin::NAME) .'</p>
										<p>'. __('<strong>Field Properties</strong> - Display field properties.', GuiForm_Plugin::NAME) .'</p>
										<p>'. __('<strong>Source Code</strong> - Generate WordPress shortcode, link, Embedded JavaScript, Iframe, Raw HTML and JavaScript.', GuiForm_Plugin::NAME) .'</p>
										<p>'. __('<strong>Thank You Message</strong> - Customize form redirect link or display message after form submission.', GuiForm_Plugin::NAME) .'</p>
										<p>'. __('<strong>Email Settings</strong> - Customize email notification.', GuiForm_Plugin::NAME) .'</p>
										<p>'. __('<strong>Form Title</strong> - Name of the form.', GuiForm_Plugin::NAME) .'</p>'
		));
		
		$screen->add_help_tab( array(
			'id' => 'guiform-help-tab-form-builder-tools',
			'title' => __('Tools', GuiForm_Plugin::NAME),
			'content' => '<p>'. __('<strong>Basic Tools</strong> - Basic fields used for creating forms.', GuiForm_Plugin::NAME) .'</p>
										<p>'. __('<strong>Quick Tools</strong> - Personalized fields used for specific kind of data input.', GuiForm_Plugin::NAME) .'</p>
										<p>'. __('<strong>Custom Tools</strong> - Customize fields where you can add prefix and postfix icon or text.', GuiForm_Plugin::NAME) .'</p>
										<p>'. __('<strong>Display Tools</strong> - Used to display text content within your form.', GuiForm_Plugin::NAME) .'</p>
										<p>'. __('<strong>Grid Layouts</strong> - Grid systems are used for creating form layouts through a series of rows and columns that house your fields.', GuiForm_Plugin::NAME) .'</p>',
		
		));
		
		$screen->add_help_tab( array(
		  'id' => 'guiform-help-tab-form-builder-properties',
		  'title' => __('Shortcut Keys', GuiForm_Plugin::NAME),
			'content' => '<ul>
											<li><p>'. __('<strong>Delete Key</strong> - Remove selected field.', GuiForm_Plugin::NAME) .'</p></li>
											<li><p>'. __('<strong>Esc Key</strong> - Hide property box.', GuiForm_Plugin::NAME) .'</p></li>
											<li><p>'. __('<strong>Double Left Click of Mouse</strong> - Display field properties.', GuiForm_Plugin::NAME) .'</p></li>
											<li><p>'. __('<strong>Ctrl + Left Arrow Key</strong> - Minimize workspace area.', GuiForm_Plugin::NAME) .'</p></li>
											<li><p>'. __('<strong>Ctrl + Right Arrow Key</strong> - Maximize workspace area.', GuiForm_Plugin::NAME) .'</p></li>
											<li><p>'. __('<strong>Arrow Keys</strong> - Navigate to form field.', GuiForm_Plugin::NAME) .'</p></li>
										</ul>'
		));
		
		$screen->add_help_tab( array(
		  'id' => 'guiform-help-tab-form-builder-context-menu',
		  'title' => __('Context Menu', GuiForm_Plugin::NAME),
			'content' => '<p>'. __('A context menu appears upon user interaction, such as a right-click mouse in the form field. A context menu offers a limited set of choices that are listed below.', GuiForm_Plugin::NAME) .'</p>
											<li><p>'. __('<strong>Delete</strong> - Remove selected field.', GuiForm_Plugin::NAME) .'</p></li>
										</ul>'
		));
		
		$screen->set_help_sidebar(
			"<p><strong>". __('For more information:', GuiForm_Plugin::NAME) ."</strong></p>
			 <p><a target='_blank' href='https://www.guiform.com/docs'>". __('Documentation', GuiForm_Plugin::NAME) ."</a></p>
			 <p><a target='_blank' href='https://www.guiform.com/support'>". __('Support Forums', GuiForm_Plugin::NAME) ."</a></p>"
		);
	}
	
	public function loadHelpTab($screen){
		$this->_addAction("load-". $screen, 'help');
	} 	
	
	public function toHTML(){	
		global $guiform;
		
		if(!function_exists('mail')){
			echo '<div style="margin-top: 10px; width: 99%;" class="guiform"><div role="alert" class="alert alert-danger" style="margin-bottom: 0px;">';
			_e('<strong>Warning!</strong> Php <i>mail()</i> function is disabled in your server. Email will not be sent.', GuiForm_Plugin::NAME);
			echo '</div></div>';
		}
		
		$html = "<a id='window-preview' target='_blank' title='". __('Window Preview', GuiForm_Plugin::NAME) ."'></a>
						 <div id='GuiForm' class='guiform container form-builder clearfix'>
							 <div style='display: none; text-align: center; margin-top: 10%;'>
							 	<img src='". $guiform->assets('images/guiform-loader.gif') ."'>
							 	<p style='font-weight: bold; letter-spacing: 5px; font-size: 16px;'>". __('WARNING: An Error occurred, please refresh the page and try again.', GuiForm_Plugin::NAME) ."</p>
							 </div>
						 </div>";
		echo "\n\n\n$html\n\n\n";
		
	}
	
}