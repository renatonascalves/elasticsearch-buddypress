<?php
/**
 * Elasticsearch BuddyPress Tests: Bootstrap File.
 *
 * @since 0.1.0-alpha
 * @package Elasticsearch\BuddyPress\Tests
 */

declare(strict_types=1);

namespace Elasticsearch\BuddyPress\Tests;

use function Mantle\Testing\tests_add_filter;

\Mantle\Testing\manager()
	->before(
		function () {
			// TODO: remove it after Mantle shims factories.
			require_once dirname( __FILE__, 2 ) . '/vendor/wp-phpunit/wp-phpunit/includes/factory.php';

			// Define constants.
			require_once dirname( __FILE__ ) . '/includes/define-constants.php';

			// Define bootstrap helper functions.
			require_once dirname( __FILE__ ) . '/includes/bootstrap-functions.php';

			if ( ! file_exists( BP_TESTS_DIR . '/includes/loader.php' ) ) {
				die( "The BuddyPress plugin could not be found.\n" );
			}
		}
	)
	->loaded(
		function () {
			// @todo This is not playing nice with Mantle.
			// Load BuddyPress.
			require_once BP_TESTS_DIR . '/includes/loader.php';

			// Load adapter plugins.
			require_once dirname( __FILE__, 3 ) . '/elasticpress/elasticpress.php';

			// Load our plugin.
			require_once dirname( __FILE__, 2 ) . '/elasticsearch-buddypress.php';
		}
	)
	->after(
		function () {
			// Boot up ES.
			elasticsearch_bootup();

			// Activate all components.
			tests_add_filter( 'bp_is_active', '__return_true' );

			// @todo This is not playing nice with Mantle.
			// echo "Loading BuddyPress testcases...\n";
			// require_once BP_TESTS_DIR . '/includes/testcase.php';

			// Loading testcases.
			require_once dirname( __FILE__ ) . '/testcases/AdapterUnitTestCase.php';
			require_once dirname( __FILE__ ) . '/testcases/ElasticPressTestCase.php';

			uses( ElasticPressTestCase::class )->in( 'adapters/elasticpress' );
		}
	)
	->install();
