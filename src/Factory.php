<?php
/**
 * Elasticsearch BuddyPress: Factory
 *
 * @package Elasticsearch\BuddyPress
 */

declare( strict_types=1 );

namespace Elasticsearch\BuddyPress;

/**
 * Factory class.
 */
class Factory {

	/**
	 * Gets an instance of the controller class, hooked.
	 *
	 * @return Controller An instance of the controller class.
	 */
	public static function controller(): Controller {
		return self::initialize( new Controller() );
	}

	/**
	 * Deinitializes the given object by calling the unhook method.
	 *
	 * @param object $instance The object instance to initialize.
	 * @return mixed The deinitialized object.
	 */
	public static function deinitialize( $instance ): mixed {
		if ( method_exists( $instance, 'unhook' ) ) {
			$instance->unhook();
		}

		return $instance;
	}

	/**
	 * Initializes the given object by calling the hook method.
	 *
	 * @param object $instance The object instance to initialize.
	 * @return mixed The initialized object.
	 */
	public static function initialize( $instance ): mixed {
		if ( method_exists( $instance, 'hook' ) ) {
			$instance->hook();
		}

		return $instance;
	}
}
