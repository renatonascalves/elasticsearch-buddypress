<?php
/**
 * Elasticsearch BuddyPress Adapters: ElasticPress - Groups Indexable
 *
 * @package Elasticsearch\BuddyPress
 */

declare( strict_types=1 );

namespace Elasticsearch\BuddyPress\Adapters\ElasticPress\Features\Groups;

use WP_Term;
use WP_User;
use WP_Taxonomy;
use WP_Error;
use ElasticPress\Indexable as EP_Indexable;

/**
 * BuddyPress Groups Indexable for ElasticPress.
 */
class Indexable extends EP_Indexable {

	/**
	 * We only need one group index.
	 *
	 * @todo maybe more with multisite?
	 *
	 * @var bool
	 */
	public $global = true;

	/**
	 * Indexable slug.
	 *
	 * @var string
	 */
	public $slug = 'bp-group';

	/**
	 * Create indexable and setup dependencies.
	 */
	public function __construct() {
		$this->labels = [
			'plural'   => esc_html__( 'Groups', 'elasticsearch-buddypress' ),
			'singular' => esc_html__( 'Group', 'elasticsearch-buddypress' ),
		];

		$this->sync_manager      = new SyncManager( $this->slug );
		$this->query_integration = new QueryIntegration( $this->slug );
	}

	/**
	 * Generate group mapping.
	 *
	 * @return array<mixed>
	 */
	public function generate_mapping(): array {
		$mapping = require plugin_dir_path( __FILE__ ) . '/mapping.php';

		/**
		 * Filter the group mapping.
		 *
		 * @param array $mapping Group mapping.
		 */
		return (array) apply_filters( 'elasticsearch_buddypress_group_mapping', (array) $mapping );
	}

	/**
	 * Prepare a group document for indexing.
	 *
	 * @param int $group_id Group ID.
	 * @return array<mixed>|false
	 */
	public function prepare_document( $group_id ): array|false {
		$group = groups_get_group( $group_id );

		if ( empty( $group->id ) ) {
			return false;
		}

		$last_activity = (string) groups_get_groupmeta( $group->id, 'last_activity', true );

		if ( empty( $last_activity ) ) {
			$last_activity = $group->date_created;
		}

		$document = [
			'ID'                   => $group->id,
			'group_id'             => $group->id,
			'group_creator'        => $this->prepare_creator_data( $group->creator_id ),
			'date_created'         => $group->date_created,
			'date_created_gmt'     => get_date_from_gmt( $group->date_created ),
			'description'          => $group->description,
			'description_filtered' => apply_filters( 'bp_get_group_description', $group->description ), // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			'enable_forum'         => $group->enable_forum,
			'last_activity'        => $last_activity,
			'last_activity_gmt'    => get_date_from_gmt( $last_activity ),
			'meta'                 => $this->prepare_meta_types( $this->prepare_meta( $group->id ) ),
			'name'                 => $group->name,
			'parent_id'            => $group->parent_id,
			'permalink'            => bp_get_group_url( $group ),
			'terms'                => $this->prepapre_terms( $group->id ),
			'slug'                 => $group->slug,
			'status'               => $group->status,
			'total_member_count'   => absint( groups_get_groupmeta( $group->id, 'total_member_count', true ) ),
		];

		/**
		 * Filter the group document for indexing.
		 *
		 * @param array<mixed> $document Group document.
		 * @param int          $group_id Group ID.
		 */
		return apply_filters( 'elasticsearch_buddypress_group_sync_args', $document, $group_id );
	}

	/**
	 * Prepare group creator data.
	 *
	 * @param int $creator_id Group creator id.
	 * @return array<mixed>
	 */
	public function prepare_creator_data( int $creator_id ): array {
		$user = get_userdata( $creator_id );

		if ( $user instanceof WP_User ) {
			$user_data = [
				'display_name' => $user->display_name,
				'id'           => $user->ID,
				'login'        => $user->user_login,
				'raw'          => $user->user_login,
			];
		} else {
			$user_data = [
				'display_name' => '',
				'id'           => '',
				'login'        => '',
				'raw'          => '',
			];
		}

		return $user_data;
	}

	/**
	 * Prepare group terms.
	 *
	 * @param int $group_id Group ID.
	 * @return array<mixed>
	 */
	public function prepapre_terms( int $group_id ): array {
		$selected_taxonomies = $this->get_indexable_group_taxonomies();

		if ( empty( $selected_taxonomies ) ) {
			return [];
		}

		$terms = [];

		/**
		 * Filter to allow child terms to be indexed.
		 *
		 * @param bool $allow True means allow.
		 */
		$allow_hierarchy = (bool) apply_filters( 'elasticsearch_buddypress_sync_terms_allow_hierarchy', true );

		foreach ( $selected_taxonomies as $taxonomy ) {
			$object_terms = bp_get_object_terms( $group_id, $taxonomy->name );

			if ( ! $object_terms || $object_terms instanceof WP_Error ) {
				continue;
			}

			$formatted_terms = [];

			foreach ( $object_terms as $term ) {
				if ( ! isset( $formatted_terms[ $term->term_id ] ) ) {
					$formatted_terms[ $term->term_id ] = $this->get_formatted_term( $term );

					if ( $allow_hierarchy ) {
						$formatted_terms = $this->get_parent_terms( $formatted_terms, $term, $taxonomy->name, $group_id );
					}
				}
			}

			$terms[ $taxonomy->name ] = array_values( $formatted_terms );
		}

		return $terms;
	}

	/**
	 *
	 * Prepare group meta.
	 *
	 * @param int $group_id Group ID.
	 * @return array<mixed>
	 */
	public function prepare_meta( int $group_id ): array {
		$meta = (array) groups_get_groupmeta( $group_id, '', false );

		if ( empty( $meta ) ) {
			return [];
		}

		$prepared_meta = [];
		$excluded_keys = [
			// Indexed as a top-level property.
			'last_activity',
			'total_member_count',
		];

		/**
		 * Filter index-able private meta.
		 *
		 * Allows for specifying private meta keys that may be indexed in the same manor as public meta keys.
		 *
		 * @param array $keys     Array of index-able private meta keys.
		 * @param int   $group_id Group ID to be indexed.
		 */
		$allowed_protected_keys = (array) apply_filters( 'elasticsearch_buddypress_prepare_group_meta_allowed_protected_keys', [], $group_id );

		/**
		 * Filter non-indexed public meta.
		 *
		 * Allows for specifying public meta keys that should be excluded from the ElasticPress index.
		 *
		 * @param array $keys     Array of public meta keys to exclude from index.
		 * @param int   $group_id Group ID to be indexed.
		 */
		$excluded_public_keys = (array) apply_filters( 'elasticsearch_buddypress_prepare_group_meta_excluded_public_keys', $excluded_keys, $group_id );

		foreach ( $meta as $key => $value ) {
			$allow_index = false;

			if ( is_protected_meta( $key ) && in_array( $key, $allowed_protected_keys, true ) ) {
				$allow_index = true;
			} elseif ( ! in_array( $key, $excluded_public_keys, true ) ) {
				$allow_index = true;
			}

			/**
			 * Filter whether to index a specific group meta key.
			 *
			 * We need this extra filter to allow for blocking dynamic keys.
			 *
			 * @param bool   $allow_index Whether to index the meta key.
			 * @param string $key         Meta key.
			 * @param int    $group_id    Group ID.
			 */
			$allow_index = (bool) apply_filters( 'elasticsearch_buddypress_allow_index_group_meta_key', $allow_index, $key, $group_id );

			if ( true === $allow_index ) {
				$prepared_meta[ $key ] = maybe_unserialize( $value );
			}
		}

		return $prepared_meta;
	}

	/**
	 * Query DB for groups.
	 *
	 * @global wpdb $wpdb WordPress database object.
	 *
	 * @param array<mixed> $args Query arguments.
	 * @return array<mixed>
	 */
	public function query_db( $args ): array {
		global $wpdb;

		$bp = buddypress();

		$defaults = [
			'number'  => 350,
			'offset'  => 0,
			'orderby' => 'date_created',
			'order'   => 'DESC',
		];

		$args = bp_parse_args( $args, $defaults, 'elasticsearch_buddypress_group_query_db' );

		if ( isset( $args['per_page'] ) ) {
			$args['number'] = $args['per_page'];
		}

		$sql = $wpdb->prepare(
			"SELECT SQL_CALC_FOUND_ROWS id as ID FROM {$bp->groups->table_name} ORDER BY %s LIMIT %d, %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$args['orderby'],
			(int) $args['offset'],
			(int) $args['number']
		);

		$objects = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared

		return [
			'objects'       => $objects,
			'total_objects' => 0 === count( $objects ) ? 0 : (int) $wpdb->get_var( 'SELECT FOUND_ROWS()' ), // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
		];
	}

	/**
	 * Format query arguments into an ES query.
	 *
	 * @param array<mixed> $args Query arguments.
	 * @return array<mixed>
	 */
	public function format_args( array $args ): array {
		$formatted_args = ( new GroupFormatArgs( $args ) )->format();

		/**
		 * Filters the query arguments for group queries.
		 *
		 * @param array<mixed> $formatted_args Formatted arguments.
		 * @param array<mixed> $args           Query arguments.
		 */
		return apply_filters( 'elasticsearch_buddypress_group_query_args', $formatted_args, $args );
	}

	/**
	 * Get an array of taxonomies that are indexable for groups.
	 *
	 * @return array<int<0, max>, string|WP_Taxonomy>
	 */
	private function get_indexable_group_taxonomies(): array {
		$group_taxonomies = get_object_taxonomies( 'bp_group', 'objects' );

		/**
		 * Filter taxonomies to be synced with the group.
		 *
		 * @param string[]|WP_Taxonomy[] $group_taxonomies Group taxonomies.
		 */
		$selected_taxonomies = (array) apply_filters( 'elasticsearch_buddypress_sync_taxonomies', $group_taxonomies );

		$validated_taxonomies = [];
		foreach ( $selected_taxonomies as $selected_taxonomy ) {
			// If we get a taxonomy name, we need to convert it to taxonomy object.
			if ( ! $selected_taxonomy instanceof WP_Taxonomy && taxonomy_exists( (string) $selected_taxonomy ) ) {
				$selected_taxonomy = get_taxonomy( $selected_taxonomy );

				// If we get a taxonomy name that doesn't exist, skip it.
				if ( ! $selected_taxonomy instanceof WP_Taxonomy ) {
					continue;
				}
			}

			$validated_taxonomies[] = $selected_taxonomy;
		}

		return $validated_taxonomies;
	}

	/**
	 * Given a term, format it to be appended to the group ES document.
	 *
	 * @param WP_Term $term Term to be formatted.
	 * @return array<mixed>
	 */
	private function get_formatted_term( WP_Term $term ): array {
		$formatted_term = [
			'term_id'          => $term->term_id,
			'slug'             => $term->slug,
			'name'             => $term->name,
			'parent'           => $term->parent,
			'term_taxonomy_id' => $term->term_taxonomy_id,
			'term_order'       => 0,
		];

		$formatted_term['facet'] = wp_json_encode( $formatted_term );

		return $formatted_term;
	}

	/**
	 * Recursively get all the ancestor terms of the given term.
	 *
	 * @param array<mixed> $terms         Terms array.
	 * @param WP_Term      $term          Current term.
	 * @param string       $taxonomy_name Taxonomy name.
	 * @param int          $group_id      Group ID.
	 * @return array<mixed>
	 */
	private function get_parent_terms( array $terms, WP_Term $term, string $taxonomy_name, int $group_id ): array {
		$parent_term = get_term( $term->parent, $taxonomy_name );

		if ( ! $parent_term instanceof WP_Term ) {
			return $terms;
		}

		if ( ! isset( $terms[ $parent_term->term_id ] ) ) {
			$terms[ $parent_term->term_id ] = $this->get_formatted_term( $parent_term );

		}

		return $this->get_parent_terms( $terms, $parent_term, $taxonomy_name, $group_id );
	}
}
