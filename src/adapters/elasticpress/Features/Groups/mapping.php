<?php
/**
 * Elasticsearch BuddyPress mapping config for the groups Feature.
 *
 * @package Elasticsearch\BuddyPress
 */

declare(strict_types=1);

return [
	'settings' => [
		'index.mapping.total_fields.limit' => 5000,
		'index.max_result_window'          => 1000000,
		'analysis'                         => [
			'analyzer'   => [
				'default'          => [
					'tokenizer' => 'standard',
					'filter'    => [ 'ewp_word_delimiter', 'lowercase', 'stop', 'ewp_snowball' ],
					'language'  => apply_filters( 'ep_analyzer_language', 'english', 'analyzer_default' ), // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
				],
				'shingle_analyzer' => [
					'type'      => 'custom',
					'tokenizer' => 'standard',
					'filter'    => [ 'lowercase', 'shingle_filter' ],
				],
				'ewp_lowercase'    => [
					'type'      => 'custom',
					'tokenizer' => 'keyword',
					'filter'    => [ 'lowercase' ],
				],
			],
			'filter'     => [
				'shingle_filter'     => [
					'type'             => 'shingle',
					'min_shingle_size' => 3,
					'max_shingle_size' => 5,
				],
				'ewp_word_delimiter' => [
					'type'              => 'word_delimiter',
					'preserve_original' => true,
				],
				'ewp_snowball'       => [
					'type'     => 'snowball',
					'language' => apply_filters( 'ep_analyzer_language', 'english', 'filter_ewp_snowball' ), // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
				],
				'edge_ngram'         => [
					'side'     => 'front',
					'max_gram' => 10,
					'min_gram' => 3,
					'type'     => 'edgeNGram',
				],
			],
			'normalizer' => [
				'lowerasciinormalizer' => [
					'type'   => 'custom',
					'filter' => [ 'lowercase', 'asciifolding' ],
				],
			],
		],
	],
	'mappings' => [
		'date_detection'    => true,
		'properties'        => [
			'group_id'             => [
				'type' => 'long',
			],
			'ID'                   => [
				'type' => 'long',
			],
			'name'                 => [
				'type'   => 'text',
				'fields' => [
					'name'     => [
						'type'     => 'text',
						'analyzer' => 'standard',
					],
					'raw'      => [
						'type'         => 'keyword',
						'ignore_above' => 10922,
					],
					'sortable' => [
						'type'         => 'keyword',
						'ignore_above' => 10922,
						'normalizer'   => 'lowerasciinormalizer',
					],
				],
			],
			'creator'              => [
				'type'       => 'object',
				'properties' => [
					'display_name' => [
						'type'   => 'text',
						'fields' => [
							'raw'      => [
								'type' => 'keyword',
							],
							'sortable' => [
								'type'       => 'keyword',
								'normalizer' => 'lowerasciinormalizer',
							],
						],
					],
					'login'        => [
						'type'   => 'text',
						'fields' => [
							'raw'      => [
								'type' => 'keyword',
							],
							'sortable' => [
								'type'       => 'keyword',
								'normalizer' => 'lowerasciinormalizer',
							],
						],
					],
					'id'           => [
						'type' => 'long',
					],
					'raw'          => [
						'type' => 'keyword',
					],
				],
			],
			'description'          => [
				'type' => 'text',
			],
			'description_filtered' => [
				'type' => 'text',
			],
			'slug'                 => [
				'type'   => 'text',
				'fields' => [
					'slug' => [
						'type' => 'text',
					],
					'raw'  => [
						'type'         => 'keyword',
						'ignore_above' => 10922,
					],
				],
			],
			'permalink'            => [
				'type' => 'keyword',
			],
			'status'               => [
				'type'   => 'text',
				'fields' => [
					'status'   => [
						'type' => 'text',
					],
					'raw'      => [
						'type'         => 'keyword',
						'ignore_above' => 10922,
					],
					'sortable' => [
						'type'         => 'keyword',
						'ignore_above' => 10922,
						'normalizer'   => 'lowerasciinormalizer',
					],
				],
			],
			'meta'                 => [
				'type' => 'object',
			],
			'terms'                => [
				'type' => 'object',
			],
			'enable_forum'         => [
				'type' => 'long',
			],
			'parent_id'            => [
				'type' => 'long',
			],
			'last_activity'        => [
				'type'   => 'date',
				'format' => 'yyyy-MM-dd HH:mm:ss',
			],
			'last_activity_gmt'    => [
				'type'   => 'date',
				'format' => 'yyyy-MM-dd HH:mm:ss',
			],
			'date_created'         => [
				'type'   => 'date',
				'format' => 'YYYY-MM-dd HH:mm:ss',
			],
			'date_created_gmt'     => [
				'type'   => 'date',
				'format' => 'YYYY-MM-dd HH:mm:ss',
			],
			'total_member_count'   => [
				'type' => 'long',
			],
		],
		'dynamic_templates' => [
			[
				'template_terms' => [
					'path_match' => 'terms.*',
					'mapping'    => [
						'type'       => 'object',
						'properties' => [
							'name'             => [
								'type'   => 'text',
								'fields' => [
									'raw'      => [
										'type' => 'keyword',
									],
									'sortable' => [
										'type'       => 'keyword',
										'normalizer' => 'lowerasciinormalizer',
									],
								],
							],
							'term_id'          => [
								'type' => 'long',
							],
							'term_taxonomy_id' => [
								'type' => 'long',
							],
							'parent'           => [
								'type' => 'long',
							],
							'slug'             => [
								'type' => 'keyword',
							],
							'facet'            => [
								'type' => 'keyword',
							],
							'term_order'       => [
								'type' => 'long',
							],
						],
					],
				],
			],
			[
				'term_suggest' => [
					'path_match' => 'term_suggest_*',
					'mapping'    => [
						'type'     => 'completion',
						'analyzer' => 'default',
					],
				],
			],
			[
				'template_meta_types' => [
					'path_match' => 'meta.*',
					'mapping'    => [
						'type'       => 'nested',
						'path'       => 'full',
						'properties' => [
							'value'    => [
								'type'   => 'keyword',
								'fields' => [
									'sortable' => [
										'type'         => 'keyword',
										'ignore_above' => 10922,
										'normalizer'   => 'lowerasciinormalizer',
									],
									'raw'      => [
										'type'         => 'keyword',
										'ignore_above' => 10922,
									],
								],
							],
							'raw'      => [ /* Left for backwards compat */
								'type'         => 'keyword',
								'ignore_above' => 10922,
							],
							'meta_id'  => [
								'type' => 'long',
							],
							'long'     => [
								'type' => 'long',
							],
							'double'   => [
								'type' => 'double',
							],
							'boolean'  => [
								'type' => 'boolean',
							],
							'date'     => [
								'type'   => 'date',
								'format' => 'yyyy-MM-dd',
							],
							'datetime' => [
								'type'   => 'date',
								'format' => 'yyyy-MM-dd HH:mm:ss',
							],
							'time'     => [
								'type'   => 'date',
								'format' => 'HH:mm:ss',
							],
						],
					],
				],
			],
		],
	],
];
