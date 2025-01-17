<?php

namespace Groundhogg\Bulk_Jobs;

use function Groundhogg\create_contact_from_user;
use Groundhogg\Plugin;
use function Groundhogg\recount_tag_contacts_count;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Sync_Contacts extends Bulk_Job {

	/**
	 * Get the action reference.
	 *
	 * @return string
	 */
	function get_action() {
		return 'gh_sync_users';
	}

	/**
	 * Get an array of items someway somehow
	 *
	 * @param $items array
	 *
	 * @return array
	 */
	public function query( $items ) {
		if ( ! current_user_can( 'add_contacts' ) ) {
			return $items;
		}

		/* convert users to contacts */
		$args = array(
			'fields' => 'all_with_meta'
		);

		$users = get_users( $args );

		$uids = [];

		/* @var $wp_user \WP_User */
		foreach ( $users as $wp_user ) {
			$uids[] = $wp_user->ID;
		}

		return $uids;
	}

	/**
	 * Process an item
	 *
	 * @param $item mixed
	 *
	 * @return void
	 */
	protected function process_item( $item ) {
		if ( ! current_user_can( 'add_contacts' ) ) {
			return;
		}

		create_contact_from_user( absint( $item ), get_transient( 'gh_sync_user_meta' ) );
	}

	/**
	 * Do stuff before the loop
	 *
	 * @return void
	 */
	protected function pre_loop() {
	}

	/**
	 * do stuff after the loop
	 *
	 * @return void
	 */
	protected function post_loop() {
		delete_transient( 'gh_sync_user_meta' );
	}

	/**
	 * Cleanup any options/transients/notices after the bulk job has been processed.
	 *
	 * @return void
	 */
	protected function clean_up() {
		recount_tag_contacts_count();
	}

	/**
	 * Get the return URL
	 *
	 * @return string
	 */
	protected function get_return_url() {
		return admin_url( 'admin.php?page=gh_contacts' );
	}
}