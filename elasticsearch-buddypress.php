<?php
/**
 * Elasticsearch BuddyPress
 *
 * @package  Elasticsearch\BuddyPress
 * @author   Renato Alves
 * @version  0.1.0-alpha
 * @license  GPL-3.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Elasticsearch BuddyPress
 * Plugin URI:        https://github.com/renatonascalves/elasticsearch-buddypress
 * GitHub Plugin URI: https://github.com/renatonascalves/elasticsearch-buddypress
 * Description:       BuddyPress integration with Elasticsearch.
 * Version:           0.1.0-alpha
 * Author:            Renato Alves
 * Author URI:        https://ralv.es
 * Text Domain:       elasticsearch-buddypress
 * Requires PHP:      8.3
 * Requires WP:       6.1
 * Tested up to:      6.5.2
 * Requires Plugins:  buddypress, elasticpress
 * License:           GPL-3.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 */

declare(strict_types=1);

namespace Elasticsearch\BuddyPress;

/* Start Composer Loader */

// Check if Composer is installed.
if ( ! file_exists( __DIR__ . '/vendor/wordpress-autoload.php' ) ) {
	if ( ! class_exists( \Composer\InstalledVersions::class ) ) {
		\add_action(
			'admin_notices',
			function () {
				?>
				<div class="notice notice-error">
					<p>
						<?php
						esc_html_e(
							'ElasticSearch BuddyPress appears to have been installed without its dependencies. It will not work properly until dependencies are installed. This likely means you have cloned it from Github and need to run the command `composer install`.',
							'elasticsearch-buddypress'
						);
						?>
					</p>
				</div>
				<?php
			}
		);

		return;
	}
} else {
	// Load Composer dependencies.
	require_once __DIR__ . '/vendor/wordpress-autoload.php';
}

/* End Composer Loader */

/**
 * Getting the instance of the controller class.
 *
 * @return Controller
 */
function elasticsearch_buddypress(): Controller {
	return Registry::controller();
}

// Bootstrap the plugin.
elasticsearch_buddypress()->load_adapters();
