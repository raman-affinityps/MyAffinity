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
 * The module for all admin stuff.
 *
 * @category GuiForm
 * @package Module
 *
 * @since 1.0
 */
class GuiForm_Module_Admin extends GuiForm_Module {

	const NAME = __CLASS__;
	
	private $_render;
	
	/**
	 * Library page suffix.
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @var string
	 */
	private $_adminPage = array();

	/**
	 * Constructor.
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @param GuiForm_Plugin $plugin The instance of the plugin.
	 */
	public function __construct( GuiForm_Plugin $plugin ) {
		parent::__construct( $plugin );
    $this->_addAction( 'admin_menu', 'menu' );
		$this->_addFilter( 'plugin_action_links', 'actionLink', 10, 2 );
		$this->_addFilter( 'plugin_row_meta', 'pluginLinks', 10, 2 );
	}
	
	public function menu(){
		global $guiform;
		
		$this->_adminPage['form']      = add_object_page(__('GuiForm', GuiForm_Plugin::NAME), __('GuiForm', GuiForm_Plugin::NAME), 'manage_guiform', GuiForm_Plugin::NAME, array(&$this, 'adminPage' ), $guiform->assets('images/guiform-icon.png'));
		$this->_adminPage['forms']     = add_submenu_page(GuiForm_Plugin::NAME, __('GuiForm', GuiForm_Plugin::NAME), __('Form Manager', GuiForm_Plugin::NAME), 'manage_guiform', GuiForm_Plugin::NAME,           array(&$this, 'adminPage'));
		$this->_adminPage['entry']     = add_submenu_page(GuiForm_Plugin::NAME, __('Entry Manager', GuiForm_Plugin::NAME), __('Entry Manager', GuiForm_Plugin::NAME), 'manage_guiform', 'guiform-entry',     array(&$this, 'adminPage'));
		$this->_adminPage['export']    = add_submenu_page(GuiForm_Plugin::NAME, __('Export Manager', GuiForm_Plugin::NAME), __('Export Manager', GuiForm_Plugin::NAME), 'manage_guiform', 'guiform-export',  array(&$this, 'adminPage'));
		$this->_adminPage['builder']   = add_submenu_page(GuiForm_Plugin::NAME, __('Form Builder', GuiForm_Plugin::NAME), __('Form Builder', GuiForm_Plugin::NAME), 'manage_guiform', 'guiform-builder',   array(&$this, 'adminPage'));
		$this->_adminPage['extension'] = add_submenu_page(GuiForm_Plugin::NAME, __('Extension', GuiForm_Plugin::NAME), __('Extension', GuiForm_Plugin::NAME), 'manage_guiform', 'guiform-extension',   array(&$this, 'adminPage'));
		$this->_adminPage['settings']  = add_submenu_page(GuiForm_Plugin::NAME, __('Settings', GuiForm_Plugin::NAME), __('Settings', GuiForm_Plugin::NAME), 'manage_guiform', 'guiform-settings',  array(&$this, 'adminPage'));
		$this->_adminPage['uninstall'] = add_submenu_page(GuiForm_Plugin::NAME, __('Uninstall', GuiForm_Plugin::NAME), __('Uninstall', GuiForm_Plugin::NAME), 'manage_guiform', 'guiform-uninstall', array(&$this, 'adminPage'));
		
		$this->_addFilter('load-'. $this->_adminPage['builder'],  array('GuiForm_Render_Builder', 'help'));

	  $class_list = array('forms', 'entry', 'export', 'builder', 'extension', 'settings', 'uninstall', 'view');
	  
	  foreach($class_list as $class){
	  	// Set a Screen Options
			$this->_addFilter('load-'. $this->_adminPage[$class], 'setScreenOptions');
			// Adds a Screen Options
			$this->_addFilter('load-'. $this->_adminPage[$class], 'formScreenOptions');
			// Load Screen Options
			$this->_addAction('load-'. $this->_adminPage[$class], 'classListTable');
	  }
	  
	}
	
	public function setScreenOptions(){
		if(isset($_POST['wp_screen_options']['value'])){
			$user = get_current_user_id();
			$value = sanitize_text_field($_POST['wp_screen_options']['value']);
			if ($value == round($value) && $value > 0) {
				update_user_meta($user, $_POST['wp_screen_options']['option'], $_POST['wp_screen_options']['value']);
			}
		}
	}
	
	/**
	 * Page screen options
	 *
	 * @since 1.0
	 * @access public
	 */
	public function formScreenOptions(){
		       
    $screen = get_current_screen();
    
    if(!is_object($screen)) return;
     switch($screen->id) :
    
     case $this->_adminPage['form'] :
      
     if(isset($_REQUEST['form'])) :
				$args = array(
              'label' => __('Item per page'),
              'default' => 15,
              'option' => 'guiform_form_mail_row_per_page'
	      );
	      add_screen_option( 'per_page', $args );
			else :
				$args = array(
              'label' => __('Item per page'),
              'default' => 15,
              'option' => 'guiform_form_row_per_page'
	      );
	      add_screen_option( 'per_page', $args );
			endif;
      
      break;
      
      case $this->_adminPage['entry'] :
	      if(isset($_REQUEST['form']) &&  isset($_REQUEST['entry'])) :
					add_screen_option( 'layout_columns', array(
						'max'		=> 2,
						'default'	=> 2
					));
				else :
					$id = isset($_REQUEST['form']) ? esc_sql($_REQUEST['form']) : get_user_meta(get_current_user_id(), 'guiform_form_selected', true);
					$args = array(
            'label' => __('Item per page'),
            'default' => 15,
            'option' => 'guiform_'. $id .'_entry_row_per_page'
		      );
		      add_screen_option( 'per_page', $args );
				endif;
      break;
      
      case $this->_adminPage['settings']:
      
	      $tab = esc_sql($_REQUEST['tab']);
	      
	      if($tab == 'database') :
	      	$args = array(
	              'label' => __('Item per page'),
	              'default' => 15,
	              'option' => 'guiform_settings_database_row_per_page'
		      );
		      add_screen_option( 'per_page', $args );
	      elseif($tab == 'font') :
	      	$args = array(
	              'label' => __('Item per page'),
	              'default' => 15,
	              'option' => 'guiform_settings_font_row_per_page'
		      );
		      add_screen_option( 'per_page', $args );
		    elseif($tab == 'mail') :
	      	$args = array(
	              'label' => __('Item per page'),
	              'default' => 15,
	              'option' => 'guiform_settings_mail_row_per_page'
		      );
		      add_screen_option( 'per_page', $args ); 
		    elseif($tab == 'backup') :
	      	$args = array(
	              'label' => __('Item per page'),
	              'default' => 15,
	              'option' => 'guiform_settings_backup_row_per_page'
		      );
		      add_screen_option( 'per_page', $args ); 
		    elseif($tab == 'options') :
	      	$args = array(
	              'label' => __('Item per page'),
	              'default' => 15,
	              'option' => 'guiform_settings_options_row_per_page'
		      );
		      add_screen_option( 'per_page', $args ); 
	      elseif($tab == 'structure') :
	      	$args = array(
	              'label' => __('Item per page'),
	              'default' => 15,
	              'option' => 'guiform_settings_datastructure_row_per_page'
		      );
		      add_screen_option( 'per_page', $args );
	      endif;
      
      break;
    endswitch;
	}
	
	/**
	 * Instantiate page class.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function classListTable(){
		
		global $guiform;
		$screen = get_current_screen();

		if($screen->id == 'toplevel_page_'. GuiForm_Plugin::NAME){
			
			if (isset($_GET['form'])) {
				$tab = (isset($_REQUEST['tab'])) ? $_REQUEST['tab'] : 'settings';
				$class = 'GuiForm_Render_Forms_' . ucfirst($tab);
				if (class_exists($class, true)) {
					$this->_render = new $class($screen);
				}
				else{
					$this->_render = new GuiForm_Render_Forms($screen);
				}
			}
			else{	
				$this->_render = new GuiForm_Render_Forms($screen);
			}
		}
		else if($screen->id == GuiForm_Plugin::NAME .'_page_guiform-builder'){
			$this->_render = new GuiForm_Render_Builder($screen);
		}
		else if($screen->id == GuiForm_Plugin::NAME .'_page_guiform-entry'){
			if(isset($_GET['form']) && isset($_GET['entry'])){
				$screen->id = $screen->id .'_view';
				$this->_render = new GuiForm_Render_View($screen);
			}
			else
				$this->_render = new GuiForm_Render_Entry($screen);
		}
		else if($screen->id == GuiForm_Plugin::NAME .'_page_guiform-export'){
			$this->_render = new GuiForm_Render_Export($screen);
		}
		else if($screen->id == GuiForm_Plugin::NAME .'_page_guiform-extension'){
			$this->_render = new GuiForm_Render_Extension($screen);
		}
		else if($screen->id == GuiForm_Plugin::NAME .'_page_guiform-uninstall'){
			$this->_render = new GuiForm_Render_Uninstall($screen);
		}
		else if($screen->id == GuiForm_Plugin::NAME .'_page_guiform-settings'){
			$tab = (isset($_REQUEST['tab'])) ? $_REQUEST['tab'] : 'general';
			$class = 'GuiForm_Render_Settings_' . ucfirst($tab);
			if ( class_exists($class, true)) {
				$this->_render = new $class($screen);
			}
		}
	}
	
	public function adminPage(){
		
		### Check Whether User Can Manage
		if(!current_user_can('manage_guiform') || !is_admin()) {
			die("<h1 class='access-denied'>Access Denied</h1>");
		}
		
		$screen = get_current_screen();
		
		$this->_render->toHTML();
		
	}
	

	/**
	 * Updates the plugin's action links, which will be rendered at the plugins table.
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @param array $links The array of original action links.
	 * @param string $file The plugin basename.
	 * @return array Updated array of action links.
	 */
	public function actionLink($links, $file){
		if ( $file == plugin_basename( GUIFORM_BASEFILE ) ) {
			array_unshift(
				$links,
				sprintf(
					'<a href="%s">%s</a>',
					admin_url( 'admin.php?page=guiform-builder' ),
					__( 'Create Form', GuiForm_Plugin::NAME )
				),
				sprintf(
					'<a href="%s">%s</a>',
					admin_url( 'admin.php?page=guiform-settings' ),
					__( 'Settings', GuiForm_Plugin::NAME )
				)
			);
		}

		return $links;
	}
	
	/**
	 * Updates the plugin's action links, which will be rendered at the plugins table.
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @param array $links The array of original action links.
	 * @param string $file The plugin basename.
	 * @return array Updated array of action links.
	 */
	public function pluginLinks($links, $file){
		if($file == plugin_basename(GUIFORM_BASEFILE)){
			$links[] = '<a href="https://www.guiform.com/documentation/" target="_blank">'. __('Documentation', GuiForm_Plugin::NAME) .'</a>';
			$links[] = '<a href="https://www.guiform.com/ticket/" target="_blank">'. __('Support', GuiForm_Plugin::NAME) .'</a>';
		}

		return $links;
	}
}