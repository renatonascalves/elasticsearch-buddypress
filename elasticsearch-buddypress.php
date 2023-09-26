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
 * Domain Path:       /languages/
 * Requires PHP:      8.2
 * Requires WP:       5.9
 * Tested up to:      6.3
 * License:           GPL-3.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 */

declare(strict_types=1);

namespace Elasticsearch\BuddyPress;

// Plugin autoloader.
require_once __DIR__ . '/vendor/wordpress-autoload.php';

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
