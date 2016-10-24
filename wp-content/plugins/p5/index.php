<?php
/*
Plugin Name: P5 : Plenty of Perishable Passwords for Protected Posts
Description: Add the ability to specify multiple passwords for pages / posts. An expiration date can be set for each password.
Author: Cyril Batillat
Version: 1.4
Author URI: http://bazalt.fr/
License: GPL2
Text Domain: p5
Domain Path: /languages
*/

/*  Copyright 2013 Cyril Batillat (email : contact@bazalt.fr)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
include_once dirname(__FILE__) . '/includes/P5_Password.php';

if ( ! class_exists('P5' ) ) :

register_activation_hook( __FILE__, array( 'P5', 'install' ) );
register_deactivation_hook( __FILE__, array( 'P5', 'deactivate' ) );
register_uninstall_hook(  __FILE__, 'P5_uninstall' );

class P5 {

    const db_version = 1.0;
    const TABLE = 'p5';

    private static $_instance;

    /**
     * Singlton pattern
     * @return P5
     */
    public static function getInstance () {
        if ( self::$_instance instanceof self) return self::$_instance;

        self::$_instance = new self();

        return self::$_instance;
    }

    /**
     * Avoid creation of an instance from outside
     */
    private function __clone () {}

    /**
     * Private constructor (part of singleton pattern)
     * Declare WordPress Hooks
     */
    private function __construct() {
        // Load a custom text domain
        add_action( 'plugins_loaded', array($this, 'action_plugins_loaded') );

        // Ajax method to retrieve a well-formatted date
        add_action( 'wp_ajax_p5_date', array($this, 'action_ajax_date') );
        // Ajax to load a new password template
        add_action( 'wp_ajax_p5_get_new_password_ui', array($this, 'action_ajax_get_new_password_ui') );

        // Modify login form
        add_filter( 'the_password_form', array($this, 'modify_password_form' ));

        // Hook to modify password verification
        add_action(
            'login_form_postpass',
            array($this, 'action_login_form_postpass')
        );

        // Add some markup in backend, in the posts submitbox
        add_action(
            'post_submitbox_misc_actions',
            array($this, 'action_post_submitbox_misc_actions' )
        );

        // Save submitted data
        add_action(
            'save_post',
            array($this, 'action_save_post')
        );

        // Register and load some assets in backend
        add_action( 'admin_enqueue_scripts', array($this, 'action_admin_enqueue_scripts') );

        // The action called by the cron to manage passwords lifetime
        add_action( 'p5cron', array($this, 'cron') );
    }

    /**
     * Plugin installation
     */
    public static function install() {
        if ( ! current_user_can( 'activate_plugins' ) ) return;

        /** @var WPDB $wpdb */
        global $wpdb;

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta(
            'CREATE TABLE IF NOT EXISTS `' . $wpdb->prefix . self::TABLE . '` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `post_id` bigint(20) unsigned NOT NULL,
                `password` varchar(20) NOT NULL,
                `expiration_datetime` datetime DEFAULT "0000-00-00 00:00:00" NOT NULL,
                PRIMARY KEY (`id`),
                KEY `post_id` (`post_id`)
            ) ' . $wpdb->get_charset_collate() . ';'
        );
        add_option( 'p5_db_version', self::db_version );

        // Copy default passwords in p5 table
        $protectedPosts = $wpdb->get_results("
            SELECT ID, post_password
            FROM " . $wpdb->posts . "
            WHERE post_password != ''
        ");
        foreach ( $protectedPosts as $protectedPost )
        {
            $password = new P5_Password();
            $password->post_id = $protectedPost->ID;
            $password->password = $protectedPost->post_password;
            $password->save();
        }

        // Cron to disable expired passwords
        // For some reason there seems to be a problem on some systems where the hook must not contain underscores or uppercase characters.
        // @see http://codex.wordpress.org/Function_Reference/wp_schedule_event
        if ( !wp_next_scheduled( 'p5cron' ) ) {
            wp_schedule_event( time(), 'hourly', 'p5cron' );
        }
    }

    /**
     * Plugin deactivation
     */
    public static function deactivate() {
        if ( ! current_user_can( 'activate_plugins' ) ) return;
        wp_clear_scheduled_hook( 'p5cron' );
    }

    /**
     * Action hook plugins_loaded
     * Load plugin text domain
     */
    public function action_plugins_loaded() {
        load_plugin_textdomain( 'p5', false, basename( dirname(__FILE__) ) . '/languages/' );
    }

    /**
     * Action hook post_submitbox_misc_actions
     * Add some markup in the posts submitbox
     */
    public function action_post_submitbox_misc_actions() {
        global $post;
        $post_type_object = get_post_type_object( $post->post_type );
        if( !current_user_can( $post_type_object->cap->publish_posts ) ) return;
        ?>
        <div id="p5-section">
            <div class="hide-if-js"><strong><?php _e( 'Passwords:', 'p5' ); ?></strong></div>
            <ul id="p5_postpasswords">
                <?php
                foreach($this->getPostPasswords( $post->ID ) as $post_password) : ?>
                    <li class="p5_postpassword">
                        <?php echo $this->getPasswordTemplate( $post_password ); ?>
                    </li>
                <?php endforeach; ?>
                <li class="p5_postpassword hide-if-js">
                    <?php echo $this->getPasswordTemplate(); ?>
                </li>
            </ul>

            <div id="p5-section-footer">
                <a href="#" title="" id="p5_add_password" class="hide-if-no-js button-primary">
                    <?php _e( 'Add a password', 'p5' ); ?>
                </a>
            </div>
        </div>
        <?php
    }

    /**
     * Action Hook save_post
     * @see http://codex.wordpress.org/Plugin_API/Action_Reference/save_post
     * @param $post_id
     */
    public function action_save_post($post_id) {
        /** @var WPDB $wpdb */
        global $wpdb;
        if ( wp_is_post_revision( $post_id ) ) return;
        if ( wp_is_post_autosave( $post_id ) ) return;
        if(!is_int( $post_id )) return;

        if( !empty( $_POST['visibility'] ) && $_POST['visibility'] === 'password' ) {
            foreach((array) $_REQUEST['p5'] as $record_id => $data) {
                if( empty( $data ) ) continue;
                $obj = new P5_Password( $record_id );

                // Deletion requested ?
                if(!empty( $data['delete'] )) {
                    $obj->delete();
                    continue;
                }
                $obj->post_id = $post_id;
                $obj->password = $data['password'];
                $obj->expiration_datetime = $data['expiration_datetime'];
                $obj->save();
            }

            // Register the first password in the original post_password field
            // Without this, the post will still remain as 'public'
            $post_passwords = $this->getPostPasswords( $post_id );
            self::updateOriginalPassword( $post_id, $post_passwords[0]->password );
        } else {
            // delete post passwords
            $post_passwords = $this->getPostPasswords( $post_id );
            foreach($post_passwords as $post_password) {
                $post_password->delete();
            }
        }

        // Purge expired passwords
        $this->cron();
    }

    /**
     * Update default post_password in wp_posts table
     * @param $post_id
     * @param string $password
     */
    public static function updateOriginalPassword($post_id, $password='') {
        /** @var WPDB $wpdb */
        global $wpdb;
        $wpdb->update(
            $wpdb->posts,
            array(
                'post_password' => empty($password) ? '' : $password
            ),
            array( 'ID' => $post_id )
        );
    }

    /**
     * Return the HTML markup for one password
     * @param P5_Password $record
     * @return string
     */
    public function getPasswordTemplate(P5_Password $record=null) {
        if(is_null($record)) $record = new P5_Password();
        if(empty($record->id)) $record->id = uniqid( 'p5new' );
        ob_start();
        ?>
        <ul>
            <li>
                <!-- Password input -->
                <label for="p5_<?php echo $record->id; ?>_password"><?php _e( 'Password:' ); ?></label>
                <input type="text"
                       name="p5[<?php echo $record->id; ?>][password]"
                       id="p5_<?php echo $record->id; ?>_password"
                       size="12"
                       value="<?php echo $record->password; ?>"/>

                <!-- Link to delete the password (JS only) -->
                <a class="hide-if-no-js p5_delete_password" href="#" title="<?php _e( 'Delete this password', 'p5' ); ?>">
                    <img src="<?php echo plugins_url( 'assets/img/delete.png' , __FILE__ ); ?>" alt="<?php _e( 'Delete this password', 'p5' ); ?>"/>
                </a>
            </li>

            <li>
                <!-- Expiration date -->
                <label for="p5_<?php echo $record->id; ?>_expiration-datetime"><?php _e( 'Expiration date:', 'p5' ); ?></label>
                <input type="text"
                       name="p5[<?php echo $record->id; ?>][expiration_datetime]"
                       class="p5-expiration-date hide-if-js"
                       value="<?php echo $record->expiration_datetime; ?>"
                       id="p5_<?php echo $record->id; ?>_expiration-datetime"/>
                <span class="p5_formatted_date hide-if-no-js">
                    <a class="p5-trigger-datepicker" href="#" title="<?php _e( 'Change expiration date', 'p5' ); ?>">
                        <?php echo $this->getFormattedDate( $record->expiration_datetime ); ?></a>
                </span>

                <a href="#"
                   class="hide-if-no-js p5-remove-date <?php if($this->handleDate( $record->expiration_datetime ) === false) echo 'hide'; ?>"
                   title="<?php _e( 'Clear date', 'p5' ); ?>"><?php _e( 'Clear date', 'p5' ); ?></a>
            </li>

            <li class="delete hide-if-js">
                <label for="p5_<?php echo $record->id; ?>_delete"><?php _e( 'Remove this password', 'p5' ); ?></label>
                <input type="checkbox" name="p5[<?php echo $record->id; ?>][delete]" value="1" id="p5_<?php echo $record->id; ?>_delete" class="p5_checkbox_delete_password"/>
            </li>
        </ul>
        <?php
        $return = ob_get_contents();
        ob_end_clean();
        return $return;
    }

    /**
     * Action hook admin_enqueue_scripts
     * Register an load some assets
     * @param $hook
     */
    public function action_admin_enqueue_scripts($hook) {
        if($hook != 'post.php' && $hook != 'post-new.php') return;

        // jQuery UI CSS
        wp_register_style(
            'jquery-ui-p5',
            plugins_url( 'lib/jquery-ui/jquery-ui-1.10.3.custom.css' , __FILE__ ),
            array(),
            '1.10.3'
        );

        // jQuery Datetimepicker JS
        wp_register_script(
            'datetimepicker',
            plugins_url( 'lib/jquery.datetimepicker/jquery-ui-timepicker-addon.js' , __FILE__ ),
            array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-slider' ),
            '1.4.4'
        );

        // jQuery Datetimepicker CSS
        wp_register_style(
            'datetimepicker',
            plugins_url( 'lib/jquery.datetimepicker/jquery-ui-timepicker-addon.css' , __FILE__ ),
            array( 'jquery-ui-p5' ),
            '1.4.4'
        );

        // P5 JS
        wp_enqueue_script(
            'p5',
            plugins_url( 'assets/js/p5.js' , __FILE__ ),
            array( 'jquery', 'datetimepicker' ),
            '1.0'
        );

        // P5 CSS
        wp_enqueue_style(
            'p5',
            plugins_url( 'assets/css/p5.css' , __FILE__ ),
            array( 'datetimepicker' ),
            '1.0'
        );
    }

    /**
     * Action hook called before login process in wp-login.php
     * Modify the password verification
     */
    public function action_login_form_postpass() {
        global $wp_hasher;

        $postID = url_to_postid( wp_get_referer() );

        // There's a bug in WordPress < 3.6 on the function
        // url_to_postid used on Custom Post Types, specially on private posts
        if(empty($postID) && !empty($_POST['post_id'])) {
            $postID = intval($_POST['post_id']);
        }
        if(empty($postID)) return;

        // Default values
        $password = $_POST['post_password'];
        $expire = time() + 10 * DAY_IN_SECONDS;

        // Search if password is valid for this post
        $P5_Password = $this->loadPasswordForPost( $postID, $password );
        /*echo '<pre>';
        var_dump($P5_Password);
        var_dump($_COOKIE);
        echo '</pre>';*/

        if($P5_Password !== false) {
            // The password is OK, but for the plugin to work,
            // the default post password must be set in place.
            $post = get_post( $postID );
            $password = $post->post_password;

            // Adjust the cookie expiration with password expiration datetime
            $dateTime = DateTime::createFromFormat(
                'Y-m-d H:i:s',
                $P5_Password->expiration_datetime,
                new DateTimezone( $this->wp_get_timezone_string() )
            );

            if($dateTime !== false) {
                $expire = intval( $dateTime->format('U') );
            }
        }

        // Max expiration datetime. WordPress default is time() + 10 * DAY_IN_SECONDS
        if($expire > time() + 10 * DAY_IN_SECONDS)
            $expire = time() + 10 * DAY_IN_SECONDS;

        if ( empty( $wp_hasher ) ) {
            require_once( ABSPATH . 'wp-includes/class-phpass.php' );
            $wp_hasher = new PasswordHash( 8, true );
        }
        setcookie(
            'wp-postpass_' . COOKIEHASH,
            $wp_hasher->HashPassword( stripslashes( $password ) ),
            $expire,
            COOKIEPATH
        );
        setcookie(
            'wp-postpass_expire' . COOKIEHASH,
            $expire
        );
       // exit();
        wp_safe_redirect( wp_get_referer() );
        exit();
    }

    /**
     * Return WP timezone
     * @return mixed|string|void
     */
    function wp_get_timezone_string() {

        // if site timezone string exists, return it
        if ( $timezone = get_option( 'timezone_string' ) ) return $timezone;

        // get UTC offset, if it isn't set then return UTC
        if ( 0 === ( $utc_offset = get_option( 'gmt_offset', 0 ) ) ) return 'UTC';

        // adjust UTC offset from hours to seconds
        $utc_offset *= 3600;

        // attempt to guess the timezone string from the UTC offset
        $timezone = timezone_name_from_abbr( '', $utc_offset, 1 );

        if( $timezone !== false ) return $timezone;

        // last try, guess timezone string manually
        $is_dst = date( 'I' );
        foreach ( timezone_abbreviations_list() as $abbr ) {
            foreach ( $abbr as $city ) {
                if ( $city['dst'] == $is_dst && $city['offset'] == $utc_offset )
                    return $city['timezone_id'];
            }
        }

        // fallback to UTC
        return 'UTC';
    }

    /**
     * Return a date formatted as specified in WordPress options
     * AJAX method.
     */
    public function action_ajax_date() {
        $return = array(
            'success' => 0,
            'date' => $this->getFormattedDate( $_POST['date'] )
        );

        if($this->handleDate( $_POST['date']) !== false ) $return['success'] = 1;

        echo json_encode( $return );
        die ();
    }

    /**
     * Return the template for a new password
     * AJAX method.
     */
    public function action_ajax_get_new_password_ui() {
        echo $this->getPasswordTemplate();
        die();
    }

    /**
     * Try to handle a date
     * @param null $date. The date with this format : Y-m-d h:i:s
     * @return boolean|DateTime
     */
    public function handleDate($date=null) {
        if($date === '0000-00-00 00:00:00') return false;

        $dateTime = DateTime::createFromFormat( 'Y-m-d H:i:s', $date );
        if( ! $dateTime ) return false;

        //$timezoneName = timezone_name_from_abbr("", get_option('gmt_offset') * 3600, false);
        //if( $timezoneName !== false ) $dateTime->setTimezone( new DateTimezone( $timezoneName )  );

        return $dateTime;
    }

    /*public function getWpTimezone() {
        get_date_from_gmt( $string, $format );
        var_dump( get_option('gmt_offset') );
        $timezoneName = timezone_name_from_abbr("", get_option('gmt_offset') * 3600, false);
        if( $timezoneName !== false ) $dateTime->setTimezone( new DateTimezone( $timezoneName )  );
    }*/

    /**
     * Return a date formatted with custom date and time formats
     * @param null $date
     * @return string : the date, formatted as specified in WordPress options
     */
    public function getFormattedDate($date=null) {
        /** @var DateTime $dateTime */
        $dateTime = $this->handleDate( $date );
        if($dateTime === false) return __( 'None', 'p5' );

        return date_i18n(
            get_option( 'date_format' ) . ' @ ' . get_option( 'time_format '),
            $dateTime->format('U')
        );
    }

    /**
     * Return an array of passwords for a given post
     * @param $post_id
     * @return P5_Password[] array
     */
    public function getPostPasswords($post_id) {
        /** @var WPDB $wpdb */
        global $wpdb;
        $return = array();
        $rows = $wpdb->get_results(
            $wpdb->prepare('
                SELECT id
                FROM `' . $wpdb->prefix . self::TABLE . '`
                WHERE post_id=%d;', array($post_id)
            ), OBJECT
        );
        foreach((array) $rows as $row) {
            $passwd = new P5_Password( $row->id );
            if($passwd === false) continue;
            $return[] = $passwd;
        }
        return $return;
    }

    /**
     * Return an array of expired passwords
     * @return P5_Password[] array
     */
    public function getExpiredPasswords() {
        /** @var WPDB $wpdb */
        global $wpdb;
        $return = array();
        $rows = $wpdb->get_results('
            SELECT id
            FROM `' . $wpdb->prefix . self::TABLE . '`
            WHERE
                expiration_datetime<=NOW()
                AND expiration_datetime != "0000-00-00 00:00:00";',
            OBJECT
        );
        foreach((array) $rows as $row) {
            $passwd = new P5_Password( $row->id );
            if($passwd === false) continue;
            $return[] = $passwd;
        }
        return $return;
    }

    /**
     * Try to load a password for a post
     * @param $post_id
     * @param string $password
     * @return bool|P5_Password
     */
    public function loadPasswordForPost($post_id, $password='') {
        /** @var WPDB $wpdb */
        global $wpdb;

        $row = $wpdb->get_row(
            $wpdb->prepare('
                SELECT id
                FROM `' . $wpdb->prefix . self::TABLE . '`
                WHERE
                    post_id=%d
                    AND password=%s
                    AND (
                        expiration_datetime>=NOW()
                        OR expiration_datetime="0000-00-00 00:00:00"
                    );', array($post_id, $password)
            ), OBJECT
        );
        if(empty($row)) return false;
        return new P5_Password( $row->id );
    }

    /**
     * The method called by the cron via p5cron action hook.
     */
    public function cron() {
        $expiredPasswords = $this->getExpiredPasswords();
        foreach( (array) $expiredPasswords as $password) {
            /** @var P5_Password $password */
            $password->delete();
        }
    }

    /**
     * Hook to modify the login form.
     * An hidden input container post ID is added
     * @param $output
     * @return string
     */
    public function modify_password_form($output) {
        global $post;

        $dom = new DOMDocument();
        $dom->loadHTML($output);
        foreach ($dom->getElementsByTagName('input') as $input) {
            if($input->getAttribute('name') !== 'post_password') continue;

            $hiddenInput = $dom->createElement('input');
            $hiddenInput->setAttribute('type', 'hidden');
            $hiddenInput->setAttribute('name', 'post_id');
            $hiddenInput->setAttribute('value', $post->ID);
            $input->parentNode->appendChild($hiddenInput);
        }
        $output = $dom->saveXML();
        return $output;
    }

}
P5::getInstance();

/**
 * Uninstallation of the P5 plugin
 */
function P5_uninstall() {
    if ( ! current_user_can( 'activate_plugins' ) ) return;
    /** @var WPDB $wpdb */
    global $wpdb;
    $wpdb->query( 'DROP TABLE `' . $wpdb->prefix. P5::TABLE . '`' );
    delete_option( 'p5_db_version' );
}
endif;