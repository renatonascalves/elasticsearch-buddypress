<?php
/**
 * Elasticsearch BuddyPress Adapters: ElasticPress Adapter
 *
 * @package Elasticsearch\BuddyPress
 */

declare( strict_types=1 );

namespace Elasticsearch\BuddyPress\Adapters\ElasticPress;

use ElasticPress\Features;
use Elasticsearch\BuddyPress\Adapters\Adapter;
use Elasticsearch\BuddyPress\Adapters\ElasticPress\Features\Groups\Group;

/**
 * An adapter for the ElasticPress plugin.
 */
class ElasticPress extends Adapter {

	/**
	 * A callback for the init action hook.
	 */
	public function setup(): void {

		// Checks if ElasticPress is active.
		if ( ! class_exists( '\ElasticPress\Features' ) ) {
			add_action( 'admin_notices', [ $this, 'missing_notice' ] );
			return;
		}

		$feature = Features::factory();

		match ( true ) {
			bp_is_active( 'groups' ) => $feature->register_feature( new Group() ),
			// @todo register other features here.
			default => null,
		};
	}

	/**
	 * Registers action and/or filter hooks with WordPress.
	 */
	public function hook(): void {
		add_action( 'plugins_loaded', [ $this, 'setup' ] );
	}

	/**
	 * Unregisters action and/or filter hooks with WordPress.
	 */
	public function unhook(): void {
		remove_action( 'plugins_loaded', [ $this, 'setup' ] );
	}

	/**
	 * Missing notice.
	 */
	public function missing_notice(): void {

		// To admins only.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		?>
		<div class="notice notice-error">
			<p>
				<strong><?php esc_html_e( 'Elasticsearch BuddyPress:', 'elasticsearch-buddypress' ); ?></strong>
				<?php esc_html_e( 'the ElasticPress adapter is active but the lastest version of ElasticPress plugin is not installed or active.', 'elasticsearch-buddypress' ); ?>
			</p>
		</div>
		<?php
	}
}
