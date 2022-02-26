<?php

namespace YOOtheme;

return [
    'transforms' => [
        'render' => function ($node, $params) {
            return (bool) $node->props['location'];
        },
    ],
];
