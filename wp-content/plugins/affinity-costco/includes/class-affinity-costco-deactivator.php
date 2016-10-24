<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Affinity_Costco
 * @subpackage Affinity_Costco/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Affinity_Costco
 * @subpackage Affinity_Costco/includes
 * @author     Your Name <email@example.com>
 */
class Affinity_Costco_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		delete_option('apcostco_demo_mode');	
		delete_option('apcostco_post_url');
		delete_option('apcostco_post_api');
	}

}
