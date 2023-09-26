<?php
/**
 * Elasticsearch BuddyPress Tests: AdapterUnitTestCase class.
 *
 * @package Elasticsearch\BuddyPress\Tests
 */

declare(strict_types=1);

namespace Elasticsearch\BuddyPress\Tests;

use Pest\PestPluginWordPress\FrameworkTestCase;

/**
 * AdapterUnitTestCase class.
 */
abstract class AdapterUnitTestCase extends FrameworkTestCase {

	/**
	 * Set up the test case.
	 */
	public function set_up(): void {
		parent::set_up();
	}
}
