<?php
/**
 * Elasticsearch BuddyPress Adapters: ElasticPress - Groups - Format Args.
 *
 * @package Elasticsearch\BuddyPress
 */

declare( strict_types=1 );

namespace Elasticsearch\BuddyPress\Adapters\ElasticPress\Features\Groups;

use Elasticsearch\BuddyPress\Adapters\FormatArgs;
use Spatie\ElasticsearchQueryBuilder\Sorts\Sort;
use Spatie\ElasticsearchQueryBuilder\Queries\TermQuery;
use Spatie\ElasticsearchQueryBuilder\Queries\TermsQuery;
use Spatie\ElasticsearchQueryBuilder\Queries\MultiMatchQuery;

/**
 * Group - Format Args.
 */
class GroupFormatArgs extends FormatArgs {

	/**
	 * Parse sort options.
	 */
	private function parse_sort(): void {
		// @todo support randon query.
		/**
		* Order by 'rand' support
		*
		* Ref: https://github.com/elastic/elasticsearch/issues/1170
		*/
		// if ( ! empty( $args['orderby'] ) ) {
		// $orderbys = $this->get_orderby_array( $args['orderby'] );
		// if ( in_array( 'rand', $orderbys, true ) ) {
		// $es_query_args                                      = $es_query_args['query'];
		// $es_query_args['query']                                   = [];
		// $es_query_args['query']['function_score']['query']        = $es_query_args;
		// $es_query_args['query']['function_score']['random_score'] = (object) [];
		// }
		// }.

		$order   = strtolower( $this->query_args['order'] ?: Sort::DESC );
		$orderby = $this->query_args['orderby'] ?: 'date_created';
		$type    = $this->query_args['type'] ?: null;

		// @todo can we set this in BP core and use a function instead?
		// @todo does it account for custom group types?
		$valid_types = [
			'active',
			'alphabetical',
			'popular',
			'random',
			'newest',
		];

		if ( ! empty( $type ) && in_array( $type, $valid_types, true ) ) {
			match ( $type ) {
				'active'        => $this->add_sort( Sort::create( 'last_activity', $order ) ),
				'alphabetical'  => $this->add_sort( Sort::create( 'name.sortable', $order ) ), // @todo Defaults to ASC in BP.
				'newest'        => $this->add_sort( Sort::create( 'date_created', $order ) ),
				'popular'       => $this->add_sort( Sort::create( 'total_member_count', $order ) ),
				'random'        => $this->add_sort( Sort::create( '_score', $order ) ), // Not actually random ).
			};
		} else {
			match ( $orderby ) {
				'date_created'       => $this->add_sort( Sort::create( 'date_created', $order ) ),
				'last_activity'      => $this->add_sort( Sort::create( 'last_activity', $order ) ),
				'meta_id'            => $this->add_sort( Sort::create( 'meta.meta_id', $order ) ), // needs schema change to index the meta_id.
				'name'               => $this->add_sort( Sort::create( 'name.raw', $order ) ),
				'random'             => $this->add_sort( Sort::create( '_score', $order ) ), // Not actually random.
				'total_member_count' => $this->add_sort( Sort::create( 'total_member_count', $order ) ),
				default              => $this->add_sort( Sort::create( 'date_created', $order ) ),
			};
		}
	}

	/**
	 * Maybe set search fields.
	 *
	 * @todo map the mysql columns to the ES equivalent fields.
	 */
	private function maybe_set_search_fields(): void {
		if ( empty( $this->query_args['search_terms'] ) ) {
			return;
		}

		$fields = [ 'name', 'description' ];

		if ( ! empty( $this->query_args['search_columns'] ) ) {
			$fields = $this->query_args['search_columns'];
		}

		/**
		 * Filters the fields to be matched by group searches.
		 *
		 * @param string[]     $fields Fields to be matched.
		 * @param array<mixed> $query_args Query arguments.
		 */
		$search_fields = apply_filters( 'elasticsearch_buddypress_group_query_search_fields', $fields, $this->query_args );

		$this->add_query(
			MultiMatchQuery::create(
				query: $this->query_args['search_terms'],
				fields: $search_fields
			)
		);
	}

	/**
	 * Maybe set the `filters`.
	 */
	private function maybe_set_filters(): void {
		if ( $this->query_args['status'] ) {
			$this->add_query( TermsQuery::create( 'status.raw', $this->query_args['status'] ) );
		} elseif ( empty( $this->query_args['show_hidden'] ) ) {
			$this->add_query( TermsQuery::create( 'status.raw', [ 'public', 'private' ] ) );
		}

		if ( $this->query_args['slug'] ) {
			$this->add_query( TermsQuery::create( 'slug.raw', $this->query_args['slug'] ) );
		}

		if ( $this->query_args['user_id'] ) {
			$this->add_query( TermQuery::create( 'group_creator.id', (string) $this->query_args['user_id'] ) );
		}

		if ( $this->query_args['parent_id'] ) {
			$this->add_query( TermsQuery::create( 'parent_id', array_values( $this->query_args['parent_id'] ) ) );
		}

		if ( $this->query_args['include'] ) {
			$this->add_query( TermsQuery::create( 'ID', array_values( $this->query_args['include'] ) ) );
		}

		if ( $this->query_args['exclude'] ) {
			$this->add_query( TermsQuery::create( 'ID', $this->query_args['exclude'] ), 'must_not' );
		}

		// @todo add support.
		if ( ! empty( $this->query_args['meta_query'] ) ) {
			/**
			 * Filters the meta keys to be excluded from group searches .
			 *
			 * @param array $exclude_meta_keys Meta keys to be excluded .
			 */
			$exclude_meta_keys = apply_filters( 'elasticsearch_buddypress_group_query_excluded_meta_keys', [] );

			foreach ( $this->query_args['meta_query'] as $meta_query ) {

				// Skip excluded meta keys.
				if ( ! empty( $exclude_meta_keys ) && in_array( $meta_query['key'], $exclude_meta_keys, true ) ) {
					continue;
				}

				$key   = 'meta.' . $meta_query['key'];
				$value = $meta_query['value'];

				if ( is_array( $value ) ) {
					$this->add_query( TermsQuery::create( $key, $value ) );
				} else {
					$this->add_query( TermQuery::create( $key, $value ) );
				}
			}
		}
	}

	/**
	 * Maybe set the `fields`.
	 */
	private function maybe_set_fields(): void {
		if ( ! isset( $this->query_args['fields'] ) ) {
			return;
		}

		switch ( $this->query_args['fields'] ) {
			case 'ids':
				$this->fields( [ 'ID' ] );
				break;
		}
	}

	/**
	 * Format query args.
	 *
	 * @return array<mixed>
	 */
	public function format(): array {

		// Bail if no query args.
		if ( empty( $this->query_args ) ) {
			return [];
		}

		$es_query_args = [
			'from'             => (int) ( $this->query_args['per_page'] * ( $this->query_args['page'] - 1 ) ),
			'size'             => (int) $this->query_args['per_page'] ?: 20,
			'track_total_hits' => true,
		];

		$this->parse_sort();
		$this->maybe_set_filters();
		$this->maybe_set_search_fields();
		$this->maybe_set_fields();

		if ( $this->query ) {
			$es_query_args['query'] = $this->query->toArray();
		}

		if ( $this->sorts ) {
			$es_query_args['sort'] = $this->sorts->toArray();
		}

		if ( $this->fields ) {
			$es_query_args['_source'] = $this->fields;
		}

		return $es_query_args;
	}
}
