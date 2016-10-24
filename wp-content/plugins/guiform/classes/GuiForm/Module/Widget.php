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
class GuiForm_Module_Widget extends WP_Widget {

	const NAME = __CLASS__;
	
  public function __construct(){
    $widget = array('classname' => self::NAME, 'description' => __('Displays form widget.', GuiForm_Plugin::NAME) );
    $this->WP_Widget(self::NAME, 'GuiForm Widget', $widget);
    add_action( 'widgets_init', array($this, 'registerWidget') );
	}
	
	public function registerWidget(){
		register_widget("GuiForm_Module_Widget");
	}
 
  public function form($instance){
  	global $wpdb;
    $instance = wp_parse_args( (array) $instance, array( 'title' => '', 'id' => '' ) );
    
    $title = $instance['title'];
    $id = $instance['id'];
    
    $forms = $wpdb->get_results("SELECT id, title FROM $wpdb->guiform ORDER BY id");
    
    $form_output = '<select name="'. $this->get_field_name('id') .'" class="widefat">';
    $form_output .= '<option value="">Select</option>';
    foreach($forms as $form){
			$form_output .= '<option '. selected( $form->id, $id, false) .' value="'. $form->id .'">#'. $form->id .' : '. $form->title .'</option>';
		}
		$form_output .= '</select>';
    
    $iframe_output = '<select name="'. $this->get_field_name('iframe') .'" class="widefat">';
    $iframe_output .= '<option '. selected("true", $iframe, false) .' value="true">Yes</option>';
    $iframe_output .= '<option '. selected("false", $iframe, false) .' value="false">No</option>';
		$iframe_output .= '</select>';
    
	?>
	  <p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('id'); ?>">Form: <?php echo $form_output; ?></label>
	<?php
 
  }
 
  public function update($new_instance, $old_instance){
    $instance = $old_instance;
    $instance['title'] = $new_instance['title'];
    $instance['id'] = $new_instance['id'];
    return $instance;
  }
 
  public function widget($args, $instance){
  	global $guiform;
  	
    extract($args, EXTR_SKIP);
    
    $title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
 		
 		echo $args["before_widget"];
 		
    if (!empty($title)){
    	echo $args["before_title"];
      echo $title;
      echo $args["after_title"];
 		}
 		
 		if($instance['id']){
 			$form = new GuiForm_Module_Frontend();
 			echo $form->renderShortCode($instance);
  	}
  	
  	echo $args["after_widget"];
 
  }
}