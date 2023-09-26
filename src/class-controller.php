<?php
/**
 * Elasticsearch BuddyPress: Controller
 *
 * @package Elasticsearch\BuddyPress
 */

declare(strict_types=1);

namespace Elasticsearch\BuddyPress;

use Elasticsearch\BuddyPress\Adapters\Adapter;
use Elasticsearch\BuddyPress\Adapters\ElasticPress\ElasticPress;
use Elasticsearch\BuddyPress\Interfaces\Hookable;

/**
 * The controller class, which is responsible for loading adapters and
 * configuration.
 */
class Controller implements Hookable {

	/**
	 * The active adapter.
	 *
	 * @var Adapter
	 */
	private Adapter $adapter;

	/**
	 * A callback for the init action hook.
	 *
	 * @todo Confirm if this is still needed.
	 */
	public function action__init(): void {
		/**
		 * An action hook that fires after this plugin is initialized and is
		 * ready for configuration.
		 *
		 * @param Controller $controller The Elasticsearch BuddyPress controller class.
		 */
		do_action( 'elasticsearch_buddypress_init_config', $this );
	}

	/**
	 * Registers action and/or filter hooks with WordPress.
	 */
	public function hook(): void {
		add_action( 'init', [ $this, 'action__init' ], 1000 );
	}

	/**
	 * Unregisters action and/or filter hooks with WordPress.
	 */
	public function unhook(): void {
		remove_action( 'init', [ $this, 'action__init' ], 1000 );
	}

	/**
	 * Load adapter.
	 *
	 * @todo do we need more than one adapter active at the same time?
	 *
	 * @param ?Adapter $adapter The adapter to load.
	 */
	public function load_adapters( ?Adapter $adapter = null ): void {

		// Checks if BuddyPress is installed.
		if ( ! class_exists( 'BuddyPress' ) ) {
			add_action( 'admin_notices', [ $this, 'missing_notice' ] );
			return;
		}

		if ( ! is_null( $adapter ) ) {
			$this->adapter = $adapter;
		} else {
			$this->adapter = Factory::initialize( new ElasticPress() );
		}
	}

	/**
	 * BuddyPress missing notice.
	 */
	public function missing_notice(): void {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		?>
		<div class="notice notice-error">
			<p>
				<strong><?php esc_html_e( 'Elasticsearch BuddyPress', 'elasticsearch-buddypress' ); ?></strong>
				<?php esc_html_e( 'depends on the lastest version of Buddypress to work!', 'elasticsearch-buddypress' ); ?>
			</p>
		</div>
		<?php
	}
}
