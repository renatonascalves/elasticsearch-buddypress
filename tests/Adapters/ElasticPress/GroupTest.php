<?php
/**
 * Elasticsearch BuddyPress Tests: Groups Tests.
 *
 * @package Elasticsearch\BuddyPress\Tests
 */

declare( strict_types=1 );

namespace Elasticsearch\BuddyPress\Tests;

test(
	'buddypress groups is active',
	function () {
		expect( bp_is_active( 'groups' ) )->toBe( true );
	}
);
