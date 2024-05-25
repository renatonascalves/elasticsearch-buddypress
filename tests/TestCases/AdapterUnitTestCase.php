<?php
/**
 * Elasticsearch BuddyPress Tests: AdapterUnitTestCase class.
 *
 * @package Elasticsearch\BuddyPress\Tests
 */

declare( strict_types=1 );

namespace Elasticsearch\BuddyPress\Tests\TestCases;

use Mantle\Testkit\Test_Case;

/**
 * AdapterUnitTestCase class.
 */
abstract class AdapterUnitTestCase extends Test_Case {

	/**
	 * Set up the test case.
	 */
	public function set_up(): void {
		parent::set_up();
	}

	/**
	 * Tear down the test case.
	 */
	public function tear_down(): void {
		parent::tear_down();
	}
}
