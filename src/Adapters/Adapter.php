<?php
/**
 * Elasticsearch BuddyPress Adapters: Adapter Abstract Class
 *
 * @package Elasticsearch\BuddyPress
 */

declare( strict_types=1 );

namespace Elasticsearch\BuddyPress\Adapters;

use Elasticsearch\BuddyPress\Interfaces\Hookable;

/**
 * An abstract class that establishes base functionality and sets requirements
 * for implementing classes.
 */
abstract class Adapter implements Hookable {

	/**
	 * Set up the adapter.
	 */
	abstract public function setup(): void;

	/**
	 * Missing notice.
	 */
	abstract public function missing_notice(): void;
}
