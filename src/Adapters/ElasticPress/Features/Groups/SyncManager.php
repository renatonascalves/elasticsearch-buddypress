<?php
/**
 * Elasticsearch BuddyPress Adapters: ElasticPress - Groups Sync Manager
 *
 * Manage synchronizing of groups between BuddyPress and Elasticsearch.
 *
 * @package Elasticsearch\BuddyPress
 */

declare( strict_types=1 );

namespace Elasticsearch\BuddyPress\Adapters\ElasticPress\Features\Groups;

use ElasticPress\Indexables;
use ElasticPress\Utils;
use ElasticPress\SyncManager as SyncManagerAbstract;
use BP_Groups_Group;

/**
 * Group Sync Manager class.
 */
class SyncManager extends SyncManagerAbstract {

	/**
	 * Setup actions and filters.
	 */
	public function setup(): void {

		// Check if we can index content in the current blog.
		if ( ! $this->can_index_site() ) {
			return;
		}

		// Don't sync when indexing.
		if ( Utils\is_indexing() ) {
			return;
		}

		add_action( 'groups_group_after_save', [ $this, 'action_sync_on_update' ] );
		add_action( 'bp_groups_delete_group', [ $this, 'action_delete_group' ] );
		add_action( 'added_group_meta', [ $this, 'action_queue_meta_sync' ], 10, 2 );
		add_action( 'updated_group_meta', [ $this, 'action_queue_meta_sync' ], 10, 2 );
		add_action( 'deleted_group_meta', [ $this, 'action_queue_meta_sync' ], 10, 2 );
	}

	/**
	 * Un-setup actions and filters.
	 */
	public function tear_down(): void {
		remove_action( 'groups_group_after_save', [ $this, 'action_sync_on_update' ] );
		remove_action( 'bp_groups_delete_group', [ $this, 'action_delete_group' ] );
		remove_action( 'added_group_meta', [ $this, 'action_queue_meta_sync' ] );
		remove_action( 'updated_group_meta', [ $this, 'action_queue_meta_sync' ] );
		remove_action( 'deleted_group_meta', [ $this, 'action_queue_meta_sync' ] );
	}

	/**
	 * Sync ES index with what happened to the group being saved.
	 *
	 * @param BP_Groups_Group $group Group object.
	 */
	public function action_sync_on_update( $group ): void {
		if ( $this->kill_sync() ) {
			return;
		}

		$this->add_to_queue( $group->id );
	}

	/**
	 * When, allowed, meta is updated/added/deleted, queue the object for reindex.
	 *
	 * @param int $meta_id  Meta id.
	 * @param int $group_id Group id.
	 */
	public function action_queue_meta_sync( $meta_id, $group_id ): void {
		if ( $this->kill_sync() ) {
			return;
		}

		$this->add_to_queue( $group_id );
	}

	/**
	 * Deletes a group from the index on group delete.
	 *
	 * @param BP_Groups_Group $group Group object.
	 */
	public function action_delete_group( $group ): void {
		if ( $this->kill_sync() ) {
			return;
		}

		Indexables::factory()->get( $this->indexable_slug )->delete( $group->id, false );
	}

	/**
	 * Determine whether syncing should take place.
	 *
	 * @return bool
	 */
	public function kill_sync(): bool {
		$kill_sync = wp_validate_boolean( parent::kill_sync() );

		/**
		 * Stop the group from being synced.
		 *
		 * @param bool $kill_sync Whether to kill the sync.
		 * @param string $indexable_slug Indexable slug.
		 */
		return apply_filters( 'elasticsearch_buddypress_group_sync_kill', $kill_sync, $this->indexable_slug );
	}
}
