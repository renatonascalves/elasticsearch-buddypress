<?php
/**
 * Elasticsearch BuddyPress Interfaces: Hookable
 *
 * @package Elasticsearch\BuddyPress
 */

declare(strict_types=1);

namespace Elasticsearch\BuddyPress\Interfaces;

/**
 * An interface for classes that need to register and unregister hooks for
 * integrating with WordPress.
 */
interface Hookable {

	/**
	 * Registers action and/or filter hooks with WordPress.
	 */
	public function hook(): void;

	/**
	 * Unregisters action and/or filter hooks that were registered in the hook
	 * method.
	 */
	public function unhook(): void;
}
