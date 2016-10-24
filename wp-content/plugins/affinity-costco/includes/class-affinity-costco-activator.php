<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Affinity_Costco
 * @subpackage Affinity_Costco/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Affinity_Costco
 * @subpackage Affinity_Costco/includes
 * @author     Your Name <email@example.com>
 */
class Affinity_Costco_Activator {
	public static function activate() {
		update_option('apcostco_demo_mode', '0');	
		update_option('apcostco_post_url', 'http://secure.affinityps.com/api/costco/');
		update_option('apcostco_post_api', 'STD');
	}

}
