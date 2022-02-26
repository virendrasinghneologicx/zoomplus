<?php

namespace YOOtheme;

return [
    'transforms' => [
        'render' => function ($node, $params) {
            // Display
            foreach (['meta', 'content', 'image', 'link', 'label', 'thumbnail'] as $key) {
                if (!$params['parent']->props["show_{$key}"]) {
                    $node->props[$key] = '';
                }
            }

            // Don't render element if content fields are empty
            // First part checks for title, second part checks for content
            return (Str::length($node->props['title']) ||
                Str::length($node->props['label']) ||
                $node->props['thumbnail'] ||
                $node->props['image']) &&
                ((Str::length($node->props['title']) && $params['parent']->props['show_title']) ||
                    Str::length($node->props['meta']) ||
                    Str::length($node->props['content']) ||
                    $node->props['image']);
        },
    ],
];
