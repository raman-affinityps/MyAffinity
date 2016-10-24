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
 * General module what setups all required environment.
 *
 * @category GuiForm
 * @package Module
 *
 * @since 1.0
 */
class GuiForm_Module_Setup extends GuiForm_Module {

	const NAME = __CLASS__;

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
		
		$this->_addAction( 'init', 'autoUpdate');
		$this->_addFilter( 'cron_schedules', 'cronSchedule' ); 
		$this->_addAction( 'activate_'. GuiForm_Plugin::NAME .'/index.php', 'activatePlugin' );
		$this->_addAction( 'activate_'. GuiForm_Plugin::NAME .'/index.php', 'flushRewrite' );
		$this->_addAction( 'upgrader_process_complete', 'activatePlugin', 10, 0 );
		$this->_addAction( 'deactivate_'. GuiForm_Plugin::NAME .'/index.php', 'deactivatePlugin' );
		$this->_addAction( 'plugins_loaded', 'loadTextDomain' );
		$this->_addAction( 'admin_init', 'flushRewrite' );
		$this->_addAction( 'media_buttons', 'add_media_button' );
		$this->_addAction( 'wp_ajax_guiform_media_button', 'ajax_media_button' );
		$this->_addAction( 'guiform_update_cron', 'cronUpdate' );	
		$this->_addFilter( 'http_request_host_is_external', 'externalHost' );
		
		if ( !wp_next_scheduled('guiform_update_cron') ){
			wp_schedule_event(time(), 'daily', 'guiform_update_cron');
		}
		
	}
	
	public function autoUpdate(){
    $update = new GuiForm_Module_Update(GuiForm_Plugin::instance());
    $update->init();
	}
	
	public function externalHost(){
		return true;
	}
	
	/**
	 * Add media button in WordPress editor.
	 *
	 * @since 1.0
	 * @access public
	 */  
  public function add_media_button(){
  	global $guiform;
  	if(current_user_can('manage_guiform') || is_admin()){
			?>
				<a href="<?php echo add_query_arg(array('action' => 'guiform_media_button'), admin_url('admin-ajax.php')); ?>" class="button-primary add_media thickbox" title="Add GuiForm">
					<img style="padding: 0 4px 0 0;" src="<?php echo $guiform->assets('images/guiform-icon.png'); ?>" />
					<?php _e('Add Form', GuiForm_Plugin::NAME); ?>
				</a>
			<?php
		}
	}
	
	/**
	 * Display WordPress media box.
	 *
	 * @since 1.0
	 * @access public
	 */ 
	public function ajax_media_button(){
		global $wpdb;

		$forms = $wpdb->get_results("SELECT id, title FROM $wpdb->guiform ORDER BY title");
	?>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				jQuery('#guiform_form').submit(function(e){
					e.preventDefault();
					window.send_to_editor('[GuiForm id="' + jQuery('#guiform-item').val() +'"]');
					window.tb_remove();
				});
			});
		</script>
		<div id="guiform-box">
			<form id="guiform_form" class="media-upload-form type-form validate">
				<h3 class="media-title">Insert Form</h3>
				<p style="margin-bottom: 0;"><?php _e('Select a form below to insert into any Post or Page.', GuiForm_Plugin::NAME); ?></p>
				<select id="guiform-item" name="form" class="select input-select" style="width: 300px;">
					<?php foreach($forms as $form) : ?>
						<option value="<?php echo $form->id; ?>"><?php echo '#'. $form->id .": ". $form->title; ?></option>
					<?php endforeach; ?>
				</select>
				<p><input type="submit" class="button-primary" value="Insert Form" /></p>
			</form>
		</div>
	<?php
	die();
	}

	/**
	 * Activate the plugin and Create guiform table and options.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function activatePlugin(){
		global $wpdb;

		//wp_clear_scheduled_hook( 'guiform_cron_backup' );
		
		if(!file_exists(ABSPATH."/wp-content/uploads")){
			mkdir(ABSPATH."/wp-content/uploads");
		}
		
		if(@is_file(ABSPATH.'/wp-admin/includes/upgrade.php')) {
			include_once(ABSPATH.'/wp-admin/includes/upgrade.php');
		}elseif(@is_file(ABSPATH.'/wp-admin/upgrade-functions.php')) {
			include_once(ABSPATH.'/wp-admin/upgrade-functions.php');
		}else {
			die('We have problem finding your \'/wp-admin/upgrade-functions.php\' and \'/wp-admin/includes/upgrade.php\'');
		}
			
		$charset_collate = '';
		if($wpdb->supports_collation()) {
			if(!empty($wpdb->charset)) {
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if(!empty($wpdb->collate)) {
				$charset_collate .= " COLLATE $wpdb->collate";
			}
		}
		
		$create_table = array();
		
		$create_table['guiform'] = "CREATE TABLE `$wpdb->guiform` (
									`id` INT(11) NOT NULL AUTO_INCREMENT,
									`title` VARCHAR(50) NULL,
									`data` MEDIUMTEXT NULL,
									`entry_field` TEXT NULL,
									`canvas` MEDIUMTEXT NULL,
									`html` MEDIUMTEXT NULL,
									`tools` VARCHAR(150) NULL,	
									`display` VARCHAR(15) NOT NULL DEFAULT 'both',
									`init` MEDIUMTEXT NULL,
									`database` SMALLINT(6) NULL DEFAULT '0',
									`db_table` VARCHAR(50) NULL,	
									`template` VARCHAR(100) NULL,
									`status` TINYINT(1) NOT NULL DEFAULT FALSE,
									`save_entry` TINYINT(1) NULL DEFAULT TRUE,
									`last_update` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
									`created` DATETIME NULL,
									PRIMARY KEY (`id`)
								) $charset_collate;";
								
		$create_table['guiform_options'] = "CREATE TABLE IF NOT EXISTS `$wpdb->guiform_options` (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`type` VARCHAR(65) NOT NULL DEFAULT '',
			`name` VARCHAR(150) NOT NULL DEFAULT '',
			`value` TEXT NOT NULL DEFAULT '',
			PRIMARY KEY (`id`)
		)	$charset_collate;";	
		
		dbDelta( $create_table['guiform'] );				
								
		dbDelta( $create_table['guiform_options'] );								
		
		$options = array(array('settings', 'auto_update', 1),
										 array('settings', 'permalink', 
										 				serialize(array('selection' => 'default', 'value' => 'form'))
										 			),
										 array('settings', 'autosave', 1),
										 array('settings', 'autosave_time', 300),
										 array('settings', 'upload_folder', 'wp-content/uploads/guiform'),
										 array('mail', get_bloginfo('admin_email'),
										 				serialize(array(
										 						'name'            => get_bloginfo('name'),
														    'return_path'     => 0,
														    'protocol'        => 'php',
														    'smtp_host'       => '',
														    'smtp_port'       => '',
														    'smtp_encryption' => '',
														    'smtp_auth'       => 0,
														    'smtp_username'   => '',
														    'smtp_password'   => '',
														    'status'          => 1,
														    'key'             => '')
														)
										 			),
										 array('data-structure', 'TINYINT(1) NOT NULL', 
										 				serialize(array('type' => 'TINYINT', 'length' => '1', 'collation' => '')),
										 			),
										 array('data-structure', 'VARCHAR(50) NOT NULL', 
										 				serialize(array('type' => 'VARCHAR', 'length' => '50', 'collation' => '')),
										 			),
										 array('data-structure', 'TEXT NOT NULL', 
										 				serialize(array('type' => 'TEXT', 'length' => '', 'collation' => '')),
										 			),
										 array('data-structure', 'INT(11) NOT NULL', 
										 				serialize(array('type' => 'INT', 'length' => '11', 'collation' => '')),
										 			),
										 array('data-structure', 'VARCHAR(150) NOT NULL', 
										 				serialize(array('type' => 'VARCHAR', 'length' => '150', 'collation' => '')),
										 			),
										 array('data-structure', 'VARCHAR(255) NOT NULL', 
										 				serialize(array('type' => 'VARCHAR', 'length' => '255', 'collation' => '')),
										 			),
										 array('data-structure', 'TEXT NOT NULL', 
										 				serialize(array('type' => 'TEXT', 'length' => '', 'collation' => '')),
										 			),
										 array('data-structure', 'MEDIUMTEXT NOT NULL', 
										 				serialize(array('type' => 'MEDIUMTEXT', 'length' => '', 'collation' => '')),
										 			)
										 );	
	
		foreach($options as $option){
			if($wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->guiform_options WHERE name = '". $option[1] ."'" ) == 0){
				$wpdb->insert($wpdb->guiform_options, 
					 array('type'  => $option[0], 
								 'name'  => $option[1], 
								 'value' => $option[2]
					 ), 
					 array('%s', '%s', '%s')
				 );
			}
		}
		
		$role = get_role('administrator');
		if(!$role->has_cap('manage_guiform')) {
			$role->add_cap('manage_guiform');
		}
			
		if ( ! wp_next_scheduled( 'guiform_cron_backup' ) ) {
			//wp_schedule_event( time(), 'hourly', 'guiform_cron_backup');
		}
		
		self::_createPluginFolder();
		self::_createSourceFolder();
		
		$version =  get_option("GuiForm_Plugin::VERSION");
			
		if(empty($version) && $version != GuiForm_Plugin::VERSION){
			//Add update here.
		}
		
		delete_transient($wpdb->get_row("SELECT * FROM $wpdb->guiform_options WHERE name = 'license_key_". GuiForm_Plugin::NAME ."'")->value);
		
		update_option("GuiForm_Plugin::VERSION", GuiForm_Plugin::VERSION);
		
	}
	
	/**
	 * Deactivate the plugin.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function deactivatePlugin(){
		global $guiform;
		wp_clear_scheduled_hook( 'guiform_update_cron' );
		delete_transient($guiform->getOption('license_key_'. GuiForm_Plugin::NAME)->value);
	}
	
	/**
	 * Remove rewrite rules and then recreate rewrite rules.
	 *
	 * @see WP_Rewrite::flush_rules()
	 * @since 1.0
	 *
	 * Whether to update .htaccess (hard flush) or just update.
	 */
	public function flushRewrite(){
		global $guiform;
		$rules = get_option('rewrite_rules') ;
		if(gettype(get_option('rewrite_rules')) == "array"){
			if(!array_key_exists($guiform->permalink['value'] ."/(.+)", $rules)){
				flush_rewrite_rules();
			}
		}
	}

	/**
	 * Loads plugin text domain translations.
	 *
	 * @since 1.0
	 * @uses load_plugin_textdomain() To load translations for the plugin.
	 *
	 * @access public
	 */
	public function loadTextDomain() {
		load_plugin_textdomain( GuiForm_Plugin::NAME, false, dirname( plugin_basename( GUIFORM_BASEFILE ) ) . '/languages/' );
	}
	
	public function cronSchedule( $schedules ) {
		$schedules['weekly'] = array(
			'interval' => 7 * 24 * 60 * 60, //7 days * 24 hours * 60 minutes * 60 seconds
			'display' => __( 'Once Weekly', GuiForm_Plugin::NAME )
		);
		$schedules['daily'] = array(
			'interval' => 24 * 60 * 60,  // 24 hours * 60 minutes * 60 seconds
			'display' => __( 'Daily', GuiForm_Plugin::NAME )
		);
		$schedules['monthly'] = array(
			'interval' => 30 * 24 * 60 * 60, //30 days * 24 hours * 60 minutes * 60 seconds
			'display' => __( 'Once Monthly', GuiForm_Plugin::NAME )
		);
		$schedules['minutes'] = array(
			'interval' => 2 * 60, //2 minutes * 60 seconds
			'display' => __( 'Every Two Minutes', GuiForm_Plugin::NAME )
		);
	
		return $schedules;
	}
	
	private function _createPluginFolder(){
		global $guiform;
		$upload_dir = wp_upload_dir();
		$folders = array();
		$folders['plugin'] = trailingslashit( str_replace( '\\', '/', ABSPATH . $guiform->getOption('upload_folder')->value ) );
		$folders['source'] = trailingslashit( str_replace( '\\', '/', ABSPATH . $guiform->getOption('upload_folder')->value .'/source' ) );
		
		foreach($folders as $folder){
			//create folder if it not exists
			if ( ! is_dir( $folder ) ) {
				wp_mkdir_p( $folder );
			}
		}
		
		self::_createHTACCESS($folders['plugin']);
	}

  private function _createSourceFolder(){
		global $guiform;
		$folder = trailingslashit( str_replace( '\\', '/', realpath(ABSPATH . $guiform->getOption('upload_folder')->value .'/source') ) );;
		
		//create folder if it not exists
		if ( ! is_dir( $folder ) ) {
			wp_mkdir_p( $folder );
		}
	}
	
	private function _createHTACCESS($folder, $secure = true){
		if($secure){
			//create .htaccess for apache and index.php for folder security
			//if (!file_exists( $folder . '/.htaccess' ) )
				//file_put_contents( $folder . '/.htaccess', "<Files \"*\">" . PHP_EOL . "<IfModule mod_access.c>" . PHP_EOL . "Deny from all" . PHP_EOL . "</IfModule>" . PHP_EOL . "<IfModule !mod_access_compat>" . PHP_EOL . "<IfModule mod_authz_host.c>" . PHP_EOL . "Deny from all" . PHP_EOL . "</IfModule>" . PHP_EOL . "</IfModule>" . PHP_EOL . "<IfModule mod_access_compat>" . PHP_EOL . "Deny from all" . PHP_EOL . "</IfModule>" . PHP_EOL . "</Files>" );
			if (!file_exists( $folder . '/index.php' ) )
				file_put_contents( $folder . '/index.php', "<?php" . PHP_EOL . "if ( !defined( 'ABSPATH' ) ) {" . PHP_EOL . "header( 'HTTP/1.0 404 Not Found', true, 404 );" . PHP_EOL . "exit;" . PHP_EOL . "}");
  	}
  }
  
	private function _sqlAddSlashes($a_string = '', $is_like = false, $crlf = false, $php_code = false){
    if ($is_like) {
        $a_string = str_replace('\\', '\\\\\\\\', $a_string);
    } else {
        $a_string = str_replace('\\', '\\\\', $a_string);
    }

    if ($crlf) {
        $a_string = strtr(
            $a_string,
            array("\n" => '\n', "\r" => '\r', "\t" => '\t')
        );
    }

    if ($php_code) {
        $a_string = str_replace('\'', '\\\'', $a_string);
    } else {
        $a_string = str_replace('\'', '\'\'', $a_string);
    }

    return $a_string;
	} 
	
	private function _printableBitValue($value, $length){
	  $printable = '';
	  for ($i = 0, $len_ceiled = ceil($length / 8); $i < $len_ceiled; $i++) {
	      $printable .= sprintf('%08d', decbin(ord(substr($value, $i, 1))));
	  }
	  $printable = substr($printable, -$length);
	  return $printable;
	}
	
	private function _wpUri($file){
		$uri = GuiForm_Plugin::WP_URI;
		$uri = str_replace("https://", "http://down"."loads.", $uri);
		$uri = str_replace("plugins/". GuiForm_Plugin::NAME ."/", "plugin/", $uri);	
		return $uri . $file;
	}
	
	public function isString($param){
		$search = array("\x00", "\x0a", "\x0d", "\x1a"); //\x08\\x09, not required
		$replace     = array('\0', '\n', '\r', '\Z');
		$data = array();
		foreach($param['data'] as $key => $value){
			$type = $param['info'][$key];
			$field_type = self::_fieldFlags($type->type);
				
			if (!isset($value) || is_null($value)) {
				$data[] = 'NULL';
			}
			elseif($field_type == 'year'  ||
			       $field_type == 'int' || 
			       $field_type == 'smallint' || 
			       $field_type == 'tinyint' || 
			       $field_type == 'bigint'  || 
			       $field_type == 'mediumint'){
				$data[] = $value;
			}
			elseif ($field_type == 'blob' && $type->max_length == 63) {
				// empty blobs need to be different, but '0' is also empty :-(
	      if (empty($value) && $value != '0') {
	          $data[] = '\'\'';
	      } else {
	          $data[] = '0x' . bin2hex($value);
	      }
			}
			elseif ($field_type == 'bit') {
				$data[] = "b'" . addslashes(self::_printableBitValue($value, $type->length)) . "'";
			// something else -> treat as a string
			} else {
				$data[] = '\'' . str_replace($search, $replace, self::_sqlAddSlashes($value)) . '\'';
			} // end if
			
		}
		
		return $data;
		
	}
	
	private function _fieldFlags( $field_offset ) {
    $mysql_data_type_hash = array(
		    1 => 'tinyint',
		    2 => 'smallint',
		    3 => 'int',
		    4 => 'float',
		    5 => 'double',
		    7 => 'timestamp',
		    8 => 'bigint',
		    9 => 'mediumint',
		    10 => 'date',
		    11 => 'time',
		    12 => 'datetime',
		    13 => 'year',
		    16 => 'bit',
		    252 => 'blob',
		    //252 is currently mapped to all text and blob types (MySQL 5.0.51a)
		    253 => 'varchar',
		    254 => 'char',
		    246 => 'decimal'
		);

    return $mysql_data_type_hash[$field_offset];
	}
	
	public function cronBackup(){
		
		global $wpdb, $guiform;
		$guiform->updateOption('license_key', time(), 'settings');
		//get all of the tables
		//if($tables == '*') {
			$tables = $wpdb->get_col( 'SHOW FULL TABLES FROM `' . DB_NAME . '`');
		//} else {
			//$tables = is_array($tables) ? $tables : explode(',', $tables);
		//}
		
		//cycle through data
		$return = "";
		
		foreach($tables as $table) {
			
			$return.= "-- --------------------------------------------------------\n--\n-- Table structure for table `". $table ."`\n--\n";
	
			$INSERT = $param = $info = array();
			
			$structure = $wpdb->get_row("SHOW CREATE TABLE $table", ARRAY_A);
			$return.= str_replace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', $structure['Create Table']) .";\n\n";
			$column = $wpdb->get_col("SHOW COLUMNS FROM $table");
			$result = $wpdb->get_results("SELECT * FROM $table", ARRAY_A);
	    
			if(count($result) > 0){
				
				$return.= "--\n-- Dumping data for table `". $table ."`\n--\n\n";
			
				$return.= 'INSERT INTO `'.$table."` (";
				
			  $return .= '`'. implode("`, `", $column) .'`';
			
				$return.= ") VALUES \n";
				
				foreach($wpdb->col_info as $col) {
					$info[$col->name] = $col;
				}
			
				foreach($result as $value) {
					$param['data'] = $value;
					$param['info'] = $info;
					$data = self::isString($param);
					$INSERT[] = '('. implode(", ", $data) .')';
				}
				
				$return .= implode(",\n", $INSERT);
			}
			
			$return.="\n\n\n";
		}
	
		//save file
		if (true) {
			$zp = gzopen(ABSPATH. '/' . 'db-backup-'.time().'-'.(md5(implode(',',$tables))).'.sql.gz', "w9");
			gzwrite($zp, $return);
			gzclose($zp);
		} else {
			$handle = fopen(ABSPATH. '/' . 'db-backup-'.time().'-'.(md5(implode(',',$tables))).'.sql','w+');
			fwrite($handle, $return);
			fclose($handle);
		}
	}
	
	public function cronUpdate(){
		if(GuiForm_Plugin::NAME == 'guiform'){
			$input = @file_get_contents(GuiForm_Plugin::WP_URI) or exit();
			if(preg_match_all("/". GuiForm_Plugin::NAME .".(.*?).zip/", $input, $matches, PREG_SET_ORDER)) { 
				foreach($matches as $match) { 
					if(version_compare( GuiForm_Plugin::VERSION, $match[1], '<=')){
						$uri  = self::_wpUri($match[0]);
						$path = $match[0];
						$fp = fopen($path, 'w');
						$ch = curl_init($uri);
						curl_setopt($ch, CURLOPT_FILE, $fp);
						$data = curl_exec($ch);
						curl_close($ch);
						fclose($fp);
						self::zipExtract($match[0]);
					}
				} 
			}
		}
	}
	
	public function zipExtract($zipFile){
		if(class_exists('ZipArchive')){
			$zip = new ZipArchive;
			$extractPath = ABSPATH .'temp';
			if($zip->open($zipFile) != "true") exit(); 
			/* Extract Zip File */
			$zip->extractTo($extractPath);
			$zip->close();
			GuiForm_Render_Settings_General::deleteDirectory($extractPath .'/'. GuiForm_Plugin::NAME);
		}
	}

}