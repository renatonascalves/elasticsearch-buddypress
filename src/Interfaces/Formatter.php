<?php
/**
 * Elasticsearch BuddyPress Interfaces: Formatter
 *
 * @package Elasticsearch\BuddyPress
 */

declare( strict_types=1 );

namespace Elasticsearch\BuddyPress\Interfaces;

/**
 * An interface for classes that need to format query vars into an ES query.
 */
interface Formatter {

	/**
	 * Format query args.
	 *
	 * @return array<mixed>
	 */
	public function format(): array;
}
