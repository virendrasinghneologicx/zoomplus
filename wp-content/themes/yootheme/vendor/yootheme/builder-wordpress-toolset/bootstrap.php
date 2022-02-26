<?php

namespace YOOtheme\Builder\Wordpress\Toolset;

use YOOtheme\Builder\UpdateTransform;

// is toolset active?
if (
    !in_array(
        'types/wpcf.php',
        apply_filters('active_plugins', (array) get_option('active_plugins', []))
    )
) {
    return [];
}

return [
    'events' => [
        'source.init' => [
            SourceListener::class => ['initSource', -10],
        ],

        'customizer.init' => [
            SourceListener::class => ['initCustomizer', 10],
        ],
    ],

    'extend' => [
        UpdateTransform::class => function (UpdateTransform $update) {
            $update->addGlobals(require __DIR__ . '/updates.php');
        },
    ],
];
