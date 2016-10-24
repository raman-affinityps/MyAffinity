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
 * Uninstall page.
 *
 * @category GuiForm
 * @package Render
 *
 * @since 1.0
 */
class GuiForm_Render_Uninstall extends GuiForm_Module{
	
	
	private $_tables = array();
	
	/**
	 * Constructor.
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @param array $data The data what has to be associated with this render.
	 */
	public function __construct( ) { 
		$this->navigation = new GuiForm_Module_Navigation();
		$this->navigation->init(GuiForm_Render_Forms::NAME);
		$this->_tables = self::_tables();
		add_action('admin_enqueue_scripts', array($this, 'scripts'));
	}
	
	public function scripts($hook_suffix){
		global $guiform;
		if($hook_suffix == GuiForm_Plugin::NAME .'_page_guiform-uninstall'){
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-widget');
			wp_enqueue_script('jquery-ui-datepicker');
			wp_enqueue_script('jquery-ui-accordion');
			wp_enqueue_style('guiform-settings',$guiform->assets('css/style.css'), false, GuiForm_Plugin::VERSION, 'all');
			wp_enqueue_script('guiform-script', $guiform->assets('js/script.js'), false, GuiForm_Plugin::VERSION, true);
			wp_localize_script('guiform-script', 'guiform', array(
			  'nonce'  => wp_create_nonce( "guiform_nonce" ),
				'assets' => $guiform->assets(),
				'type'   => 'export'
			));
		}
	}
	
	private function _process(){
		global $wpdb, $guiform;
		
		$opstions = array('GuiForm_Plugin::VERSION');
		delete_transient($guiform->getOption('license_key_'. GuiForm_Plugin::NAME)->value);
		
		echo '<div id="message" class="updated fade" style="margin: 0px;">';
		echo '<p>';
		$tables = array($wpdb->guiform, $wpdb->guiform_options);
		foreach($tables as $table) {
			$wpdb->query("DROP TABLE {$table}");
			echo '<font style="color: green;">';
			printf(__('Table \'%s\' has been deleted.', GuiForm_Plugin::NAME), "<strong><em>{$table}</em></strong>");
			echo '</font><br />';
		}
		echo '</p>';
		echo '<p>';
		foreach($this->_tables as $row) {
			$table = $wpdb->guiform_form.$row->id;
			$wpdb->query("DROP TABLE {$table}");
			echo '<font style="color: green;">';
			printf(__('Table \'%s\' has been deleted.', GuiForm_Plugin::NAME), "<strong><em>{$table}</em></strong>");
			echo '</font><br />';
		}
		echo '</p>';
		echo '<p>';
		foreach($opstions as $setting) {
			$delete_setting = delete_option($setting);
			if($delete_setting) {
				echo '<font color="green">';
				printf(__('Setting Key \'%s\' has been deleted.', GuiForm_Plugin::NAME), "<strong><em>{$setting}</em></strong>");
				echo '</font><br />';
			}
		}
		echo '</p>';
		echo '</div>'; 
		
		
		self::_deactivate();
	}
	
	private function _deactivate(){
		$deactivate_url = wp_nonce_url('plugins.php?action=deactivate&amp;plugin='. GuiForm_Plugin::NAME .'/index.php', 'deactivate-plugin_'. GuiForm_Plugin::NAME .'/index.php');
		echo '<div class="wrap">';
		echo '<p><strong>'.sprintf(__('<a href="%s">Click Here</a> To Finish The Uninstallation And GuiForm Will Be Deactivated Automatically.', GuiForm_Plugin::NAME), $deactivate_url).'</strong></p>';
		echo '</div>';
	}
	
	private function _tables(){
		global $wpdb;
		return $wpdb->get_results("SELECT id, title FROM ". $wpdb->guiform, OBJECT);
	}
	
	public function toHTML( ){
		
		
		if(isset($_POST['do']) && $_POST['uninstall_guiform_yes'] == 'yes')
			self::_process();
		else	
			self::_toHTML();
	}
	
	private function _toHTML( ) {		
		
		global $wpdb;
		
		?>
		<div id="guiform-manager" class="wrap uninstall metabox-holder">
    	
    	<?php $this->navigation->toHTML(); ?>
    	
			<form method="post" action="<?php echo admin_url('admin.php?page=guiform-uninstall'); ?>">
			<div class="wrap">
				<p>
					<?php _e('Deactivating GuiForms plugin does not remove any data that may have been created, such as form entries and tables. To completely remove this plugin, you can uninstall it here.', GuiForm_Plugin::NAME); ?>
				</p>
				<p style="color: red">
					<strong><?php _e('WARNING:', GuiForm_Plugin::NAME); ?></strong><br />
					<?php _e('Once uninstalled, this cannot be undone. You should use a Database Backup plugin of WordPress to back up all the data first.', GuiForm_Plugin::NAME); ?>
				</p>
				<p style="color: red">
					<strong><?php _e('The following WordPress Tables will be DELETED:', GuiForm_Plugin::NAME); ?></strong><br />
				</p>
				<table class="widefat">
					<thead>
						<tr>
							<th><?php _e('GuiForm Tables', GuiForm_Plugin::NAME); ?></th>
						</tr>
					</thead>
					<tr>
						<td valign="top">
							<ol>
								<li><?php echo $wpdb->guiform; ?></li>
								<li><?php echo $wpdb->guiform_options; ?></li>
							<?php
								foreach($this->_tables as $row){
									$table = $wpdb->guiform_form.$row->id;
									if($wpdb->get_var("SHOW TABLES LIKE '$table'") == $table){
										echo '<li>'. $table . "<i style='font-weight: bold;'> (Form Title: $row->title)</i>"	.'</li>'."\n";
									}
								}
							?>
							</ol>
						</td>
					</tr>
				</table>
				<p style="text-align: center;">
					<?php _e('Do you really want to uninstall GuiForm?', GuiForm_Plugin::NAME); ?><br /><br />
					<input type="checkbox" name="uninstall_guiform_yes" value="yes" />&nbsp;<?php _e('Yes', GuiForm_Plugin::NAME); ?><br /><br />
					<input type="submit" name="do" value="<?php _e('UNINSTALL GuiForm', GuiForm_Plugin::NAME); ?>" class="button-primary" onclick="return confirm('<?php _e('You Are About To Uninstall GuiForm From WordPress.\nThis Action Is Not Reversible.\n\n Choose [Cancel] To Stop, [OK] To Uninstall.', GuiForm_Plugin::NAME); ?>')" />
				</p>
			</div>
			</form>
		
		</div>
		
		<?php
	}
	
}