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
 * Base class for all modules. Implements routine methods required by all modules.
 *
 * @category GuiForm
 * @package Module
 *
 * @since 1.0
 */
class GuiForm_Module_Api{
	
	/**
	 * Form url.
	 *
	 * @since 1.0
	 * @var string
	 * @access public
	 */
	public $permalink;
	
	/**
	 * Get operating system used.
	 *
	 * @since 1.0
	 * @var string
	 * @access public
	 */
	public $os;
	
	/**
	 * Get IP address used.
	 *
	 * @since 1.0
	 * @var string
	 * @access public
	 */
	public $ip;
	
	/**
	 * Get browser used.
	 *
	 * @since 1.0
	 * @var string
	 * @access public
	 */
	public $browser;
	
	/**
	 * Get user agent used.
	 *
	 * @since 1.0
	 * @var string
	 * @access public
	 */
	public $userAgent;
	
	/**
	 * Root folder.
	 *
	 * @since 1.0
	 * @var string
	 * @access public
	 */
	public $rootFolder = ABSPATH;
	
	/**
	 * Temporary folder.
	 *
	 * @since 1.0
	 * @var string
	 * @access public
	 */
	public $tempFolder;
	
	/**
	 * Upload folder.
	 *
	 * @since 1.0
	 * @var string
	 * @access public
	 */
	public $uploadFolder;
	
	/**
	 * Temporary folder categorize by date.
	 *
	 * @since 1.0
	 * @var string
	 * @access public
	 */
	private $tempDir;
	
	/**
	 * Constructor.
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @param array $data The data what has to be associated with this render.
	 */
	public function __construct( ) { 
		$this->userAgent = esc_html($_SERVER["HTTP_USER_AGENT"]);
		$this->browser = new GuiForm_Module_Browser();
		$this->browser->Browser();
	}
	
	public function init(){
		$this->ip            = $this->_ip();
		$this->os            = $this->_os();
		$this->mobile        = $this->_mobile();
		$this->desktop       = $this->_desktop();
		$this->permalink     = $this->getOption('permalink')->value;
		$this->uploadFolder  = $this->_uploadFolder();
		$this->tempFolder    = $this->_tempFolder();
		$this->tempDir       = $this->tempFolder.'/'.date('Y').'/'.date('n').'/'.date('j').'/';
	}
	
	public function assets($path = '', $abspath = false, $plugin = GuiForm_Plugin::NAME){
		return self::_resource('assets', $path, $abspath, $plugin);
	}
	
	public function vendor($path = '', $abspath = false){
		return self::_resource('vendor', $path, $abspath);
	}
	
	public function wpIncludes($path = '', $abspath = false){
		return self::_resource('wp-includes', $path, $abspath);
	}
	
	private function _resource($type, $path = '', $abspath = false, $plugin = GuiForm_Plugin::NAME){
		if($type == 'wp-includes'){
			return includes_url($path);
		}
		else{
			if(empty($path) || $path == false)
				return ($abspath) ? GUIFORM_ABSPATH . $type .'/' : plugins_url($plugin .'/'. $type .'/');
			else
				return ($abspath) ? GUIFORM_ABSPATH . $type .'/'. $path : plugins_url($plugin .'/'. $type .'/'. $path);
		}
	}
	
	public function trimAll( $str , $what = NULL , $with = '' ){
    if( $what === NULL ){
    	
        //  Character      Decimal      Use
        //  "\0"            0           Null Character
        //  "\t"            9           Tab
        //  "\n"           10           New line
        //  "\x0B"         11           Vertical Tab
        //  "\r"           13           New Line in Mac
        //  " "            32           Space
       
        $what   = "\\x00-\\x20";    //all white-spaces and control chars
    }
   
    return trim( preg_replace( "/[".$what."]+/" , $with , $str ) , $what );
  }

	/**
	 * Check user IP address.
	 *
	 * @since 1.0
	 * @access private
	 * @return $ipaddress string
	 */
	private function _ip(){
		$ipaddress = '';
		if (getenv('HTTP_CLIENT_IP'))
		   $ipaddress = getenv('HTTP_CLIENT_IP');
		else if(getenv('HTTP_X_FORWARDED_FOR'))
		   $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
		else if(getenv('HTTP_X_FORWARDED'))
		   $ipaddress = getenv('HTTP_X_FORWARDED');
		else if(getenv('HTTP_FORWARDED_FOR'))
		   $ipaddress = getenv('HTTP_FORWARDED_FOR');
		else if(getenv('HTTP_FORWARDED'))
		  $ipaddress = getenv('HTTP_FORWARDED');
		else if(getenv('REMOTE_ADDR'))
		   $ipaddress = getenv('REMOTE_ADDR');
		else
		   $ipaddress = 'UNKNOWN';
		
		return $ipaddress; 
	}
	
	/**
	 * Check browser name.
	 *
	 * @since 1.0
	 * @access private
	 * @return #browser string
	 */
	private function _browser(){
		$browser        =   "Unknown Browser";
    $browser_array  =   array (
        '/msie/i'       => 'Internet Explorer',
        '/firefox/i'    => 'Firefox',
        '/safari/i'     => 'Safari',
        '/chrome/i'     => 'Chrome',
        '/opera/i'      => 'Opera',
        '/netscape/i'   => 'Netscape',
        '/maxthon/i'    => 'Maxthon',
        '/konqueror/i'  => 'Konqueror',
        '/mobile/i'     => 'Handheld Browser'
    );

    foreach($browser_array as $regex => $value){ 
    	if(preg_match($regex, $this->userAgent)) $browser = $value;
    }

    return $browser;
	}
	
	/**
	 * Check operating system.
	 *
	 * @since 1.0
	 * @access private
	 * @return $os string
	 */
	private function _os(){
		$os    =   'Unknown OS';
    $os_array       =   array (
		    '/wpdesktop/i'          => 'Windows Phone',
		    '/zunewp7/i'            => 'Windows Phone',
   			'/windows phone/i'      => 'Windows Phone',
        '/windows nt 6.2/i'     => 'Windows 8',
        '/windows nt 6.1/i'     => 'Windows 7',
        '/windows nt 6.0/i'     => 'Windows Vista',
        '/windows nt 5.2/i'     => 'Windows Server 2003/XP x64',
        '/windows nt 5.1/i'     => 'Windows XP',
        '/windows xp/i'         => 'Windows XP',
        '/windows nt 5.0/i'     => 'Windows 2000',
        '/windows me/i'         => 'Windows ME',
        '/win98/i'              => 'Windows 98',
        '/win95/i'              => 'Windows 95',
        '/win16/i'              => 'Windows 3.11',
        '/macintosh|mac os x/i' => 'Mac OS X',
        '/mac_powerpc/i'        => 'Mac OS 9',
        '/linux/i'              => 'Linux',
        '/ubuntu/i'             => 'Ubuntu',
        '/iphone/i'             => 'iPhone',
        '/ipod/i'               => 'iPod',
        '/ipad/i'               => 'iPad',
        '/android/i'            => 'Android',
        '/blackberry/i'         => 'BlackBerry',
        '/webos/i'              => 'Mobile',
        '/gecko\/18.0/i'        => 'Firefox OS'
    );

    foreach($os_array as $regex => $value){ 
    	if(preg_match($regex, $this->userAgent)) $os = $value;
    }   

    return $os;
	}
	
	/**
	 * List of desktop operating system.
	 *
	 * @since 1.0.1
	 * @access private
	 * @return array
	 */
	private function _desktop(){
    return array ('Unknown OS',
							    'Windows 8',
							    'Windows 7',
							    'Windows Vista',
							    'Windows Server 2003/XP x64',
							    'Windows XP',
							    'Windows 2000',
							    'Windows ME',
							    'Windows 98',
							    'Windows 95',
							    'Windows 3.11',
							    'Mac OS X',
							    'Mac OS 9',
							    'Linux',
							    'Ubuntu'
							    );
	}
	
	/**
	 * List of moble device operating system.
	 *
	 * @since 1.0.1
	 * @access private
	 * @return array
	 */
	private function _mobile(){
    return array('Unknown OS',
								 'Windows Phone',
								 'iPhone',
								 'iPod',
								 'iPad',
							   'Android',
								 'BlackBerry',
								 'Mobile',
								 'Firefox OS'
								 );
	}

  /**
	 * Check value if serialize array.
	 *
	 * @since 1.0
	 * @access public
	 * @return string
	 */
	public function isSerial($string) {
    return (@unserialize($string) !== false || $string == 'b:0;');
	}
	
	/**
	 * Return verified email list.
	 *
	 * @since 1.0.2
	 * @access public
	 */
	public function get_mail(){
		global $wpdb;
		$result = $wpdb->get_results("SELECT name, value FROM $wpdb->guiform_options WHERE type = 'mail' ORDER BY name ASC");
		$emails = array();
		
		foreach($result as $row){
			$value = unserialize($row->value);
			if($value['status'] == 1){
				$emails[] = array('name' => $row->name);
			}
		}
		
		return $emails;
	}
	
	/**
	 * Copy all folders and files.
	 *
	 * @since 1.0.2
	 * @access public
	 */
	public function fileTransfer($source, $dest, $permissions = 0755){
	 	    // Check for symlinks
    if (is_link($source)) {
        return symlink(readlink($source), $dest);
    }

    // Simple copy for a file
    if (is_file($source)) {
        return copy($source, $dest);
    }

    // Make destination directory
    if (!is_dir($dest)) {
        mkdir($dest, $permissions);
    }

    // Loop through the folder
    $dir = dir($source);
    while (false !== $entry = $dir->read()) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        // Deep copy directories
        self::fileTransfer("$source/$entry", "$dest/$entry");
    }

    // Clean up
    $dir->close();
    return true;
	}
	
	
  /**
	 * Add options settings.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function addOption($name, $value, $type){
		global $wpdb;
		
		$value = (is_array($value)) ? serialize(array_map('esc_sql', $value)) : esc_html(trim($value));
		$wpdb->insert($wpdb->guiform_options, 
	  					 array('type' => $type, 
				  					 'name' => $name, 
				  					 'value' => $value), 
	  					 array('%s', '%s', '%s')
    				 ); 
	}
	
	/**
	 * Get options settings.
	 *
	 * @since 1.0
	 * @access public
	 * @return $option array
	 */
	public function getOption($name, $id = false, $type = false){
		global $wpdb;
		
		if($id == false && empty($name)) return false;
		
		if($id == false){
			$key = 'name';
			$value = esc_sql($name);
			$esc = '%s';
		}
		else{
			$key = 'id';
			$value = $id;
			$esc = '%d';
		}
		
		if($type != false){
			$option = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->guiform_options WHERE name = '%s' AND type = '%s'", array($name, $type)), OBJECT);
		}
		else{
			$option = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->guiform_options WHERE $key = $esc", $value), OBJECT);
		}
		
		if(count($option) == 1){
			$option[0]->value = self::isSerial($option[0]->value) ? unserialize($option[0]->value) : $option[0]->value;
			return $option[0];
		}		
		elseif(count($option) > 1){
			return $option;
		}	
		else{
			return false;
		}	
		
	}
	
	/**
	 * Get options settings.
	 *
	 * @since 1.0
	 * @access public
	 * @return $option array
	 */
	public function getOptions($type, $key = "*"){
		global $wpdb;
		$option = $wpdb->get_results($wpdb->prepare("SELECT $key FROM $wpdb->guiform_options WHERE type = '%s'", $type), OBJECT);
		
		if(sizeof($option) && $key != "value"){
			return $option;
		}	
		else if(sizeof($option) && $key == "value"){
			return array_map(array(self, 'unserializeArray'), $option);
		}
		else{
			return array();
		}	
	}
	
	public function getFormSettings($name){
		global $wpdb;
		return $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->guiform_options WHERE name = '%s'", $name), OBJECT);
	}
	
	public function unserializeArray($value){
		return self::isSerial($value->value) ? unserialize($value->value) : $value->value;
	}
	
	public function formatBytes($size){
		return $size * pow(1024, 2);
	}
	
	public function bytesToSize($bytes){
		$sizes = array('Bytes', 'KB', 'MB', 'GB', 'TB');
		if ($bytes == 0) return '';
		$i = intval(floor(log($bytes) / log(1024)));
		if ($i == 0) return $bytes . ' ' . $sizes[$i]; 
		return round(($bytes / pow(1024, $i)),1,PHP_ROUND_HALF_UP). ' ' . $sizes[$i];
	}
	
	/**
	 * Check if option name is existing.
	 *
	 * @since 1.0
	 * @access public
	 * @return string
	 */
	public function unique_option($key, $field, $type){ 
  	global $wpdb;
  	if(count($wpdb->get_row("SELECT name FROM $wpdb->guiform_options WHERE name = '$key' AND type = '$type'", ARRAY_N)) > 0){
  		return "$key is already exist.";
  	}
  }
	
	/**
	 * Update option setting.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function updateOption($name, $data, $type = false, $id = false){
		global $wpdb;
		
		
		if($type == 'settings' || $type == 'confirmation' || $type == 'smtp' || $type == 'mailchimp' || $type == 'drive'){
			if($wpdb->get_var("SELECT COUNT(*) FROM $wpdb->guiform_options WHERE name = '$name' AND type = '$type'") < 1){
				self::addOption($name, $data, $type);
				return false;
			}
		}
		
		$data = (is_array($data)) ? serialize(array_map('esc_sql', $data)) : $data;
		
		if($id){
			$wpdb->update($wpdb->guiform_options, 
									array('type' => $type,
												'name' => $name,
												'value' => $data),
									array('id' => $id),
									array('%s', '%s', '%s'),
									array('%d')
									);
		}
		else if($type == 'confirmation' || $type == 'settings' || $type == 'smtp' || $type == 'mailchimp' || $type == 'drive'){
			$wpdb->update($wpdb->guiform_options, 
									array('type' => $type,
												'name' => $name,
												'value' => $data),
									array('name' => $name, 'type' => $type),
									array('%s', '%s', '%s'),
									array('%s', '%s')
									);
		}
		
		//Update variables.				
		self::init();
	}
	
	/**
	 * Get the URL of the form.
	 *
	 * @since 1.0
	 * @access public
	 * @return $link string
	 */
	public function permalink($id, $_atts = array()){
		
		$query = array('_atts' => $_atts, '_param' => $_REQUEST);
		
		if(get_option('permalink_structure')){
			$and = "/";
			$con = "?";
  		$link = get_site_url() ."/". $this->permalink['value'];
  	}
  	else{
  		$and = "=";
  		$con = "&";
  		$link = get_site_url() ."/?". $this->permalink['value'];
  	}
  	
  	if($id == 0){
			return $link.$and;
		}
		else{
  		return (strpos($id, 'js/') !== false) ? $link.$and.$id : $link.$and.$id.$con.http_build_query($query);
  	}
	}
	
	/**
	 * Upload folder.
	 *
	 * @since 1.0
	 * @access public
	 * @return $url string
	 */
	public function uploadFolder($file = "", $path = "", $link = false){
		$url = ($link) ? get_site_url() .'/'. self::getOption('upload_folder')->value : $this->rootFolder .'/'. self::getOption('upload_folder')->value;
		return $url .'/'. $path.'/'.$file;
	}
	
	/**
	 * Check upload folder setting.
	 *
	 * @since 1.0
	 * @access private
	 * @return string
	 */
	private function _uploadFolder(){
		return $this->rootFolder.self::getOption('upload_folder')->value;
	}
	
	/**
	 * Temporary folder.
	 *
	 * @since 1.0
	 * @access private
	 * @return string
	 */
	private function _tempFolder(){
		return $this->rootFolder ."temp";
	}
	
	/**
	 * Temporary file directory.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function tempFileUpload($file){
		file_put_contents(
			self::tempDir($file),
			file_get_contents('php://input')
		);
	}
	
	/**
	 * Temporary file directory.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function tempDir($file){
		
		$dir = $this->tempFolder;
		$upload_dir = $this->uploadFolder;
		$day = explode('-', $file);
		
		$var = array(
						date('Y'), // Year
						date('n'), // Month
						$day[0]       // day
					 );
					 
		if(!file_exists($dir)){
			mkdir($dir);
		}
					 
		foreach($var as $folder){
			$dir = $dir.'/'.$folder;
			if(!file_exists($dir)){
				mkdir($dir);
			}
		}
		
		self::createUploadDir();
		
		return $dir .'/'. $file;
	}
	
	public function createUploadDir(){
		
    $upload_dir = $this->uploadFolder;
    
    if(!file_exists($upload_dir)){
			mkdir($upload_dir);
		}
    
		$var = array(
						date('Y'), // Year
						date('n'), // Month
						date('j')  // day
					 );
					 
		$dir = implode("/", $var);	
		if(!file_exists($upload_dir.'/'.$dir)){				
			foreach($var as $folder){
				$upload_dir = $upload_dir.'/'.$folder;
				if(!file_exists($upload_dir)){
					mkdir($upload_dir);
				}
			}
		}
	}
	
	/**
	 * Check database server setting.
	 *
	 * @since 1.0
	 * @access public
	 * @return array
	 */
	public function server($form){
		$db = self::database($form);
		if($db == 0)
			return array('host' => __('localhost', 'guiform'));
		else
			return unserialize(self::getOption($db));
	}
	
	/**
	 * Check database setting.
	 *
	 * @since 1.0
	 * @access public
	 * @return $db array
	 */
	public function database($id){
		global $wpdb;
		$db =  $wpdb->get_var($wpdb->prepare("SELECT `database` FROM $wpdb->guiform WHERE id = %d", $id), 0);
		return !empty($db) ? $db : 0; 
	}
	
	/**
	 * Get form entry result.
	 *
	 * @since 1.0
	 * @access public
	 * @return $entry array
	 */
	public function entry($form, $entry){
		global $wpdb;
		if(!empty($form) && !empty($entry)){
  		$entry = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->guiform_form$form WHERE id = %d", $entry), ARRAY_A);
			return $entry;
		}
	}
	
	/**
	 * Get form datas.
	 *
	 * @since 1.0
	 * @access public
	 * @return $db array
	 */
	public function form($id, $type = "", $key = array()){
		global $wpdb;
		
		$key = (sizeof($key)) ? implode(', ', $key) : '*';
		
		if(empty($type))
			return $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->guiform WHERE id = %d", $id), OBJECT);
  	else if($type == "row")
  		return $wpdb->get_results("SELECT $key FROM $wpdb->guiform");
  	else if($type == "data")
  		return unserialize($wpdb->get_var($wpdb->prepare("SELECT `data` FROM $wpdb->guiform WHERE id = %d", $id)));
  	else if($type == "entry")
  		return unserialize($wpdb->get_var($wpdb->prepare("SELECT `entry_field` FROM $wpdb->guiform WHERE id = %d", $id)));
  	else if($type == "init")
  		return unserialize($wpdb->get_var($wpdb->prepare("SELECT `init` FROM $wpdb->guiform WHERE id = %d", $id)));
  }
  
  public function printForm($atts = array()){
  	return GuiForm_Module_Frontend::renderShortCode($atts);
  }
  
}