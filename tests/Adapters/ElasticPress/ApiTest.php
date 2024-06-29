<?php
/**
 * Elasticsearch BuddyPress Tests: ElasticPress API Tests.
 *
 * @since 0.1.0-alpha
 * @package Elasticsearch\BuddyPress\Tests
 */

declare(strict_types=1);

namespace Elasticsearch\BuddyPress\Tests;

use \ElasticPress\Elasticsearch;

it(
	'gets Elasticsearch version',
	function (): void {
		expect( Elasticsearch::factory()->get_elasticsearch_version( true ) )->
		toBeString();
	}
);

it(
	'gets the ep_host value',
	function (): void {
		expect( get_option( 'ep_host' ) )->toBe( 'http://localhost:9200' );
	}
);
