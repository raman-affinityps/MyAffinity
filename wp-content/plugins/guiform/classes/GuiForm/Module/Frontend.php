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
 * Frontend module class.
 *
 * @category GuiForm
 * @package Module
 *
 * @since 1.0
 */
class GuiForm_Module_Frontend extends GuiForm_Module {

	const NAME = __CLASS__;

	private $_id;
	
	private $_form;
	
	private $_atts = array();
	
	private $_data = array();
	
	public $_randID;

	/**
	 * Constructor.
	 *
	 * @since 1.0
	 * @uses add_filter() To add "do_shortcode" hook for "widget_text" and "term_description" filters.
	 *
	 * @access public
	 * @param GuiForm_Plugin $plugin The instance of the plugin.
	 */
	public function __construct(){
		parent::__construct(GuiForm_Plugin::instance());
		
		session_start();
		
		if(isset($_REQUEST['mv-code'])) self::_activateMail();		
		
		self::_accessControl();
    $this->_addAction('generate_rewrite_rules', 'rewriteRules');
    $this->_addAction('parse_request', 'parseRequest');
		$this->_addFilter('query_vars', 'filterQueryVars');
    $this->_addShortcode('GuiForm', 'renderShortCode');
    $this->_addFilter('widget_text', 'doShortCode');
    $this->_addAction( 'wp_footer', 'inlineScripts', 9999 );
			
	}
	
	public function inlineScripts(){
		if(!empty($this->_form)){
			$validator = new GuiForm_Module_Validator(GuiForm_Plugin::instance());
			foreach($this->_data as $key => $row){
				$validator->init($row["form"]->id, $row["element"], $row["form"]);
				echo $validator->printJS();
			}
		}
	}
	
	public function doShortCode($atts){
		return do_shortcode($atts);
	}
	
	private function _activateMail(){
		global $guiform, $wpdb;
		$mv_code = esc_sql(trim($_GET['mv-code']));
		$id = $_REQUEST[$guiform->getOption('permalink')->value['value']];
		$data = $wpdb->get_row($wpdb->prepare("SELECT name, value FROM $wpdb->guiform_options WHERE id = %d", $id));
		$row = unserialize($data->value);
	
		if($row['key'] == $mv_code){
			$row['key'] = '';
			$row['status'] = 1;
			$guiform->updateOption($data->name, $row, 'mail', $id);
			$blog_id = get_current_blog_id();
			$path = "admin.php?page=guiform-settings&tab=mail";
			$path = get_admin_url( $blog_id, $path);
			
			$html = "<strong>Congratulations!</strong><br /><br />
							You can now use ". $data->name ." for sending confirmation message with your form.<br /><br />
							<a href=\"$path\">Click here to continue.</a><br /><br />";
			echo GuiForm_Module_Ajax::emailTpl($html);
		}
		else{
			$blog_id = get_current_blog_id();
			$path = "admin.php?page=guiform-settings&tab=mail";
			$path = get_admin_url( $blog_id, $path);
			
			$html = "<strong>Invalid Code!</strong><br /><br />
							You entered invalid email verification code. Please check your email or send a new verification code request.<br /><br />
							<a href=\"$path\">Click here to continue.</a><br /><br />";
			echo GuiForm_Module_Ajax::emailTpl($html);
		}
		
		die();
	}
	
	public function renderShortCode($atts){
		
		global $guiform;
		$this->_atts = $atts;
		
		$this->_randID = 'GuiForm_' .rand();
		$this->_id = $this->_atts['id'];
		$this->_form = $guiform->form($this->_atts['id']);
		
		$this->_data[$this->_randID] = array(
			'element' => $this->_randID,
			'form' => $this->_form
		);
		
		if(!$this->_form->id) return;
		
		if(is_admin() && $this->_form->display == 'frontend'){
			return;
		}
		else if(!is_admin()){
			if($this->_form->display == 'admin'){
				return;
			}
		}
		
    if(!isset($this->_atts['iframe']) || filter_var($this->_atts['iframe'], FILTER_VALIDATE_BOOLEAN )){
    	return self::renderScript();
    }
    else{
    	return self::renderForm();
    }
		
	}
	
	/**
	 * Add URL rewrite for form preview.
	 *
	 * @since 1.0
	 * @access public
	 */    
	public function rewriteRules($wp_rewrite){
		global $guiform;
	  $new_rules = array($guiform->permalink['value'] ."/(.+)" => "index.php?". $guiform->permalink['value'] ."=". $wp_rewrite->preg_index(1));	
	  $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
	}
	
	/**
	 * filter slugs.
	 *
	 * @since 1.0
	 * @access public
	 * @return $query_vars array
	 */
	public function filterQueryVars($query_vars) {
		global $guiform;
	  $query_vars[] = $guiform->permalink['value'];
	  return $query_vars;
	}
	
	private function _accessControl(){
		if(isset($_SERVER['HTTP_ORIGIN'])){
			header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
			header('Access-Control-Allow-Credentials: true');
			header('Access-Control-Max-Age: 86400');    // cache for 1 day
		}
	}
	
	public function parseRequest(&$wp){
		global $wpdb, $guiform, $wp;
		
		GuiForm_Module_Setup::flushRewrite();
		
		$query = $wp->query_vars[$guiform->permalink['value']];
		
		$this->_id = (strpos($query, 'js') !== false) ? preg_replace('/\D/', '', $query) : $query;
		
		if( array_key_exists( $guiform->permalink['value'], $wp->query_vars ) || in_array( $guiform->permalink['value'], $wp->query_vars ) ) {
	    
	    $this->_atts = $_REQUEST['_atts'];
	    
			$this->_form = $guiform->form($this->_id);
					   
			if (strpos($query, 'js') !== false){
				$this->_atts['js'] = true;
				header("content-type: application/javascript");
				echo self::renderScript();
			}
			else{
				$this->_atts['js'] = false;
				echo self::renderForm();
			}
			
	  	die();
	  }
		
	}
	
	public function renderForm(){
		global $guiform;
		
		
		if(is_admin() && $this->_form->display == 'frontend'){
			return;
		}
		else if(!is_admin()){
			if(current_user_can('manage_guiform')){
				//Display form.
			}
			else if($this->_form->display == 'admin'){
				return;
			}
		}
		
		
		if(!isset($this->_atts['iframe']) || filter_var($this->_atts['iframe'], FILTER_VALIDATE_BOOLEAN )){
		
		?>
		<!DOCTYPE html>
		<html>
		<head>
		<title><?php echo $this->_form->title; ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="HandheldFriendly" content="true">
		
		<link rel="stylesheet" id="wp-tinymce-css" href="<?php echo includes_url('js/tinymce/skins/lightgray/content.min.css'); ?>" type="text/css" media="all" />			
		<link rel="stylesheet" href="<?php echo $guiform->vendor('jquery-ui/css/jquery-ui.min.css'); ?>" type="text/css" media="all" />
		<link rel="stylesheet" href="<?php echo $guiform->vendor('bootstrap/css/custom-bootstrap.min.css'); ?>" type="text/css" media="all" />			
		<link rel="stylesheet" href="<?php echo $guiform->vendor('bootstrap/css/bootstrap-theme.min.css'); ?>" type="text/css" media="all" />			
		<link rel="stylesheet" href="<?php echo $guiform->vendor('formvalidation/css/formValidation.min.css'); ?>" type="text/css" media="all" />			
		<link rel="stylesheet" href="<?php echo $guiform->vendor('font-awesome/css/font-awesome.min.css'); ?>" type="text/css" media="all" />					
		<link rel="stylesheet" href="<?php echo $guiform->assets('css/guiform.css?ver='. GuiForm_Plugin::VERSION); ?>" type="text/css" media="all" />			
		
		</head>
		<body id="GuiForm" <?php echo (isset($_GET['preview'])) ? " style='padding-top: 35px;'" : ''; ?>>
		
		<?php
			} 
			else{
				ob_start();	
			}
			
		?>
		
		<?php if(!empty($this->_form->html)): ?>
			<form enctype="multipart/form-data" action="javascript:return false;" class="form-horizontal guiform" id="<?php echo $this->_randID; ?>" method="POST"  >	
				<input type="hidden" value="<?php echo $this->_randID; ?>" name="guiform-random-id">
				<input type="hidden" value="guiform-save-entry" name="action">
				
				<?php
				
				  wp_nonce_field('guiform_nonce','nonce');
				  
					$content = $this->_form->html;
				  $styles = array();
				  $count = 0;
				  preg_match_all('/<style.*?\>(.*?)<\/style>/si', $content, $matches);
					foreach($matches[1] as $string){
						$styles['guiform_replacement_style_'. $count] = $string;
						$content = str_replace($string, 'guiform_replacement_style_'. $count++, $content);
					}
							
					if(has_filter('guiform_render_form')){
						$content = apply_filters('guiform_render_form', $content);
					}
					
					foreach($styles as $key => $style){
						$content = str_replace($key, $style, $content);
					}
					
					echo $content;				
				
				?>
			</form>
			
		<?php endif; ?>
		
		<?php if(!isset($this->_atts['iframe']) || filter_var($this->_atts['iframe'], FILTER_VALIDATE_BOOLEAN )){ ?>
		
		<?php self::footerScript();	?>
		
		</body>
		</html>
		<?php 
			}
			else{
				$view = ob_get_contents();
				ob_end_clean();
				return $view;
			}
		?>
		<?php
	}
	
	private function _getFormContent(){
		ob_start();
		self::renderForm();
		$view = ob_get_contents();
		ob_end_clean();
		
		global $guiform;
		
		$content = $view;
		$content = str_replace('"', '\\"', $content);
		$content = str_replace('/', '\/', $content);
		$content = preg_replace("/[\\n\\r]+/", "", $content);
		$content = preg_replace("/[\\t]+/", "", $content);
		$content = str_replace('> <', '><', $content);
		$content = "$content";
		
		return $content;
	}
	
	public function renderScript(){
		global $guiform;
	
		if(is_admin() && $this->_form->display == 'frontend'){
			return;
		}
		else if(!is_admin()){
			if(current_user_can('manage_guiform') && ($this->_form->display == 'admin' && $this->_atts['js'] == false)){
				//Display form.
			}
			else if($this->_form->display == 'admin'){
				return;
			}
		}
		
		ob_start();
		
		if($this->_atts['js'] == false) echo '<script type="text/javascript">';
		
		?>
/*!
 * GuiForm Plugin
 * https://www.guiform.com
 *
 * Author: Russell Pabon
 * http://russellpabon.com
 *
 */
 
var isOpera = !!window.opera || navigator.userAgent.indexOf(' OPR/') >= 0;
    // Opera 8.0+ (UA detection to detect Blink/v8-powered Opera)
var isFirefox = typeof InstallTrigger !== 'undefined';   // Firefox 1.0+
var isSafari = Object.prototype.toString.call(window.HTMLElement).indexOf('Constructor') > 0;
    // At least Safari 3+: "[object HTMLElementConstructor]"
var isChrome = !!window.chrome && !isOpera;              // Chrome 1+
var isIE = /*@cc_on!@*/false || !!document.documentMode; // At least IE6
 
function GuiForm(){

	this.Id = '';
	this.iframe = null;
	this.content = "";
	this.timeInterval = 100;
	
	this.init = function(){
		this.buidFrame();
	};
	
	this.setTimer = function(){
    var self = this;
    this.iframe = document.getElementById(this.Id);
    this.interval = setTimeout(function(){
    	self.resizeHeight();
    	self.resizeWidth();
    	self.setTimer();
    },this.timeInterval);
    this.timeInterval = 1;
  };
  
  this.resizeWidth = function(){
	  	var F = document.getElementById(this.Id);
	  	try{
		  	var maxWidth = F.contentWindow.document.getElementById("canvas").style.maxWidth;
		  	F.style.maxWidth = maxWidth;
		  }catch(e){ }     
  };
  
  this.resizeHeight = function(){
    var F = this.iframe;
    var height;
    
    try{
			if(F.contentWindow.document.height){
                
         height = F.contentWindow.document.height;
          
         if(F.contentWindow.document.body.scrollHeight){
             height = F.contentWindow.document.body.scrollHeight;
         }
          
         if(F.contentWindow.document.body.offsetHeight){
             height = F.contentWindow.document.body.offsetHeight;
         }
      }
      else if(F.contentWindow.document.body){
          
         if(F.contentWindow.document.body.scrollHeight){
              height = F.contentWindow.document.body.scrollHeight;
         }
          
         if(F.contentWindow.document.body.offsetHeight){
              height = F.contentWindow.document.body.offsetHeight;
         }
      }  
      
      F.style.height = (isSafari) ? height + 40 +'px' : height + 20 +'px';
      
    }catch(e){ }            
	}
	
	
	this.buidFrame = function(){
		this.Id = 'GuiForm-'+Math.floor(Math.random() * 90000);
    
    var preview =  document.getElementById('guiform-preview-frame');
		if (typeof(preview) != 'undefined' && preview != null)
		{
			var script = document.createElement('iframe');
			script.src = "<?php echo $guiform->permalink($this->_form->id, $this->_atts); ?>";
			script.id = this.Id;
			script.style.width = "100%";
			script.scrolling = "no";
			script.frameborder = "0";
			script.allowtransparency = "true";
			document.getElementById('guiform-preview-frame').appendChild(script);
		}
		else{
			var htmlCode = "<i"+"frame id=\""+this.Id+"\" src=\"<?php echo $guiform->permalink($this->_form->id, $this->_atts); ?>\" onload=\"window.parent.scrollTo(0,0)\" allowtransparency=\"true\" frameborder=\"0\" style=\"display: inline; height: auto; width: 100%; border:none;\" scrolling=\"no\"></i"+"frame>";
			document.write(htmlCode);
			this.iframe = document.getElementById(this.Id);
			this.iframe.style.height = 'auto';
		}
		
		this.setTimer();
	};
	
	this.init();
}

var guiform = new GuiForm();
		
		<?php
		if($this->_atts['js'] == false) echo '</script>';
		$view = ob_get_contents();
		ob_end_clean();
		return $view;
	}
	
	/**
	 * Enqueue footer jquery library.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function footerScript(){
		global $guiform;
		
		$prefix = (file_exists(ABSPATH ."wp-includes/js/jquery/ui/jquery.ui.effect.min.js" )) ? 'jquery.ui.' : '';
	
		?>
		
		<script src="<?php echo includes_url("js/jquery/jquery.js"); ?>" type="text/javascript"></script>
		<script src="<?php echo includes_url("js/jquery/jquery-migrate.min.js"); ?>" type="text/javascript"></script>
		<script src="<?php echo includes_url("js/jquery/jquery.ui.touch-punch.js"); ?>" type="text/javascript"></script>
		
		<script src="<?php echo includes_url("js/jquery/ui/". $prefix ."core.min.js"); ?>" type="text/javascript"></script>
		<script src="<?php echo includes_url("js/jquery/ui/". $prefix ."widget.min.js"); ?>" type="text/javascript"></script>
		<script src="<?php echo includes_url("js/jquery/ui/". $prefix ."mouse.min.js"); ?>" type="text/javascript"></script>
		<script src="<?php echo includes_url("js/jquery/ui/". $prefix ."effect.min.js"); ?>" type="text/javascript"></script>
		
		<script src="<?php echo includes_url("js/jquery/ui/". $prefix ."effect-slide.min.js"); ?>" type="text/javascript"></script>
		<script src="<?php echo includes_url("js/jquery/ui/". $prefix ."effect-highlight.min.js"); ?>" type="text/javascript"></script>
		<script src="<?php echo includes_url("js/jquery/ui/". $prefix ."spinner.min.js"); ?>" type="text/javascript"></script>
		<script src="<?php echo includes_url("js/jquery/ui/". $prefix ."button.min.js"); ?>" type="text/javascript"></script>
		
		<script src="<?php echo $guiform->vendor("formvalidation/js/formValidation.min.js"); ?>" type="text/javascript"></script>
		<script src="<?php echo $guiform->vendor("formvalidation/js/framework/bootstrap.min.js"); ?>" type="text/javascript"></script>
			
		<script src="<?php echo $guiform->assets("js/jquery.maskedinput.min.js"); ?>" type="text/javascript"></script>
		<script src="<?php echo $guiform->assets("js/guiform-form.min.js?ver=". GuiForm_Plugin::VERSION); ?>" type="text/javascript"></script>
		
		<?php
		$validator = new GuiForm_Module_Validator(GuiForm_Plugin::instance());
		$validator->init($this->_id, $this->_randID, $this->_form);
		echo $validator->printJS();
		
	}
}