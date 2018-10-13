<?php
/**
 * Contact Meta DB class
 *
 * This class is for interacting with the contact meta database table
 *
 * @package     WPGH
 * @subpackage  Classes/DB Contact Meta
 * @copyright   Copyright (c) 2018, Adrian Tobey (Modified from EDD)
 * @license     http://opensource.org/licenses/gpl-3.0 GNU Public License
 * @since       2.6
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WPGH_DB_Contact_Meta extends WPGH_DB {

    /**
     * Get things started
     *
     * @access  public
     * @since   2.6
     */
    public function __construct() {
        global $wpdb;

        $this->table_name  = $wpdb->prefix . 'gh_contactmeta';
        $this->primary_key = 'meta_id';
        $this->version     = '1.0';

        add_action( 'plugins_loaded', array( $this, 'register_table' ), 11 );

    }

    /**
     * Get table columns and data types
     *
     * @access  public
     * @since   1.7.18
     */
    public function get_columns() {
        return array(
            'meta_id'     => '%d',
            'contact_id'  => '%d',
            'meta_key'    => '%s',
            'meta_value'  => '%s',
        );
    }

    /**
     * Register the table with $wpdb so the metadata api can find it
     *
     * @access  public
     * @since   2.6
     */
    public function register_table() {
        global $wpdb;
        $wpdb->contactmeta = $this->table_name;
    }

    /**
     * Retrieve contact meta field for a contact.
     *
     * For internal use only. Use EDD_Contact->get_meta() for public usage.
     *
     * @param   int    $contact_id   Contact ID.
     * @param   string $meta_key      The meta key to retrieve.
     * @param   bool   $single        Whether to return a single value.
     * @return  mixed                 Will be an array if $single is false. Will be value of meta data field if $single is true.
     *
     * @access  private
     * @since   2.6
     */
    public function get_meta( $contact_id = 0, $meta_key = '', $single = false ) {
        $contact_id = $this->sanitize_contact_id( $contact_id );
        if ( false === $contact_id ) {
            return false;
        }

        return get_metadata( 'contact', $contact_id, $meta_key, $single );
    }

    /**
     * Add meta data field to a contact.
     *
     * For internal use only. Use EDD_Contact->add_meta() for public usage.
     *
     * @param   int    $contact_id   Contact ID.
     * @param   string $meta_key      Metadata name.
     * @param   mixed  $meta_value    Metadata value.
     * @param   bool   $unique        Optional, default is false. Whether the same key should not be added.
     * @return  bool                  False for failure. True for success.
     *
     * @access  private
     * @since   2.6
     */
    public function add_meta( $contact_id = 0, $meta_key = '', $meta_value, $unique = false ) {
        $contact_id = $this->sanitize_contact_id( $contact_id );
        if ( false === $contact_id ) {
            return false;
        }

        return add_metadata( 'contact', $contact_id, $meta_key, $meta_value, $unique );
    }

    /**
     * Update contact meta field based on Contact ID.
     *
     * For internal use only. Use EDD_Contact->update_meta() for public usage.
     *
     * Use the $prev_value parameter to differentiate between meta fields with the
     * same key and Contact ID.
     *
     * If the meta field for the contact does not exist, it will be added.
     *
     * @param   int    $contact_id   Contact ID.
     * @param   string $meta_key      Metadata key.
     * @param   mixed  $meta_value    Metadata value.
     * @param   mixed  $prev_value    Optional. Previous value to check before removing.
     * @return  bool                  False on failure, true if success.
     *
     * @access  private
     * @since   2.6
     */
    public function update_meta( $contact_id = 0, $meta_key = '', $meta_value, $prev_value = '' ) {
        $contact_id = $this->sanitize_contact_id( $contact_id );
        if ( false === $contact_id ) {
            return false;
        }

        return update_metadata( 'contact', $contact_id, $meta_key, $meta_value, $prev_value );
    }

    /**
     * Remove metadata matching criteria from a contact.
     *
     * For internal use only. Use EDD_Contact->delete_meta() for public usage.
     *
     * You can match based on the key, or key and value. Removing based on key and
     * value, will keep from removing duplicate metadata with the same key. It also
     * allows removing all metadata matching key, if needed.
     *
     * @param   int    $contact_id   Contact ID.
     * @param   string $meta_key      Metadata name.
     * @param   mixed  $meta_value    Optional. Metadata value.
     * @return  bool                  False for failure. True for success.
     *
     * @access  private
     * @since   2.6
     */
    public function delete_meta( $contact_id = 0, $meta_key = '', $meta_value = '' ) {
        return delete_metadata( 'contact', $contact_id, $meta_key, $meta_value );
    }

    /**
     * Create the table
     *
     * @access  public
     * @since   2.6
     */
    public function create_table() {

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $sql = "CREATE TABLE {$this->table_name} (
		meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		contact_id bigint(20) unsigned NOT NULL,
		meta_key varchar(255) DEFAULT NULL,
		meta_value longtext,
		PRIMARY KEY  (meta_id),
		KEY contact_id (contact_id),
		KEY meta_key (meta_key)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

        dbDelta( $sql );

        update_option( $this->table_name . '_db_version', $this->version );
    }

    /**
     * Given a contact ID, make sure it's a positive number, greater than zero before inserting or adding.
     *
     * @since  2.6
     * @param  int|string $contact_id A passed contact ID.
     * @return int|bool                The normalized contact ID or false if it's found to not be valid.
     */
    private function sanitize_contact_id( $contact_id ) {
        if ( ! is_numeric( $contact_id ) ) {
            return false;
        }

        $contact_id = (int) $contact_id;

        // We were given a non positive number
        if ( absint( $contact_id ) !== $contact_id ) {
            return false;
        }

        if ( empty( $contact_id ) ) {
            return false;
        }

        return absint( $contact_id );

    }

}