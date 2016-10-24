<?php

//Rename this file to "functions.php"

class Custom_Class extends GuiForm_Module{
	
	function __construct() {
		//$this->_addAction('guiform_filter_{Form ID}_save_entry', array($this, 'saveEntry'), 10, 2);
		//add_action('guiform_filter_{Form ID}_save_entry', array($this, 'savePost'));
  }
  
  public function savePost($args, $props){
  	
  }
  
}

//Check if custom class is already loaded.
if ( ! class_exists( 'Custom_Class', false ) ) {
   new Custom_Class();
}