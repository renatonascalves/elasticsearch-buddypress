<?php
/**
 * Elasticsearch BuddyPress Adapters: ElasticPress - Groups Feature
 *
 * @package Elasticsearch\BuddyPress
 */

declare(strict_types=1);

namespace Elasticsearch\BuddyPress\Adapters\ElasticPress\Features\Groups;

use ElasticPress\Feature;
use ElasticPress\Indexables;

/**
 * BuddyPress Groups feature for ElasticPress.
 */
class Group extends Feature {

	/**
	 * Initialize groups feature.
	 */
	public function __construct() {
		$this->slug = 'bp-groups';

		$this->title = esc_html__( 'BuddyPress Groups', 'elasticsearch-buddypress' );

		$this->summary = esc_html__( 'Improve BuddyPress groups search relevancy and query performance.', 'elasticsearch-buddypress' );

		$this->requires_install_reindex = false;

		parent::__construct();
	}

	/**
	 * Setup feature.
	 */
	public function setup(): void {
		$indexable = Indexables::factory();

		$indexable->register(
			indexable: new Indexable(),
			activate: false
		);

		$indexable->activate( slug: 'bp-group' );

		add_action( 'init', [ $this, 'search_setup' ] );
	}

	/**
	 * Search setup.
	 */
	public function search_setup(): void {
		add_filter( 'ep_elasticpress_enabled', [ $this, 'integrate_search_queries' ], 10, 2 );
	}

	/**
	 * Enable integration on, search, queries.
	 *
	 * @param bool         $enabled Whether to integrate with Elasticsearch or not.
	 * @param array<mixed> $query The query to evaluate.
	 * @return bool
	 */
	public function integrate_search_queries( $enabled, $query ): bool {

		// BuddyPress doesn't pass an object.
		if ( ! is_array( $query ) ) {
			return $enabled;
		}

		if ( ! array_key_exists( 'group_type', $query ) ) {
			return $enabled;
		}

		return match ( true ) {
			! empty( $query['search_terms'] ) => true,
			isset( $query['ep_integrate'] ) && wp_validate_boolean( $query['ep_integrate'] ) => true,
			default => $enabled,
		};
	}
}
