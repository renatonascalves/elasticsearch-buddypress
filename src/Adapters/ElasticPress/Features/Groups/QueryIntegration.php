<?php
/**
 * Elasticsearch BuddyPress Adapters: ElasticPress - Groups Query Integration
 *
 * @package Elasticsearch\BuddyPress
 */

declare(strict_types=1);

namespace Elasticsearch\BuddyPress\Adapters\ElasticPress\Features\Groups;

use ElasticPress\Indexables;
use ElasticPress\Utils;

/**
 * Groups query integration class.
 */
readonly class QueryIntegration {

	/**
	 * Setup actions and filters.
	 *
	 * @param string $indexable_slug Indexable slug.
	 */
	public function __construct( private string $indexable_slug ) {

		/**
		 * Filter whether to enable query integration during indexing.
		 *
		 * @param bool $enable To allow query integration during indexing.
		 * @param string $indexable_slug Indexable slug.
		 */
		$allow_query_integration_during_indexing = apply_filters( 'elasticsearch_buddypress_enable_group_query_integration_during_indexing', false, $this->indexable_slug );

		if ( ! $allow_query_integration_during_indexing && Utils\is_indexing() ) {
			return;
		}

		add_filter( 'bp_after_bp_groups_group_get_parse_args', [ $this, 'maybe_filter_bp_groups_group_get_parse_args' ] );

		add_filter( 'bp_groups_get_paged_groups_sql', [ $this, 'maybe_filter_pagination_query' ], 10, 3 );
		add_filter( 'bp_groups_get_total_groups_sql', [ $this, 'maybe_filter_total_groups_sql' ], 10, 3 );
	}

	/**
	 * Filters the parsed arguments for the get method.
	 *
	 * @param array<mixed> $parsed_args Array of parsed arguments.
	 * @return array<mixed>
	 */
	public function maybe_filter_bp_groups_group_get_parse_args( $parsed_args ): array {

		// Check if we are on the groups component.
		// @todo this might be too broad. We might need to check for specific group queries instead.
		if ( bp_is_groups_component() ) {
			$parsed_args['ep_integrate'] = true;
		}

		return $parsed_args;
	}

	/**
	 * Filters the SQL used to retrieve group results.
	 *
	 * @param string       $groups_sql Concatenated SQL statement.
	 * @param array<mixed> $sql        Array of SQL parts for the query.
	 * @param array<mixed> $r          Array of parsed arguments.
	 * @return string
	 */
	public function maybe_filter_pagination_query( $groups_sql, $sql, $r ): string {
		$group_indexable = Indexables::factory()->get( $this->indexable_slug );

		/**
		 * Filter whether to skip group query integration.
		 *
		 * @param bool         $skip Whether to skip group query integration. Defaults to `false`.
		 * @param array<mixed> $r    Array of parsed arguments.
		 */
		$skip_group_query_integration = apply_filters( 'elasticsearch_buddypress_skip_group_query_integration', false, $r );

		if ( ! $group_indexable->elasticpress_enabled( $r ) || true === $skip_group_query_integration ) {
			return $groups_sql;
		}

		$formatted_args = $group_indexable->format_args( $r );
		$static_results = $this->static_results( $formatted_args );
		$ep_query       = is_null( $static_results )
			? $group_indexable->query_es( $formatted_args, $r )
			: $static_results;

		if ( empty( $ep_query ) || empty( $ep_query['found_documents']['value'] ?? 0 ) ) {
			return $groups_sql;
		}

		$this->static_results( $formatted_args, $ep_query );

		$document_ids = array_map(
			static fn ( $document ) => $document['ID'],
			$ep_query['documents']
		);

		$bp  = buddypress();
		$ids = implode( ',', wp_parse_id_list( $document_ids ) );

		if ( empty( $ids ) ) {
			$ids = 0;
		}

		return sprintf( 'SELECT DISTINCT g.id FROM %s g WHERE g.id IN (%s)', $bp->groups->table_name, $ids );
	}

	/**
	 * Filters the SQL used to retrieve total group results.
	 *
	 * @global wpdb $wpdb WordPress database object.
	 *
	 * @param string       $total_groups_sql Concatenated SQL statement used for retrieving total group results.
	 * @param array<mixed> $sql              Array of SQL parts for the query.
	 * @param array<mixed> $r                Array of parsed arguments.
	 * @return string
	 */
	public function maybe_filter_total_groups_sql( $total_groups_sql, $sql, $r ): string {
		global $wpdb;

		$group_indexable = Indexables::factory()->get( $this->indexable_slug );

		/**
		 * Filter whether to skip group query integration.
		 *
		 * @param bool         $skip Whether to skip group query integration. Defaults to `false`.
		 * @param array<mixed> $r    Array of parsed arguments.
		 */
		$skip_group_query_integration = apply_filters( 'elasticsearch_buddypress_skip_group_query_integration', false, $r );

		if ( ! $group_indexable->elasticpress_enabled( $r ) || true === $skip_group_query_integration ) {
			return $total_groups_sql;
		}

		$formatted_args = $group_indexable->format_args( $r );

		// Get from static cache if available.
		$cached = $this->static_results( $formatted_args );

		if ( empty( $cached ) ) {
			return $total_groups_sql;
		}

		return $wpdb->prepare( 'SELECT %d', absint( $cached['found_documents']['value'] ?? 0 ) );
	}

	/**
	 * Get or/and set static cached results.
	 *
	 * @param array<mixed>      $formatted_args Formatted arguments.
	 * @param array<mixed>|null $ep_query EP query. Defaults to `null`.
	 * @return array<mixed>|null
	 */
	protected function static_results( array $formatted_args, ?array $ep_query = null ): ?array {
		static $results;

		$args_key = md5( (string) wp_json_encode( $formatted_args ) );

		if ( ! is_null( $ep_query ) ) {
			$results[ $args_key ] = $ep_query;
		}

		return $results[ $args_key ] ?? null;
	}
}
