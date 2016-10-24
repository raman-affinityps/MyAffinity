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
class GuiForm_Module_I18n extends WP_Widget {

	const NAME = __CLASS__;
	
	var $index;
	
	public function lang($js = 'guiform', $index = 0){
		
		$this->index = $index;
		
		if($js == 'guiform')
			return self::_guiform();
		else
			return self::_guifbox();
	}
	
	private function _guiform(){
		return array(
		 'menu'  => self::_menu(),
		 'tools' => self::_tools(),
		 'tool'  => self::_tool(),
		 'prop'  => self::_properties(),
		 'validation'  => self::_validation()
		);
	}
	
	private function _guifbox(){
		
		$text = array();
				
		return $text;
	}
	
  private function _menu(){
		return array(
			 'save'         => __('Save Form', GuiForm_Plugin::NAME),
			 'create'       => __('Create New Form', GuiForm_Plugin::NAME),
			 'preview'      => __('Form Preview', GuiForm_Plugin::NAME),
			 'style'        => __('Form Style', GuiForm_Plugin::NAME),
			 'required'     => __('Required', GuiForm_Plugin::NAME),
			 'delete'       => __('Remove Field', GuiForm_Plugin::NAME),
			 'properties'   => __('Field Properties', GuiForm_Plugin::NAME),
			 'code'         => __('Source Code', GuiForm_Plugin::NAME),
			 'confirmation' => __('Confirmation Message', GuiForm_Plugin::NAME),
			 'mail'         => __('Email Settings', GuiForm_Plugin::NAME)
		);
	}
	
	private function _tools(){
		return array(
			 __('Basic Tools', GuiForm_Plugin::NAME), 
			 __('Quick Tools', GuiForm_Plugin::NAME), 
			 __('Display Tools', GuiForm_Plugin::NAME), 
			 __('Custom Tools', GuiForm_Plugin::NAME),
			 __('Anti-Spam Tools', GuiForm_Plugin::NAME), 
			 __('Grid Layouts', GuiForm_Plugin::NAME)
		);
	}
	
	private function _tool(){
		return array(
			'text'          => array( 'name'  => __('Text Box', GuiForm_Plugin::NAME),
																'icon'  => 'fa-square-o',
																'label' => __('Text Label', GuiForm_Plugin::NAME),
																'text'  => array(''),
																'lock'  => false
															),
			'textarea'      => array( 'name'  => __('Text Area', GuiForm_Plugin::NAME),
																'icon'  => 'fa-square-o',
																'label' => __('Message', GuiForm_Plugin::NAME),
																'text'  => array(''),
																'lock'  => false 
															),
			'select'        => array( 'name'  => __('Select Box', GuiForm_Plugin::NAME),
																'icon'  => 'fa-caret-square-o-down',
																'label' => __('Text Label', GuiForm_Plugin::NAME),
																'text'  => array(''),
																'lock'  => false
															),
			'radio'         => array( 'name'  => __('Radio Button', GuiForm_Plugin::NAME),
																'icon'  => 'fa-dot-circle-o',
																'label' => __('Text Label', GuiForm_Plugin::NAME),
																'text'  => array(''),
																'lock'  => false
															),
			'checkbox'      => array( 'name'  => __('Check Box', GuiForm_Plugin::NAME),
																'icon'  => 'fa-check-square-o',
																'label' => __('Text Label', GuiForm_Plugin::NAME),
																'text'  => array(''),
																'lock'  => false
															),
			'file'          => array( 'name'  => __('File Upload', GuiForm_Plugin::NAME),
																'icon'  => 'fa-upload',
																'label' => __('Text Label', GuiForm_Plugin::NAME),
																'text'  => array('fa-paperclip'),
																'lock'  => false
															),
			'password'      => array( 'name'  => __('Password', GuiForm_Plugin::NAME),
																'icon'  => 'fa-key',
																'label' => __('Text Label', GuiForm_Plugin::NAME),
																'text'  => array(''),
																'lock'  => false
															),
			'hidden_textbox' => array('name'  => __('Hidden Textbox', GuiForm_Plugin::NAME),
																'icon'  => 'fa-eye-slash',
																'label' => __('Hidden Text', GuiForm_Plugin::NAME),
																'text'  => array(''),
																'lock'  => true
															),
			'submit'        => array( 'name'  => __('Submit Button', GuiForm_Plugin::NAME),
																'icon'  => 'fa-thumbs-o-up',
																'label' => __('Submit', GuiForm_Plugin::NAME),
																'text'  => array(''),
																'lock'  => false
															),
			'phone'         => array( 'name'  => __('Phone Number', GuiForm_Plugin::NAME),
																'icon'  => 'fa-phone',
																'label' => __('Phone Number', GuiForm_Plugin::NAME),
																'text'  => array(''),
																'lock'  => false
															),
			'email'         => array( 'name'  => __('Email Address', GuiForm_Plugin::NAME),
																'icon'  => 'fa-at',
																'label' => __('Email Address', GuiForm_Plugin::NAME),
																'text'  => array(''),
																'lock'  => false
															),
			'link'          => array( 'name'  => __('Link', GuiForm_Plugin::NAME),
																'icon'  => 'fa-link',
																'label' => __('Link', GuiForm_Plugin::NAME),
																'text'  => array(''),
																'lock'  => true
															),
			'spinner'       => array( 'name'  => __('Spinner', GuiForm_Plugin::NAME),
																'icon'  => 'fa-sort-numeric-desc',
																'label' => __('Text Label', GuiForm_Plugin::NAME),
																'text'  => array(''),
																'lock'  => false
															),
			'slider'        => array( 'name'  => __('Slider', GuiForm_Plugin::NAME),
																'icon'  => 'fa-sliders',
																'label' => __('Text Label', GuiForm_Plugin::NAME),
																'text'  => array(''),
																'lock'  => true
															),
			'switch'        => array( 'name'  => __('Switch Button', GuiForm_Plugin::NAME),
																'icon'  => 'fa-toggle-on',
																'label' => __('Text Label', GuiForm_Plugin::NAME),
																'text'  => array(''),
																'lock'  => true
															),
			'button'        => array( 'name'  => __('Button', GuiForm_Plugin::NAME),
																'icon'  => 'fa-circle-o',
																'label' => __('Text Label', GuiForm_Plugin::NAME),
																'text'  => array(''),
																'lock'  => false
															),
			'datetime_picker' => array( 'name'  => __('Date & Time Picker', GuiForm_Plugin::NAME),
																  'icon'  => 'fa-calendar',
																  'label' => __('Date', GuiForm_Plugin::NAME),
																  'text'  => array(''),
																'lock'  => true
															),
			'time_picker'   => array( 'name'  => __('Time Picker', GuiForm_Plugin::NAME),
																'icon'  => 'fa-clock-o',
																'label' => __('Time', GuiForm_Plugin::NAME),
																'text'  => array(''),
																'lock'  => true
															),
			'heading'       => array( 'name'  => __('Heading', GuiForm_Plugin::NAME),
																'icon'  => 'fa-header',
																'text'  => array(''),
																'lock'  => false
															),
			'html'          => array( 'name'  => __('HTML', GuiForm_Plugin::NAME),
																'icon'  => 'fa-code',
																'text'  => array(''),
																'lock'  => true
															),
			'paragraph'     => array( 'name'  => __('Paragraph Text', GuiForm_Plugin::NAME),
																'icon'  => 'fa-paragraph',
																'text'  => array(''),
																'lock'  => false
															),
			'list'          => array( 'name'  => __('List', GuiForm_Plugin::NAME),
																'icon'  => 'fa-list-ul',
																'text'  => array(''),
																'lock'  => false
															),
			'image'         => array( 'name'  => __('Image', GuiForm_Plugin::NAME),
																'icon'  => 'fa-image',
																'text'  => array(''),
																'lock'  => true
															),
			'divider'       => array( 'name'  => __('Divider', GuiForm_Plugin::NAME),
																'icon'  => 'fa-minus',
																'text'  => array(''),
																'lock'  => false
															),
			'panel'         => array( 'name'  => __('Panel', GuiForm_Plugin::NAME),
																'icon'  => 'fa-square-o',
																'text'  => array(''),
																'lock'  => true
															),
			'alert'         => array( 'name'  => __('Alert', GuiForm_Plugin::NAME),
																'icon'  => 'fa-warning',
																'text'  => array(''),
																'lock'  => true
															),
			'callout'       => array( 'name'  => __('Callout', GuiForm_Plugin::NAME),
																'icon'  => 'fa-bullhorn',
																'text'  => array(''),
																'lock'  => true
															),
			'folding'       => array( 'name'  => __('Folding', GuiForm_Plugin::NAME),
																'icon'  => 'fa-sort-desc',
																'text'  => array(''),
																'lock'  => true
															),
			'page_break'    => array( 'name'  => __('Page Break', GuiForm_Plugin::NAME),
																'icon'  => 'fa-share-square-o',
																'text'  => array(''),
																'lock'  => true
															),
			'exclude_next'  => array( 'name'  => __('Exclude Next', GuiForm_Plugin::NAME),
																'icon'  => 'fa-ellipsis-h',
																'text'  => array(''),
																'lock'  => true
															),
			'text_captcha'  => array( 'name'  => __('Text Captcha', GuiForm_Plugin::NAME),
																'icon'  => 'fa-refresh',
																'label' => __('Text Label', GuiForm_Plugin::NAME),
																'text'  => array(''),
																'lock'  => true
															),
			'recaptcha_v1'  => array( 'name'  => __('reCaptcha version 1.0', GuiForm_Plugin::NAME),
																'icon'  => 'fa-refresh',
																'label' => __('Text Label', GuiForm_Plugin::NAME),
																'text'  => array(''),
																'lock'  => true
															),
			'recaptcha_v2'  => array( 'name'  => __('reCaptcha version 2.0', GuiForm_Plugin::NAME),
																'icon'  => 'fa-refresh',
																'label' => __('Text Label', GuiForm_Plugin::NAME),
																'text'  => array(''),
																'lock'  => true
															),
			'grid_system_1' => array( 'name'  => __('1 Column', GuiForm_Plugin::NAME),
																'icon'  => 'fa-columns',
																'text'  => array(''),
																'lock'  => true
															),
			'grid_system_2' => array( 'name'  => __('2 Columns', GuiForm_Plugin::NAME),
																'icon'  => 'fa-columns',
																'text'  => array(''),
																'lock'  => true
															),
			'grid_system_3' => array( 'name'  => __('3 Columns', GuiForm_Plugin::NAME),
																'icon'  => 'fa-columns',
																'text'  => array(''),
																'lock'  => true
															),
			'grid_system_4' => array( 'name'  => __('4 Columns', GuiForm_Plugin::NAME),
																'icon'  => 'fa-columns',
																'text'  => array(''),
																'lock'  => true
															),
			'grid_system_5' => array( 'name'  => __('5 Columns', GuiForm_Plugin::NAME),
																'icon'  => 'fa-columns',
																'text'  => array(''),
																'lock'  => true
															),
			'grid_system_6' => array( 'name'  => __('6 Columns', GuiForm_Plugin::NAME),
																'icon'  => 'fa-columns',
																'text'  => array(''),
																'lock'  => true
															),
			'custom_textbox' => array( 'name'  => __('Text Box', GuiForm_Plugin::NAME),
																'icon'  => 'fa-user',
																'label' => __('Text Label', GuiForm_Plugin::NAME),
																'text'  => array(''),
																'lock'  => true
															),
			'custom_checkbox' => array( 'name'  => __('Checkbox', GuiForm_Plugin::NAME),
																'icon'  => 'fa-check-square-o',
																'label' => __('Text Label', GuiForm_Plugin::NAME),
																'text'  => array(''),
																'lock'  => true
															),
			'custom_radio'    => array( 'name'  => __('Radio Button', GuiForm_Plugin::NAME),
																'icon'  => 'fa-dot-circle-o',
																'label' => __('Text Label', GuiForm_Plugin::NAME),
																'text'  => array(''),
																'lock'  => true
															),																								
			'custom_password' => array( 'name'  => __('Password', GuiForm_Plugin::NAME),
																'icon'  => 'fa-key',
																'label' => __('Password', GuiForm_Plugin::NAME),
																'text'  => array(''),
																'lock'  => true
															),
			'custom_datetime_picker' => array( 'name'  => __('Date & Time Picker', GuiForm_Plugin::NAME),
																  'icon'  => 'fa-calendar',
																  'label' => __('Date', GuiForm_Plugin::NAME),
																  'text'  => array(''),
																'lock'  => true
															),
			'custom_time_picker'   => array( 'name'  => __('Time Picker', GuiForm_Plugin::NAME),
																'icon'  => 'fa-clock-o',
																'label' => __('Time', GuiForm_Plugin::NAME),
																'text'  => array(''),
																'lock'  => true
															),
			'custom_file'          => array( 'name'  => __('File Upload', GuiForm_Plugin::NAME),
																'icon'  => 'fa-upload',
																'label' => __('Text Label', GuiForm_Plugin::NAME),
																'text'  => array('fa-paperclip'),
																'lock'  => true
															)											
		);
	}
	
	private function _properties(){
		return array(
			'label_text'        => array('text'  => __('Text', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'label_position'    => array('text'  => __('Position', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'label_display'     => array('text'  => __('Display', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'display_switch_label' => array('text' => __('Display Text', GuiForm_Plugin::NAME),
																		  'desc' => ''
			),
			'label_width'       => array('text'  => __('Width', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'input_width'       => array('text'  => __('Width', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'input_height'      => array('text'  => __('Height', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'input_size'        => array('text'  => __('Size', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'submit_offset'     => array('text'  => __('Offset', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'maximum_characters' => array('text' => __('Maximum Characters', GuiForm_Plugin::NAME),
																		'desc' => ''
			),
			'column_number'     => array('text'  => __('Number of Columns', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'number_of_months'  => array('text'  => __('Number of Months', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'data_length'       => array('text'  => __('Length', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'data_type'         => array('text'  => __('Type', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'data_name'         => array('text'  => __('Name', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'data_default_value' => array('text' => __('Default Value', GuiForm_Plugin::NAME),
																		'desc' => ''
			),
			'default_value'     => array('text'  => __('Default Value', GuiForm_Plugin::NAME),
																	 'desc'  => __('Enclose string with {}.', GuiForm_Plugin::NAME),
																	 'note'  => __('Sample formats:<br>{Hello World!}<br>$_SERVER[{HTTP_REFERER}]<br>wp_get_current_user()->user_email<br>1 + 2 * (3 + 4)', GuiForm_Plugin::NAME)
			),
			'placeholder'       => array('text'  => __('Placeholder', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'security_error'    => array('text'  => __('Security Error', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'short_description' => array('text'  => __('Short Description', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'switch_on_label'   => array('text'  => __('On Label', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'switch_off_label'  => array('text'  => __('Off Label', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'validation_require' => array('text' => __('Required', GuiForm_Plugin::NAME),
																		'desc' => ''
			),
			'validation_text_length'  => array('text'  => __('Text Length', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'validation_format' => array('text'  => __('Validation Format', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'validation_phone'  => array('text'  => __('Validation Format', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'validation_email'  => array('text'  => __('Validation Format', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'validation_distinct' => array('text' => __('Distinct Record', GuiForm_Plugin::NAME),
																		 'desc' => __('Prevent duplicate value.', GuiForm_Plugin::NAME)
			),
			'item_id'           => array('text'  => __('Item ID', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'field_name'        => array('text'  => __('Field Name', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'multiple_upload'   => array('text'  => __('Multiple File Upload', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'file_size_limit'   => array('text'  => __('File Size Limit', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'password_match'    => array('text'  => __('Match With Another Password', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'submit_text'       => array('text'  => __('Submit Text', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'reset_text'        => array('text'  => __('Reset Text', GuiForm_Plugin::NAME),
																		'desc' => ''
			),
			'loading_text'      => array('text'  => __('Loading Text', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'submit_alignment'  => array('text'  => __('Alignment', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'text_content'      => array('text'  => __('Text Content', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'image_caption'     => array('text'  => __('Caption', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'heading'           => array('text'  => __('Heading', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'html_content'      => array('text'  => __('HTML Content', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'panel_title'       => array('text'  => __('Title', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'panel_theme'       => array('text'  => __('Theme', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'recaptchaV1_theme' => array('text'  => __('Theme', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'alert_content'     => array('text'  => __('Content', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'alert_theme'       => array('text'  => __('Theme', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'callout_theme'     => array('text'  => __('Theme', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'callout_content'   => array('text'  => __('Content', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'list_type'         => array('text'  => __('List Type', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'image_source'      => array('text'  => __('Image Source', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'target_link'       => array('text'  => __('Target Link', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'target_attribute'  => array('text'  => __('Target Attribute', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'border_settings'   => array('text'  => __('Border Settings', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'header_title'      => array('text'  => __('Title', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'input_mask'        => array('text'  => __('Input Mask', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'maximum_number'    => array('text'  => __('Maximum Number', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'minimum_number'    => array('text'  => __('Minimum Number', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'allow_empty'       => array('text'  => __('Allow Empty Value', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'date_format'       => array('text'  => __('Date Format', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'time_format'       => array('text'  => __('Time Format', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'time_settings'     => array('text'  => __('Settings', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'group_prefix'      => array('text'  => __('Prefix', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'group_postfix'     => array('text'  => __('Postfix', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			),
			'regexp'            => array('text'  => __('Regular Expression', GuiForm_Plugin::NAME),
																	 'desc'  => ''
			)
		);
	}
	
	private function _validation(){
		return array(
			'none' => array('text'  => __('', GuiForm_Plugin::NAME),
											'label' => __('Any', GuiForm_Plugin::NAME),
											'lock'  => false
			),
			'emailAddress' => array('text' => __('Please enter a valid email address', GuiForm_Plugin::NAME),
											'label' => __('Email', GuiForm_Plugin::NAME),
											'lock'  => false
			),
			'numeric' => array('text' => __('Please enter a valid number', GuiForm_Plugin::NAME),
											'label' => __('Numeric', GuiForm_Plugin::NAME),
											'lock'  => false
			),
			'digits' => array('text'  => __('Please enter only digits', GuiForm_Plugin::NAME),
											'label' => __('Digits', GuiForm_Plugin::NAME),
											'lock'  => false
			),
			'phone' => array('text' => __('Please enter a valid phone number', GuiForm_Plugin::NAME),
											'label' => __('Phone Number', GuiForm_Plugin::NAME),
											'lock'  => false
			),
			'uri'  => array('text'  => __('Please enter a valid URL address', GuiForm_Plugin::NAME),
											'label' => __('URL Address', GuiForm_Plugin::NAME),
											'lock'  => true
			),
			'ipv4' => array('text'  => __('Please enter a valid IPv4 address', GuiForm_Plugin::NAME),
											'label' => __('IPv4 Address', GuiForm_Plugin::NAME),
											'lock'  => true
			),
			'ipv6' => array('text'  => __('Please enter a valid IPv6 address', GuiForm_Plugin::NAME),
											'label' => __('IPv6 Address ', GuiForm_Plugin::NAME),
											'lock'  => true
			),
			'ip'   => array('text'  => __('Please enter a valid IP address', GuiForm_Plugin::NAME),
											'label' => __('IPv4 and IPv6 Address', GuiForm_Plugin::NAME),
											'lock'  => true
			),
			'mac'  => array('text'  => __('Please enter a valid MAC address', GuiForm_Plugin::NAME),
											'label' => __('MAC Address', GuiForm_Plugin::NAME),
											'lock'  => true
			),
			'siren' => array('text' => __('Please enter a valid SIREN number', GuiForm_Plugin::NAME),
											'label' => __('Siren Number', GuiForm_Plugin::NAME),
											'lock'  => true
			),
			'siret' => array('text' => __('Please enter a valid SIRET number', GuiForm_Plugin::NAME),
											'label' => __('Siret Number', GuiForm_Plugin::NAME),
											'lock'  => true
			),
			'bic'  => array('text'  => __('Please enter a valid BIC number', GuiForm_Plugin::NAME),
											'label' => __('BIC <i>(Business Identifier Codes)</i>', GuiForm_Plugin::NAME),
											'lock'  => true
			),
			'ean'  => array('text'  => __('Please enter a valid EAN number', GuiForm_Plugin::NAME),
											'label' => __('EAN <i>(International Article Number)</i>', GuiForm_Plugin::NAME),
											'lock'  => true
			),
			'ein'  => array('text'  => __('Please enter a valid EIN number', GuiForm_Plugin::NAME),
											'label' => __('EIN <i>(Employer Identification Number)</i>', GuiForm_Plugin::NAME),
											'lock'  => true
			),
			'grid' => array('text'  => __('Please enter a valid GRId number', GuiForm_Plugin::NAME),
											'label' => __('GRID <i>(Global Release Identifier)</i>', GuiForm_Plugin::NAME),
											'lock'  => true
			),
			'iban' => array('text'  => __('The country code AS is not supported', GuiForm_Plugin::NAME),
											'label' => __('IBAN <i>(International Bank Account Number)</i>', GuiForm_Plugin::NAME),
											'lock'  => true
			),
			'imei' => array('text'  => __('Please enter a valid IMEI number', GuiForm_Plugin::NAME),
											'label' => __('IMEI <i>(International Mobile Station Equipment Identity)</i>', GuiForm_Plugin::NAME),
											'lock'  => true
			),
			'imo'  => array('text'  => __('Please enter a valid IMO number', GuiForm_Plugin::NAME),
											'label' => __('IMO <i>(International Maritime Organization)</i>', GuiForm_Plugin::NAME),
											'lock'  => true
			),
			'isbn' => array('text'  => __('Please enter a valid ISBN number', GuiForm_Plugin::NAME),
											'label' => __('ISBN <i>(International Standard Book Number). Support both ISBN 10 and ISBN 13</i>', GuiForm_Plugin::NAME),
											'lock'  => true
			),
			'isin' => array('text'  => __('Please enter a valid ISIN number', GuiForm_Plugin::NAME),
											'label' => __('ISIN <i>(International Securities Identification Number)</i>', GuiForm_Plugin::NAME),
											'lock'  => true
			),
			'ismn' => array('text'  => __('Please enter a valid ISMN number', GuiForm_Plugin::NAME),
											'label' => __('ISMN <i>(International Standard Music Number)</i>', GuiForm_Plugin::NAME),
											'lock'  => true
			),
			'issn' => array('text'  => __('Please enter a valid ISSN number', GuiForm_Plugin::NAME),
											'label' => __('ISSN <i>(International Standard Serial Number)</i>', GuiForm_Plugin::NAME),
											'lock'  => true
			),
			'meid' => array('text'  => __('Please enter a valid MEID number', GuiForm_Plugin::NAME),
											'label' => __('MEID <i>(mobile equipment identifier)</i>', GuiForm_Plugin::NAME),
											'lock'  => true
			),
			'rtn'  => array('text'  => __('Please enter a valid RTN number', GuiForm_Plugin::NAME),
											'label' => __('RTN <i>(Routing transit number)</i>', GuiForm_Plugin::NAME),
											'lock'  => true
			),
			'sedol' => array('text' => __('Please enter a valid SEDOL number', GuiForm_Plugin::NAME),
											'label' => __('SEDOL <i>(Stock Exchange Daily Official List)</i>', GuiForm_Plugin::NAME),
											'lock'  => true
			),
			'vin'  => array('text'  => __('Please enter a valid VIN number', GuiForm_Plugin::NAME),
											'label' => __('US VIN <i>(Vehicle Identification Number)</i>', GuiForm_Plugin::NAME),
											'lock'  => true
			)
		);
	}
}