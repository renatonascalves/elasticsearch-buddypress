<?php
/**
 * Elasticsearch BuddyPress: Registry
 *
 * @package Elasticsearch\BuddyPress
 */

declare(strict_types=1);

namespace Elasticsearch\BuddyPress;

/**
 * Registry class.
 */
class Registry {

	/**
	 * Keeps a list of active instances of classes, keyed by identifier.
	 *
	 * @var array<string, Controller>
	 */
	private static array $registry = [];

	/**
	 * Gets the active instance of the controller.
	 *
	 * @return Controller The requested class instance.
	 */
	public static function controller(): Controller {
		if ( ! isset( self::$registry['controller'] ) ) {
			self::$registry['controller'] = Factory::controller();
		}

		return self::$registry['controller'];
	}
}
