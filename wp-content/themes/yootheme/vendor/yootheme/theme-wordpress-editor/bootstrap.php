<?php

namespace YOOtheme\Theme\Wordpress;

return [
    'events' => [
        'customizer.init' => [
            EditorListener::class => 'enqueueEditor',
        ],
    ],

    'actions' => [
        'wp_tiny_mce_init' => [
            EditorListener::class => 'enqueueEditorJs',
        ],
    ],
];
