<?php

namespace YOOtheme\Builder\Wordpress\Source\Type;

use YOOtheme\Arr;
use YOOtheme\Builder\Wordpress\Source\Helper;
use YOOtheme\Str;
use function YOOtheme\trans;

class CustomPostQueryType
{
    /**
     * @param \WP_Post_Type $type
     *
     * @return array
     */
    public static function config(\WP_Post_Type $type)
    {
        $name = Str::camelCase($type->name, true);
        $base = Str::camelCase(Helper::getBase($type), true);

        $plural = Str::lower($type->label);
        $singular = Str::lower($type->labels->singular_name);

        $taxonomies = Helper::getObjectTaxonomies($type->name);

        ksort($taxonomies);

        $terms = $taxonomies
            ? [
                'label' => trans('Filter by Terms'),
                'type' => 'select',
                'default' => [],
                'options' => array_map(function ($taxonomy) {
                    return ['evaluate' => "config.taxonomies.{$taxonomy}"];
                }, array_keys($taxonomies)),
                'attrs' => [
                    'multiple' => true,
                    'class' => 'uk-height-medium',
                ],
            ]
            : [];

        $operators = [];

        foreach ($taxonomies as $taxonomy) {
            $operators[strtr($taxonomy->name, '-', '_') . '_operator'] = [
                'description' =>
                    end($taxonomies) === $taxonomy
                        ? trans(
                            'Filter %post_types% by terms. Use the <kbd>shift</kbd> or <kbd>ctrl/cmd</kbd> key to select multiple terms. Set the logical operator to match at least one of the terms, none of the terms or all terms.',
                            ['%post_types%' => $plural]
                        )
                        : '',
                'type' => 'select',
                'default' => 'IN',
                'options' => [
                    trans('Match One %taxonomy% (OR)', [
                        '%taxonomy%' => $taxonomy->labels->singular_name,
                    ]) => 'IN',
                    trans('Match All %taxonomies% (AND)', [
                        '%taxonomies%' => $taxonomy->label,
                    ]) => 'AND',
                    trans('Don\'t Match %taxonomies% (NOR)', [
                        '%taxonomies%' => $taxonomy->label,
                    ]) => 'NOT IN',
                ],
            ];
        }

        return [
            'fields' => [
                "custom{$name}" => [
                    'type' => $name,

                    'args' => array_merge(
                        [
                            'id' => [
                                'type' => 'Int',
                            ],
                            'terms' => [
                                'type' => [
                                    'listOf' => 'Int',
                                ],
                            ],
                            'users' => [
                                'type' => [
                                    'listOf' => 'Int',
                                ],
                            ],
                            'users_operator' => [
                                'type' => 'String',
                            ],
                            'offset' => [
                                'type' => 'Int',
                            ],
                            'order' => [
                                'type' => 'String',
                            ],
                            'order_direction' => [
                                'type' => 'String',
                            ],
                            'order_alphanum' => [
                                'type' => 'Boolean',
                            ],
                        ],
                        array_map(function () {
                            return ['type' => 'String'];
                        }, $operators)
                    ),

                    'metadata' => [
                        'label' => trans('Custom %post_type%', [
                            '%post_type%' => $type->labels->singular_name,
                        ]),
                        'group' => 'Custom',
                        'fields' => array_merge(
                            [
                                'id' => [
                                    'label' => trans('Select Manually'),
                                    'description' => trans(
                                        'Pick a %post_type% manually or use filter options to specify which %post_type% should be loaded dynamically.',
                                        ['%post_type%' => $singular]
                                    ),
                                    'type' => 'select-item',
                                    'post_type' => $type->name,
                                    'labels' => [
                                        'type' => $type->labels->singular_name,
                                    ],
                                ],
                            ],
                            $terms
                                ? [
                                        'terms' => $terms + [
                                            'enable' => '!id',
                                        ],
                                    ] +
                                    array_map(function ($operator) {
                                        return $operator + ['enable' => '!id'];
                                    }, $operators)
                                : [],
                            [
                                'users' => [
                                    'label' => trans('Filter by Users'),
                                    'type' => 'select',
                                    'default' => [],
                                    'options' => [['evaluate' => 'config.users']],
                                    'attrs' => [
                                        'multiple' => true,
                                        'class' => 'uk-height-small',
                                    ],
                                    'enable' => '!id',
                                ],
                                'users_operator' => [
                                    'description' => trans(
                                        'Filter %post_types% by users. Use the <kbd>shift</kbd> or <kbd>ctrl/cmd</kbd> key to select multiple users. Set the logical operator to match or not match the selected users.',
                                        ['%post_types%' => $plural]
                                    ),
                                    'type' => 'select',
                                    'default' => 'IN',
                                    'options' => [
                                        trans('Match (OR)') => 'IN',
                                        trans('Don\'t Match (NOR)') => 'NOT IN',
                                    ],
                                    'enable' => '!id',
                                ],
                                'offset' => [
                                    'label' => trans('Start'),
                                    'description' => trans(
                                        'Set the starting point to specify which %post_type% is loaded.',
                                        ['%post_type%' => $singular]
                                    ),
                                    'type' => 'number',
                                    'default' => 0,
                                    'modifier' => 1,
                                    'attrs' => [
                                        'min' => 1,
                                        'required' => true,
                                    ],
                                    'enable' => '!id',
                                    '@order' => 50,
                                ],
                                '_order' => [
                                    'type' => 'grid',
                                    'width' => '1-2',
                                    'fields' => [
                                        'order' => [
                                            'label' => trans('Order'),
                                            'type' => 'select',
                                            'default' => 'date',
                                            'options' => [
                                                [
                                                    'evaluate' =>
                                                        'config.sources.postTypeOrderOptions',
                                                ],
                                                [
                                                    'evaluate' => "config.sources.{$type->name}OrderOptions",
                                                ],
                                            ],
                                            'enable' => '!id',
                                        ],
                                        'order_direction' => [
                                            'label' => trans('Direction'),
                                            'type' => 'select',
                                            'default' => 'DESC',
                                            'options' => [
                                                trans('Ascending') => 'ASC',
                                                trans('Descending') => 'DESC',
                                            ],
                                            'enable' => '!id',
                                        ],
                                    ],
                                    '@order' => 60,
                                ],
                                'order_alphanum' => [
                                    'text' => trans('Alphanumeric Ordering'),
                                    'type' => 'checkbox',
                                    'enable' => '!id',
                                    '@order' => 70,
                                ],
                            ]
                        ),
                    ],

                    'extensions' => [
                        'call' => [
                            'func' => __CLASS__ . '::resolvePost',
                            'args' => ['post_type' => $type->name],
                        ],
                    ],
                ],

                "custom{$base}" => [
                    'type' => [
                        'listOf' => $name,
                    ],

                    'args' => array_merge(
                        [
                            'terms' => [
                                'type' => [
                                    'listOf' => 'Int',
                                ],
                            ],
                            'users' => [
                                'type' => [
                                    'listOf' => 'Int',
                                ],
                            ],
                            'users_operator' => [
                                'type' => 'String',
                            ],
                            'offset' => [
                                'type' => 'Int',
                            ],
                            'limit' => [
                                'type' => 'Int',
                            ],
                            'order' => [
                                'type' => 'String',
                            ],
                            'order_direction' => [
                                'type' => 'String',
                            ],
                            'order_alphanum' => [
                                'type' => 'Boolean',
                            ],
                        ],
                        array_map(function () {
                            return ['type' => 'String'];
                        }, $operators)
                    ),
                    'metadata' => [
                        'label' => trans('Custom %post_types%', ['%post_types%' => $type->label]),
                        'group' => 'Custom',
                        'fields' => array_merge($terms ? ['terms' => $terms] + $operators : [], [
                            'users' => [
                                'label' => trans('Filter by Users'),
                                'type' => 'select',
                                'default' => [],
                                'options' => [['evaluate' => 'config.users']],
                                'attrs' => [
                                    'multiple' => true,
                                    'class' => 'uk-height-small',
                                ],
                            ],
                            'users_operator' => [
                                'description' => trans(
                                    'Filter %post_types% by users. Use the <kbd>shift</kbd> or <kbd>ctrl/cmd</kbd> key to select multiple users. Set the logical operator to match or not match the selected users.',
                                    ['%post_types%' => $plural]
                                ),
                                'type' => 'select',
                                'default' => 'IN',
                                'options' => [
                                    trans('Match (OR)') => 'IN',
                                    trans('Don\'t Match (NOR)') => 'NOT IN',
                                ],
                            ],
                            '_offset' => [
                                'description' => trans(
                                    'Set the starting point and limit the number of %post_types%.',
                                    ['%post_types%' => $plural]
                                ),
                                'type' => 'grid',
                                'width' => '1-2',
                                'fields' => [
                                    'offset' => [
                                        'label' => trans('Start'),
                                        'type' => 'number',
                                        'default' => 0,
                                        'modifier' => 1,
                                        'attrs' => [
                                            'min' => 1,
                                            'required' => true,
                                        ],
                                    ],
                                    'limit' => [
                                        'label' => trans('Quantity'),
                                        'type' => 'limit',
                                        'default' => 10,
                                        'attrs' => [
                                            'min' => 1,
                                        ],
                                    ],
                                ],
                                '@order' => 50,
                            ],
                            '_order' => [
                                'type' => 'grid',
                                'width' => '1-2',
                                'fields' => [
                                    'order' => [
                                        'label' => trans('Order'),
                                        'type' => 'select',
                                        'default' => 'date',
                                        'options' => [
                                            ['evaluate' => 'config.sources.postTypeOrderOptions'],
                                            [
                                                'evaluate' => "config.sources.{$type->name}OrderOptions",
                                            ],
                                        ],
                                    ],
                                    'order_direction' => [
                                        'label' => trans('Direction'),
                                        'type' => 'select',
                                        'default' => 'DESC',
                                        'options' => [
                                            trans('Ascending') => 'ASC',
                                            trans('Descending') => 'DESC',
                                        ],
                                    ],
                                ],
                                '@order' => 60,
                            ],
                            'order_alphanum' => [
                                'text' => trans('Alphanumeric Ordering'),
                                'type' => 'checkbox',
                                '@order' => 70,
                            ],
                        ]),
                    ],

                    'extensions' => [
                        'call' => [
                            'func' => __CLASS__ . '::resolvePosts',
                            'args' => ['post_type' => $type->name],
                        ],
                    ],
                ],
            ],
        ];
    }

    public static function resolvePost($root, array $args)
    {
        if (!empty($args['id'])) {
            return get_post($args['id']);
        }

        if ($posts = static::resolvePosts($root, ['limit' => 1] + $args)) {
            return array_shift($posts);
        }
    }

    public static function resolvePosts($root, array $args)
    {
        $args += [
            'order' => 'date',
            'order_direction' => 'DESC',
            'offset' => 0,
            'limit' => 10,
            'users_operator' => 'IN',
        ];

        $query = [
            'post_status' => 'publish',
            'post_type' => $args['post_type'],
            'orderby' => $args['order'],
            'order' => $args['order_direction'],
            'offset' => $args['offset'],
            'numberposts' => $args['limit'],
            'suppress_filters' => false,
        ];

        if (!empty($args['include'])) {
            $query['include'] = $args['include'];
        }

        if (!empty($args['exclude'])) {
            $query['exclude'] = $args['exclude'];
        }

        if (!empty($args['terms'])) {
            $taxonomies = [];

            foreach ($args['terms'] as $id) {
                if (($term = get_term($id)) && $term instanceof \WP_Term) {
                    $taxonomies[$term->taxonomy][] = $id;
                }
            }

            foreach ($taxonomies as $taxonomy => $terms) {
                $query['tax_query'][] = [
                    'taxonomy' => $taxonomy,
                    'terms' => $terms,
                    'operator' => Arr::get($args, "{$taxonomy}_operator", 'IN'),
                    'field' => 'term_id',
                    'include_children' => false,
                ];
            }
        }

        if (!empty($args['users'])) {
            $query[$args['users_operator'] === 'IN' ? 'author__in' : 'author__not_in'] =
                $args['users'];
        }

        if (str_starts_with($query['orderby'], 'field:')) {
            $query['meta_key'] = substr($query['orderby'], 6);
            $query['orderby'] = 'meta_value';
        }

        if (!empty($args['order_alphanum']) && $args['order'] !== 'rand') {
            Helper::filterOnce('posts_orderby', Helper::orderAlphanum($query));
        }

        return get_posts($query);
    }
}
