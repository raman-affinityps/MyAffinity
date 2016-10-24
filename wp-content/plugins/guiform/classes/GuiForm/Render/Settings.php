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
 * Settings page.
 *
 * @category GuiForm
 * @package Render
 *
 * @since 1.0
 */
class GuiForm_Render_Settings extends GuiForm_Render{
	
	var $tab;
	
	/**
	 * Constructor.
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @param array $data The data what has to be associated with this render.
	 */
	public function __construct( ) { }

	public function toHTML(){
		$this->tab = (isset($_REQUEST['tab'])) ? $_REQUEST['tab'] : 'general';
		echo "<div id='settings'>";
		$this->header($this->tab);
		$this->_toHTML();
		echo "</div>";
	}
	
	public function header($current = 'general'){
		global $guiform;
		$tabs = array('license'     => 'License', 
		              'general'     => 'General',
									'mail'        => 'Mail Setup',
									'options'     => 'Options'							
									); 
							
	  echo '<h2 class="nav-tab-wrapper">';
	  foreach( $tabs as $tab => $name ){
			$url = add_query_arg(array('setup' => false, 'tab' => $tab, 'orderby' => false, 'order' => false ));
			$class = ( $tab == $current ) ? ' nav-tab-active' : '';
			echo "<a class='nav-tab$class' href='{$url}'>$name</a>";
	  }
	  echo '</h2>';
	}

	protected function _toHTML() {}
}