<?php

namespace YOOtheme\Builder\Wordpress\Source\Type;

use YOOtheme\Str;
use function YOOtheme\trans;

class TaxonomyType
{
    /**
     * @param \WP_Taxonomy $taxonomy
     *
     * @return array
     */
    public static function config(\WP_Taxonomy $taxonomy)
    {
        $name = Str::camelCase($taxonomy->name, true);

        return [
            'fields' =>
                [
                    'name' => [
                        'type' => 'String',
                        'metadata' => [
                            'label' => trans('Name'),
                            'filters' => ['limit'],
                        ],
                    ],

                    'description' => [
                        'type' => 'String',
                        'metadata' => [
                            'label' => trans('Description'),
                            'filters' => ['limit'],
                        ],
                    ],

                    'link' => [
                        'type' => 'String',
                        'metadata' => [
                            'label' => trans('Link'),
                        ],
                        'extensions' => [
                            'call' => __CLASS__ . '::resolveLink',
                        ],
                    ],

                    'count' => [
                        'type' => 'String',
                        'metadata' => [
                            'label' => trans('Item Count'),
                        ],
                    ],
                ] +
                ($taxonomy->hierarchical
                    ? [
                        'parent' => [
                            'type' => $name,
                            'metadata' => [
                                'label' => trans('Parent %taxonomy%', [
                                    '%taxonomy%' => $taxonomy->labels->singular_name,
                                ]),
                            ],
                            'extensions' => [
                                'call' => [
                                    'func' => __CLASS__ . '::resolveParent',
                                    'args' => ['taxonomy' => $taxonomy->name],
                                ],
                            ],
                        ],

                        'children' => [
                            'type' => [
                                'listOf' => $name,
                            ],
                            'args' => [
                                'order' => [
                                    'type' => 'String',
                                ],
                                'order_direction' => [
                                    'type' => 'String',
                                ],
                            ],
                            'metadata' => [
                                'label' => trans('Child %taxonomies%', [
                                    '%taxonomies%' => $taxonomy->labels->name,
                                ]),
                                'fields' => [
                                    '_order' => [
                                        'type' => 'grid',
                                        'width' => '1-2',
                                        'fields' => [
                                            'order' => [
                                                'label' => trans('Order'),
                                                'type' => 'select',
                                                'default' => 'term_order',
                                                'options' => [
                                                    trans('Term Order') => 'term_order',
                                                    trans('Alphabetical') => 'name',
                                                ],
                                            ],
                                            'order_direction' => [
                                                'label' => trans('Direction'),
                                                'type' => 'select',
                                                'default' => 'ASC',
                                                'options' => [
                                                    trans('Ascending') => 'ASC',
                                                    trans('Descending') => 'DESC',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'extensions' => [
                                'call' => [
                                    'func' => __CLASS__ . '::resolveChildren',
                                    'args' => ['taxonomy' => $taxonomy->name],
                                ],
                            ],
                        ],
                    ]
                    : []),

            'metadata' => [
                'type' => true,
                'label' => $taxonomy->labels->singular_name,
            ],
        ];
    }

    public static function resolveLink(\WP_Term $term)
    {
        return get_term_link($term);
    }

    public static function resolveParent(\WP_Term $term, array $args)
    {
        return $term->parent
            ? get_term($term->parent)
            : new \WP_Term((object) (['id' => 0, 'name' => 'ROOT'] + $args));
    }

    public static function resolveChildren(\WP_Term $term, array $args)
    {
        $args += [
            'order' => 'term_order',
            'order_direction' => 'ASC',
        ];

        $query = [
            'taxonomy' => $args['taxonomy'],
            'orderby' => $args['order'],
            'order' => $args['order_direction'],
            'parent' => $term->term_id,
        ];

        return get_terms($query);
    }
}
