<?php


namespace Groundhogg\DB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Notes extends DB {
	public function get_db_suffix() {
		return 'gh_notes';
	}

	public function get_primary_key() {
		return 'ID';
	}

	public function get_db_version() {
		return '2.0';
	}

	public function get_object_type() {
		return 'note';
	}

	/**
	 * Get columns and formats
	 *
	 * @access  public
	 * @since   2.1
	 */
	public function get_columns() {
		return array(
			'ID'           => '%d',
			'object_id'    => '%d',
			'object_type'  => '%s',
			'context'      => '%s',
			'user_id'      => '%d',
			'content'      => '%s',
			'date_created' => '%s',
			'timestamp'    => '%d',
		);
	}

	/**
	 * Get default column values
	 *
	 * @access  public
	 * @since   2.1
	 */
	public function get_column_defaults() {

		return array(
			'ID'           => 0,
			'object_id'    => 0,
			'object_type'  => '',
			'context'      => '',
			'user_id'      => 0,
			'content'      => '',
			'date_created' => current_time( 'mysql', true ),
			'timestamp'    => time(),
		);
	}

	/**
	 * Create the table
	 *
	 * @access  public
	 * @since   2.1
	 */
	public function create_table() {

		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE " . $this->table_name . " (
		ID bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        object_id bigint(20) unsigned NOT NULL,
        object_type VARCHAR({$this->get_max_index_length()}) NOT NULL,    
        user_id bigint(20) unsigned NOT NULL,
        context VARCHAR(50) NOT NULL,    
        content longtext NOT NULL,
        timestamp bigint(12) unsigned NOT NULL,
        date_created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY (ID)
		) {$this->get_charset_collate()};";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	}

	/**
	 * Rename gh_contactnotes to gh_notes
	 * change contact_id to object_id
	 * Add new object_type column
	 * Set the object type to contact because up to this point all notes where for contacts.
	 */
	public function update_3_0(){

		global $wpdb;

		$old_table_name = $wpdb->prefix . 'gh_contactnotes';

		$wpdb->query( "ALTER TABLE $old_table_name RENAME TO {$this->table_name};" );
		$wpdb->query( "ALTER TABLE {$this->table_name} CHANGE contact_id object_id bigint(20) unsigned NOT NULL ;" );
		$wpdb->query( "ALTER TABLE {$this->table_name} ADD object_type VARCHAR({$this->get_max_index_length()}) NOT NULL;" );
		$wpdb->query( "UPDATE {$this->table_name} SET object_type = 'contact' WHERE object_type = ''; " );
	}
}