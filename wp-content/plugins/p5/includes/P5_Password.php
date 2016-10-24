<?php
class P5_Password {

    public $id;
    public $post_id;
    public $password;
    public $expiration_datetime;

    public function __construct($id=null) {
        if(!empty($id)) $this->load($id);
        return $this;
    }

    public function load($id=null) {
        if( empty( $id ) ) return false;
        global $wpdb;
        $result = $wpdb->get_row(
            $wpdb->prepare('
                SELECT id, post_id, password, expiration_datetime
                FROM `' . $wpdb->prefix . P5::TABLE . '`
                WHERE id=%d', array($id)
            ), OBJECT
        );
        if(is_null($result)) return false;
        $this->id = $result->id;
        $this->post_id = $result->post_id;
        $this->password = $result->password;
        $this->expiration_datetime = $result->expiration_datetime;
        return $this;
    }

    public function save() {
        global $wpdb;
        if(empty($this->id) && !empty($this->password)) {
            $wpdb->insert(
                $wpdb->prefix . P5::TABLE,
                array(
                    'post_id' => $this->post_id,
                    'password' => $this->password,
                    'expiration_datetime' => $this->expiration_datetime
                ),
                array(
                    '%d',
                    '%s',
                    '%s'
                )
            );
            do_action( 'p5_insert_password', $this );
        } else {
            $wpdb->update(
                $wpdb->prefix . P5::TABLE,
                array(
                    'post_id' => $this->post_id,
                    'password' => $this->password,
                    'expiration_datetime' => $this->expiration_datetime
                ),
                array('id' => $this->id), // WHERE
                array(
                    '%d',
                    '%s',
                    '%s'
                ),
                array('%d') // WHERE format
            );
            do_action( 'p5_update_password', $this );
        }
        do_action( 'p5_save_password', $this );
    }

    public function delete() {
        /** @var WPDB $wpdb */
        global $wpdb;
        $wpdb->query(
            $wpdb->prepare('
                DELETE FROM ' . $wpdb->prefix . P5::TABLE . '
		        WHERE id = %d', array($this->id)
            )
        );

        // Don't forget to update post_password in posts table
        if( ! empty( $this->post_id ) ) {
            $passwords = P5::getInstance()->getPostPasswords($this->post_id);
            P5::updateOriginalPassword( $this->post_id, $passwords[0]->password );
        }

        // Custom Hook
        do_action( 'p5_delete_password', $this );
        return $this;
    }
}