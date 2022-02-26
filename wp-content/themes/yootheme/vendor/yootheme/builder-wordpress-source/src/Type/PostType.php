<?php

namespace YOOtheme\Builder\Wordpress\Source\Type;

use function YOOtheme\app;
use YOOtheme\Arr;
use YOOtheme\Builder\Wordpress\Source\Helper;
use YOOtheme\Config;
use function YOOtheme\link_pages;
use YOOtheme\Path;
use YOOtheme\Str;
use function YOOtheme\trans;
use YOOtheme\View;

class PostType
{
    /**
     * @param \WP_Post_Type $type
     *
     * @return array
     */
    public static function config(\WP_Post_Type $type)
    {
        $taxonomies = Helper::getObjectTaxonomies($type->name);

        $fields = [
            'title' => [
                'type' => 'String',
                'metadata' => [
                    'label' => trans('Title'),
                    'filters' => ['limit'],
                ],
                'extensions' => [
                    'call' => __CLASS__ . '::title',
                ],
            ],

            'content' => [
                'type' => 'String',
                'args' => [
                    'show_intro_text' => [
                        'type' => 'Boolean',
                    ],
                ],
                'metadata' => [
                    'label' => trans('Content'),
                    'arguments' => [
                        'show_intro_text' => [
                            'label' => trans('Intro Text'),
                            'description' => trans('Show or hide the intro text.'),
                            'type' => 'checkbox',
                            'default' => true,
                            'text' => trans('Show intro text'),
                        ],
                    ],
                    'filters' => ['limit'],
                ],
                'extensions' => [
                    'call' => __CLASS__ . '::content',
                ],
            ],

            'teaser' => [
                'type' => 'String',
                'args' => [
                    'show_excerpt' => [
                        'type' => 'Boolean',
                    ],
                ],
                'metadata' => [
                    'label' => trans('Teaser'),
                    'arguments' => [
                        'show_excerpt' => [
                            'label' => trans('Excerpt'),
                            'description' => trans(
                                'Display the excerpt field if it has content, otherwise the intro text.'
                            ),
                            'type' => 'checkbox',
                            'default' => true,
                            'text' => trans('Prefer excerpt over intro text'),
                        ],
                    ],
                    'filters' => ['limit'],
                ],
                'extensions' => [
                    'call' => __CLASS__ . '::teaser',
                ],
            ],

            'excerpt' => [
                'type' => 'String',
                'metadata' => [
                    'label' => trans('Excerpt'),
                    'filters' => ['limit'],
                ],
                'extensions' => [
                    'call' => __CLASS__ . '::excerpt',
                ],
            ],

            'date' => [
                'type' => 'String',
                'metadata' => [
                    'label' => trans('Date'),
                    'filters' => ['date'],
                ],
                'extensions' => [
                    'call' => __CLASS__ . '::date',
                ],
            ],

            'modified' => [
                'type' => 'String',
                'metadata' => [
                    'label' => trans('Modified'),
                    'filters' => ['date'],
                ],
                'extensions' => [
                    'call' => __CLASS__ . '::modified',
                ],
            ],

            'commentCount' => [
                'type' => 'String',
                'metadata' => [
                    'label' => trans('Comment Count'),
                ],
                'extensions' => [
                    'call' => __CLASS__ . '::commentCount',
                ],
            ],

            'metaString' => [
                'type' => 'String',
                'args' => [
                    'format' => [
                        'type' => 'String',
                    ],
                    'separator' => [
                        'type' => 'String',
                    ],
                    'link_style' => [
                        'type' => 'String',
                    ],
                    'show_publish_date' => [
                        'type' => 'Boolean',
                    ],
                    'show_author' => [
                        'type' => 'Boolean',
                    ],
                    'show_comments' => [
                        'type' => 'Boolean',
                    ],
                    'show_taxonomy' => [
                        'type' => 'String',
                    ],
                    'date_format' => [
                        'type' => 'String',
                    ],
                ],
                'metadata' => [
                    'label' => trans('Meta'),
                    'arguments' => [
                        'format' => [
                            'label' => trans('Format'),
                            'description' => trans(
                                'Display the meta text in a sentence or a horizontal list.'
                            ),
                            'type' => 'select',
                            'default' => 'list',
                            'options' => [
                                trans('List') => 'list',
                                trans('Sentence') => 'sentence',
                            ],
                        ],
                        'separator' => [
                            'label' => trans('Separator'),
                            'description' => trans('Set the separator between fields.'),
                            'default' => '|',
                            'enable' => 'arguments.format === "list"',
                        ],
                        'link_style' => [
                            'label' => trans('Link Style'),
                            'description' => trans('Set the link style.'),
                            'type' => 'select',
                            'default' => '',
                            'options' => [
                                trans('Default') => '',
                                'Muted' => 'link-muted',
                                'Text' => 'link-text',
                                'Heading' => 'link-heading',
                                'Reset' => 'link-reset',
                            ],
                        ],
                        'show_publish_date' => [
                            'label' => trans('Display'),
                            'description' => trans('Show or hide fields in the meta text.'),
                            'type' => 'checkbox',
                            'default' => true,
                            'text' => trans('Show date'),
                        ],
                        'show_author' => [
                            'type' => 'checkbox',
                            'default' => true,
                            'text' => trans('Show author'),
                        ],
                        'show_comments' => [
                            'type' => 'checkbox',
                            'default' => true,
                            'text' => trans('Show comment count'),
                        ],
                        'show_taxonomy' => [
                            'type' => 'select',
                            'default' => $type->name === 'post' ? 'category' : '',
                            'show' => (bool) $taxonomies,
                            'options' =>
                                [
                                    trans('Hide Term List') => '',
                                ] +
                                array_combine(
                                    array_map(function ($taxonomy) {
                                        return trans('Show %taxonomy%', [
                                            '%taxonomy%' => $taxonomy->label,
                                        ]);
                                    }, $taxonomies),
                                    array_keys($taxonomies)
                                ),
                        ],
                        'date_format' => [
                            'label' => trans('Date Format'),
                            'description' => trans(
                                'Select a predefined date format or enter a custom format.'
                            ),
                            'type' => 'data-list',
                            'default' => '',
                            'options' => [
                                'Aug 6, 1999 (M j, Y)' => 'M j, Y',
                                'August 06, 1999 (F d, Y)' => 'F d, Y',
                                '08/06/1999 (m/d/Y)' => 'm/d/Y',
                                '08.06.1999 (m.d.Y)' => 'm.d.Y',
                                '6 Aug, 1999 (j M, Y)' => 'j M, Y',
                                'Tuesday, Aug 06 (l, M d)' => 'l, M d',
                            ],
                            'enable' => 'arguments.show_publish_date',
                            'attrs' => [
                                'placeholder' => 'Default',
                            ],
                        ],
                    ],
                ],
                'extensions' => [
                    'call' => __CLASS__ . '::metaString',
                ],
            ],

            'featuredImage' => [
                'type' => 'Attachment',
                'metadata' => [
                    'label' => trans('Featured Image'),
                ],
                'extensions' => [
                    'call' => __CLASS__ . '::featuredImage',
                ],
            ],

            'link' => [
                'type' => 'String',
                'metadata' => [
                    'label' => trans('Link'),
                ],
                'extensions' => [
                    'call' => __CLASS__ . '::link',
                ],
            ],

            'author' => [
                'type' => 'User',
                'metadata' => [
                    'label' => trans('Author'),
                ],
                'extensions' => [
                    'call' => __CLASS__ . '::author',
                ],
            ],
        ];

        $metadata = [
            'type' => true,
            'label' => $type->labels->singular_name,
        ];

        $features = [
            'title' => 'title',
            'author' => 'author',
            'editor' => 'content',
            'excerpt' => 'excerpt',
            'thumbnail' => 'featuredImage',
            'comments' => 'commentCount',
        ];

        // omit unsupported static features
        if ($values = array_diff_key($features, get_all_post_type_supports($type->name))) {
            $fields = Arr::omit($fields, $values);
        }

        $fields += static::configTaxonomyFields($taxonomies);
        $fields += static::configRelatedPostsField($type, $taxonomies);

        return compact('fields', 'metadata');
    }

    protected static function configTaxonomyFields($taxonomies)
    {
        $fields = [];

        foreach ($taxonomies as $taxonomy) {
            $fields[Helper::getBase($taxonomy)] = [
                'type' => [
                    'listOf' => Str::camelCase($taxonomy->name, true),
                ],

                'metadata' => [
                    'label' => $taxonomy->label,
                ],

                'extensions' => [
                    'call' => [
                        'func' => __CLASS__ . '::resolveTerms',
                        'args' => ['taxonomy' => $taxonomy->name],
                    ],
                ],
            ];

            $fields[strtr($taxonomy->name, '-', '_') . 'String'] = [
                'type' => 'String',

                'args' => [
                    'separator' => [
                        'type' => 'String',
                    ],
                    'show_link' => [
                        'type' => 'Boolean',
                    ],
                    'link_style' => [
                        'type' => 'String',
                    ],
                ],

                'metadata' => [
                    'label' => $taxonomy->label,
                    'arguments' => [
                        'separator' => [
                            'label' => trans('Separator'),
                            'description' => trans('Set the separator between terms.'),
                            'default' => ', ',
                        ],
                        'show_link' => [
                            'label' => trans('Link'),
                            'type' => 'checkbox',
                            'default' => true,
                            'text' => trans('Show link'),
                        ],
                        'link_style' => [
                            'label' => trans('Link Style'),
                            'description' => trans('Set the link style.'),
                            'type' => 'select',
                            'default' => '',
                            'options' => [
                                'Default' => '',
                                'Muted' => 'link-muted',
                                'Text' => 'link-text',
                                'Heading' => 'link-heading',
                                'Reset' => 'link-reset',
                            ],
                            'enable' => 'arguments.show_link',
                        ],
                    ],
                ],

                'extensions' => [
                    'call' => [
                        'func' => __CLASS__ . '::resolveTermString',
                        'args' => ['taxonomy' => $taxonomy->name],
                    ],
                ],
            ];
        }

        return $fields;
    }

    protected static function configRelatedPostsField(\WP_Post_Type $type, $taxonomies)
    {
        if (!$taxonomies && !post_type_supports($type->name, 'author')) {
            return [];
        }

        $args = [];
        $arguments = [];
        $base = Helper::getBase($type);

        foreach ($taxonomies as $id => $taxonomy) {
            $args[strtr($id, '-', '_')] = [
                'type' => 'String',
            ];

            $arguments[$id] = [
                'label' => current($taxonomies) === $taxonomy ? 'Relationship' : '',
                'type' => 'select',
                'default' => '',
                'options' => [
                    trans('Ignore %taxonomy%', [
                        '%taxonomy%' => $taxonomy->labels->singular_name,
                    ]) => '',
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

        $taxonomyList = implode(
            ', ',
            array_map(function ($taxonomy) {
                return $taxonomy->label;
            }, $taxonomies)
        );

        return [
            Str::camelCase(['related', $base]) => [
                'type' => [
                    'listOf' => Str::camelCase($type->name, true),
                ],

                'args' => $args + [
                    'author' => [
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

                'metadata' => [
                    'label' => trans('Related %post_type%', ['%post_type%' => $type->labels->name]),
                    'arguments' => $arguments + [
                        'author' => [
                            // TODO $taxonomies is always true at this point
                            'label' => !$taxonomies ? 'Relationship' : '',
                            'description' => $taxonomyList
                                ? trans(
                                    'Set the logical operators for how the %post_type% relate to %taxonomy_list% and author. Choose between matching at least one term, all terms or none of the terms.',
                                    [
                                        '%post_type%' => $type->labels->name,
                                        '%taxonomy_list%' => $taxonomyList,
                                    ]
                                )
                                : trans(
                                    'Set the logical operator for how the %post_type% relate to the author. Choose between matching at least one term, all terms or none of the terms.',
                                    ['%post_type%' => $type->labels->name]
                                ),
                            'type' => 'select',
                            'options' => [
                                trans('Ignore Author') => '',
                                trans('Match Author (OR)') => 'IN',
                                trans('Don\'t Match Author (NOR)') => 'NOT IN',
                            ],
                        ],

                        '_offset' => [
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
                                        ['evaluate' => "config.sources.{$type->name}OrderOptions"],
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
                    ],
                    'directives' => [],
                ],

                'extensions' => [
                    'call' => __CLASS__ . '::relatedPosts',
                ],
            ],
        ];
    }

    public static function title($post)
    {
        return get_the_title($post);
    }

    public static function content($post, $args)
    {
        global $page, $numpages, $multipage;

        $args += ['show_intro_text' => true];

        // Hint: this returns different results depending on the current view (archive vs. single page)
        $content = get_the_content('', !$args['show_intro_text'], $post);
        $content = str_replace("<span id=\"more-{$post->ID}\"></span>", '', $content);

        if ($multipage && Helper::isPageSource($post)) {
            $title = sprintf(__('Page %s of %s', 'yootheme'), $page, $numpages);
            $content =
                '<p class="uk-text-meta tm-page-break' .
                ($page == '1' ? ' tm-page-break-first-page' : '') .
                "\">{$title}</p>{$content}" .
                link_pages();
        }

        if (!has_blocks($content) && !app(Config::class)('~theme.disable_wpautop')) {
            // trim leading whitespace, because ` </div>` results in `</p></div>
            $content = wpautop(preg_replace('/^\s+<\//m', '</', $content));
        }

        return $content;
    }

    public static function teaser($post, $args)
    {
        $args += ['show_excerpt' => true];

        if ($args['show_excerpt'] && has_excerpt($post)) {
            return get_the_excerpt($post);
        }

        $extended = get_extended($post->post_content);
        $teaser = $extended['main'];

        // Having multiple `<!-- wp:more -->` blocks confuses the parse_blocks function
        if (has_blocks($teaser)) {
            $teaser = do_blocks($teaser);
        }

        return $teaser;
    }

    public static function excerpt($post)
    {
        return get_the_excerpt($post);
    }

    public static function date($post)
    {
        return $post->post_date;
    }

    public static function modified($post)
    {
        return $post->post_modified;
    }

    public static function commentCount($post)
    {
        return $post->comment_count;
    }

    public static function link($post)
    {
        $link = get_permalink($post);

        return is_string($link) ? $link : null;
    }

    public static function featuredImage($post)
    {
        return get_post_thumbnail_id($post) ?: null;
    }

    public static function author($post)
    {
        return get_userdata($post->post_author) ?: null;
    }

    public static function metaString($post, array $args)
    {
        $args += [
            'format' => 'list',
            'separator' => '|',
            'link_style' => '',
            'show_publish_date' => true,
            'show_author' => true,
            'show_comments' => true,
            'show_taxonomy' => $post->post_type === 'post' ? 'category' : '',
            'date_format' => '',
        ];

        return app(View::class)->render(Path::get('../../templates/meta'), compact('post', 'args'));
    }

    public static function resolveTerms($post, array $args)
    {
        return wp_get_post_terms($post->ID, $args['taxonomy']);
    }

    public static function resolveTermString($post, array $args)
    {
        $args += ['separator' => ', ', 'show_link' => true, 'link_style' => ''];
        $before = $args['link_style'] ? "<span class=\"uk-{$args['link_style']}\">" : '';
        $after = $args['link_style'] ? '</span>' : '';

        if ($args['show_link']) {
            return get_the_term_list(
                $post->ID,
                $args['taxonomy'],
                $before,
                $args['separator'],
                $after
            ) ?:
                null;
        }

        $terms = get_the_terms($post->ID, $args['taxonomy']);

        return $terms
            ? implode(
                $args['separator'],
                array_map(function ($term) {
                    return $term->name;
                }, $terms)
            )
            : null;
    }

    public static function relatedPosts($post, array $args)
    {
        $args += ['post_type' => $post->post_type, 'terms' => []];

        $args['exclude'] = array_merge(
            [$post->ID],
            !empty($args['exclude']) ? $args['exclude'] : []
        );

        $taxonomyNames = get_taxonomies();
        $taxonomies = Arr::pick($args, function ($arg, $name) use ($taxonomyNames) {
            return in_array($name, $taxonomyNames);
        });

        foreach (array_filter($taxonomies) as $taxonomy => $operator) {
            $args['terms'] = array_merge(
                $args['terms'],
                wp_get_post_terms($post->ID, $taxonomy, ['fields' => 'ids']) ?: []
            );
            $args["{$taxonomy}_operator"] = $operator;
        }

        if (!empty($args['author'])) {
            $args['users'] = [$post->post_author];
            $args['users_operator'] = $args['author'];
        }

        return CustomPostQueryType::resolvePosts($post, $args);
    }
}
