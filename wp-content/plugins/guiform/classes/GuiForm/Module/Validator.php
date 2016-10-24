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
class GuiForm_Module_Validator extends GuiForm_Module {
	
	const NAME = __CLASS__;

	private $_id;
	
	private $_form;
	
	private $_data;
	
	private $_randID;
	
	private $_feedbackIcons;
	
	private $_vars = array();

	/**
	 * Constructor.
	 *
	 * @since 1.0.3
	 *
	 * @access public
	 * @param GuiForm_Plugin $plugin The instance of the plugin.
	 */
	public function __construct(GuiForm_Plugin $plugin){
		parent::__construct($plugin);	
	}
	
	public function init($id, $formId, $form){
		$this->_id = $id;
		
		$this->_randID = $formId;
		
		$this->_feedbackIcons = self::_feedbackIcons();
		$this->_form = $form;
		$this->_data = unserialize($this->_form->data);
	}
	
	private function _jsonEncode($results) {
		nocache_headers();
		return json_encode( (object) $results);
	}
	
	private function _feedbackIcons(){
		return array( 'valid'      => 'fa fa-check',
			            'invalid'    => 'fa fa-times',
			            'validating' => 'fa fa-refresh',
			            'required'   => 'fa fa-asterisk');
	}
	
	public function object(){
		$data                  = array();
		$data['id']            = $this->_id;
		$data['feedbackIcons'] = self::_feedbackIcons();
		$data['fields']        = self::_fields();
		$data['ajax_url']      = admin_url("admin-ajax.php");
		$data['nonce']         = wp_create_nonce( "guiform_nonce" );
		return self::_jsonEncode($data);
	}
	
	public function _fields(){
		$fields = array();
		
		if(sizeof($this->_data) && is_array($this->_data)){
			
			foreach($this->_data as $key => $value){
				
				$validators = array();
				$format = $value['validation']['format'];
				
				if($value['validation']['required']['enable']){
					$validators['notEmpty'] = array('message' => $value['validation']['required']['text']);
				}
				
				if($value['validation']['format']['enable']){
					$type = $format['type'];
					$validators[$type] = array('message' => $format['text']);
				}
				
				if($value['type'] == 'f_password' && $value['validation']['match']['enable']){
					$validators['identical'] = array(
						'field'   => $value['validation']['match']['field'],
						'message' => $value['validation']['match']['text']
					);
				}
				
				if($value['validation']['distinct']['enable']){
					$validators['remote'] = 
					array(
	          'type'    => 'POST',
	          'url'     => admin_url('admin-ajax.php'),
	          'message' => $value['validation']['distinct']['text'],
	          'delay'   => '1000',
	          'data'    => array(
	          	'nonce' => wp_create_nonce( "guiform_nonce" ),
	          	'id'    => $this->_id,
	          	'name'  => $value['name'], 
	          	'action' => 'guiform-tools-validate-distinct'
          	)
          );
				}
						
				if($value['type'] == 'f_phone'){
					$validators[$format['type']]['country'] = $format['country'];
				}
				
				if($value['type'] == 'f_file'){
					$validators['file'] = array('extension' => $value['validation']['extension']['type'],
																			'type'      => $value['validation']['extension']['mime'],
																			'message'   => $value['validation']['extension']['text']);
				}
				
				$validators['type'] = $value['type'];
				
				$fields[$value['name']] = array(
					'validators' => $validators,
					'excluded' => false
				);
				
			}
			
			return $fields;
		}
		
	}
	
	public function printJS($wrapper = true){
		return self::_build();
	}
		
	private function _build(){
		
		global $guiform;
		
		$data =  self::object();
		
		$output = '<script type="text/javascript">'."\n";
		$output .= 'jQuery(document).ready(function() { ' ."\n";
		$output .= "jQuery('form.guiform[id=\"".  $this->_randID ."\"]').guiform(". $data .");\n";
		$output .= '});'."\n";
		$output .= '</script>'."\n";
		
		return $output;
	}
	
}