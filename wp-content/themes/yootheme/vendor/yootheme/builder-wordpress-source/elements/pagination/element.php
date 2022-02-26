<?php

namespace YOOtheme;

return [
    'transforms' => [
        'render' => function ($node) {
            $getItem = function ($text, $link, $active = true) {
                return (object) compact('active', 'text', 'link');
            };

            if (is_single()) {
                $node->props['pagination_type'] = 'previous/next';
                $taxonomy = $node->props['pagination_taxonomy'] ?: '';

                $previous = get_previous_post((bool) $taxonomy, '', $taxonomy ?: 'category');
                $next = get_next_post((bool) $taxonomy, '', $taxonomy ?: 'category');

                $node->props['pagination'] = [
                    'previous' => $previous
                        ? $getItem(__('Previous', 'yootheme'), get_permalink($previous))
                        : null,
                    'next' => $next ? $getItem(__('Next', 'yootheme'), get_permalink($next)) : null,
                ];

                return true;
            }

            global $wp_query;

            if ($wp_query->max_num_pages <= 1) {
                return false;
            }

            $total = isset($wp_query->max_num_pages) ? $wp_query->max_num_pages : 1;
            $current = (int) get_query_var('paged') ?: 1;
            $endSize = 1;
            $midSize = 3;
            $dots = false;
            $pagination = [];

            if ($current > 1 && ($previous = previous_posts(false))) {
                $pagination['previous'] = $getItem(__('Previous', 'yootheme'), $previous, false);
            }

            for ($n = 1; $n <= $total; $n++) {
                $active =
                    $n <= $endSize ||
                    ($current && $n >= $current - $midSize && $n <= $current + $midSize) ||
                    $n > $total - $endSize;

                if ($active || $dots) {
                    $pagination[$n] = $getItem(
                        $active ? number_format_i18n($n) : __('&hellip;'),
                        $active ? get_pagenum_link($n, false) : '',
                        $n === $current
                    );
                    $dots = $active;
                }
            }

            if ($current < $total && ($next = next_posts($wp_query->max_num_pages, false))) {
                $pagination['next'] = $getItem(__('Next', 'yootheme'), $next, false);
            }

            $node->props['pagination'] = $pagination;
        },
    ],
];
