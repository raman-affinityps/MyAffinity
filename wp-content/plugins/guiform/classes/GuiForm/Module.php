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
class Guiform_Module {

	/**
	 * The instance of wpdb class.
	 *
	 * @since 1.0
	 *
	 * @access protected
	 * @var wpdb
	 */
	protected $_wpdb = null;

	/**
	 * The plugin instance.
	 *
	 * @since 1.0
	 *
	 * @access protected
	 * @var GuiForm_Plugin
	 */
	protected $_plugin = null;

	/**
	 * Constructor.
	 *
	 * @since 1.0
	 * @global wpdb $wpdb Current database connection.
	 *
	 * @access public
	 * @param GuiForm_Plugin $plugin The instance of the plugin.
	 */
	public function __construct( Guiform_Plugin $plugin ) {
		global $wpdb;

		$this->_wpdb = $wpdb;
		$this->_plugin = $plugin;
	}

	/**
	 * Registers an action hook.
	 *
	 * @since 1.0
	 * @uses add_action() To register action hook.
	 *
	 * @access protected
	 * @param string $tag The name of the action to which the $method is hooked.
	 * @param string $method The name of the method to be called.
	 * @param int $priority optional. Used to specify the order in which the functions associated with a particular action are executed (default: 10). Lower numbers correspond with earlier execution, and functions with the same priority are executed in the order in which they were added to the action.
	 * @param int $accepted_args optional. The number of arguments the function accept (default 1).
	 * @return GuiForm_Module
	 */
	protected function _addAction( $tag, $method, $priority = 10, $accepted_args = 1 ) {
		$method = (is_array($method)) ? $method : array( $this, $method );
		add_action( $tag, $method, $priority, $accepted_args );
		return $this;
	}

	/**
	 * Registers AJAX action hook.
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @param string $tag The name of the AJAX action to which the $method is hooked.
	 * @param string $method Optional. The name of the method to be called. If the name of the method is not provided, tag name will be used as method name.
	 * @param boolean $private Optional. Determines if we should register hook for logged in users.
	 * @param boolean $public Optional. Determines if we should register hook for not logged in users.
	 * @return GuiForm_Module
	 */
	protected function _addAjaxAction( $tag, $method, $private = true, $public = false ) {
		
		$method = (is_array($method)) ? $method : array( $this, $method );
		
		if ( $private ) {
			$this->_addAction( 'wp_ajax_' . $tag, $method );
		}

		if ( $public ) {
			$this->_addAction( 'wp_ajax_nopriv_' . $tag, $method );
		}

		return $this;
	}

	/**
	 * Registers ADMIN POST action hook.
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @param string $tag The name of the ADMIN POST action to which the $method is hooked.
	 * @param string $method Optional. The name of the method to be called. If the name of the method is not provided, tag name will be used as method name.
	 * @param boolean $private Optional. Determines if we should register hook for logged in users.
	 * @param boolean $public Optional. Determines if we should register hook for not logged in users.
	 * @return GuiForm_Module
	 */
	protected function _addAdminAction( $tag, $method, $private = true, $public = false ) {
		
		$method = (is_array($method)) ? $method : array( $this, $method );
		
		if ( $private ) {
			$this->_addAction( 'admin_post_' . $tag, $method );
		}

		if ( $public ) {
			$this->_addAction( 'admin_post_nopriv_' . $tag, $method );
		}

		return $this;
	}

	/**
	 * Registers a filter hook.
	 *
	 * @since 1.0
	 * @uses add_filter() To register filter hook.
	 *
	 * @access protected
	 * @param string $tag The name of the filter to hook the $method to.
	 * @param type $method The name of the method to be called when the filter is applied.
	 * @param int $priority optional. Used to specify the order in which the functions associated with a particular action are executed (default: 10). Lower numbers correspond with earlier execution, and functions with the same priority are executed in the order in which they were added to the action.
	 * @param int $accepted_args optional. The number of arguments the function accept (default 1).
	 * @return GuiForm_Module
	 */
	protected function _addFilter( $tag, $method, $priority = 10, $accepted_args = 1 ) {
		$method = ( is_array( $method ) ) ? $method : array( $this, $method );
		add_filter( $tag, $method, $priority, $accepted_args );
		return $this;
	}
	
	/**
	 * Registers a filter hook.
	 *
	 * @since 1.0
	 * @uses add_filter() To register filter hook.
	 *
	 * @access protected
	 * @param string $tag The name of the filter to hook the $method to.
	 * @param type $method The name of the method to be called when the filter is applied.
	 * @param int $priority optional. Used to specify the order in which the functions associated with a particular action are executed (default: 10). Lower numbers correspond with earlier execution, and functions with the same priority are executed in the order in which they were added to the action.
	 * @param int $accepted_args optional. The number of arguments the function accept (default 1).
	 * @return GuiForm_Module
	 */
		
	protected function _addMetaBox( $tag, $title, $method, $type, $context, $priority ) {
		$method = ( is_array( $method ) ) ? $method : array( $this, $method );
		add_meta_box( $tag, $title, $method, $type, $context, $priority );
		return $this;
	}

	/**
	 * Registers a hook for shortcode tag.
	 *
	 * @since 1.0
	 * @uses add_shortcode() To register shortcode hook.
	 *
	 * @access protected
	 * @param string $tag Shortcode tag to be searched in post content.
	 * @param string $method Hook to run when shortcode is found.
	 * @return GuiForm_Module
	 */
	protected function _addShortcode( $tag, $method ) {
		$method = ( is_array( $method ) ) ? $method : array( $this, $method );
		add_shortcode( $tag, $method );
		return $this;
	}

}