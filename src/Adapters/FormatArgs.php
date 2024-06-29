<?php
/**
 * Elasticsearch BuddyPress Format Args
 *
 * @package Elasticsearch\BuddyPress
 */

declare(strict_types=1);

namespace Elasticsearch\BuddyPress\Adapters;

use Elasticsearch\BuddyPress\Interfaces\Formatter;
use Spatie\ElasticsearchQueryBuilder\Sorts\Sort;
use Spatie\ElasticsearchQueryBuilder\Queries\Query;
use Spatie\ElasticsearchQueryBuilder\SortCollection;
use Spatie\ElasticsearchQueryBuilder\Queries\BoolQuery;

/**
 * Format Args.
 */
abstract class FormatArgs implements Formatter {

	/**
	 * Queries.
	 *
	 * @var BoolQuery|null
	 */
	protected ?BoolQuery $query = null;

	/**
	 * Sorts.
	 *
	 * @var SortCollection|null
	 */
	protected ?SortCollection $sorts = null;

	/**
	 * Fields.
	 *
	 * @var array<mixed>|null
	 */
	protected ?array $fields = null;

	/**
	 * Query args.
	 *
	 * @param array<mixed> $query_args Query args.
	 */
	public function __construct( protected array $query_args ) {}

	/**
	 * Add fields.
	 *
	 * @param array<mixed> $fields Fields.
	 * @return static
	 */
	public function fields( array $fields ): static {
		$this->fields = array_unique( array_merge( $this->fields ?? [], $fields ) );

		return $this;
	}

	/**
	 * Add query.
	 *
	 * @param Query  $query Query.
	 * @param string $type Bool type. Default 'must'.
	 * @return static
	 */
	public function add_query( Query $query, string $type = 'must' ): static {
		if ( ! $this->query ) {
			$this->query = new BoolQuery();
		}

		$this->query->add( $query, $type );

		return $this;
	}

	/**
	 * Add sort.
	 *
	 * @param Sort $sort Sort object.
	 * @return static
	 */
	public function add_sort( Sort $sort ): static {

		if ( ! $this->sorts ) {
			$this->sorts = new SortCollection();
		}

		$this->sorts->add( $sort );

		return $this;
	}
}
