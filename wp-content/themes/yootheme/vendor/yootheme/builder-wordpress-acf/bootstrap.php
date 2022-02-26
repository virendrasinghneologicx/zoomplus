<?php

namespace YOOtheme\Builder\Wordpress\Acf;

use YOOtheme\Builder\UpdateTransform;

// is acf installed?
if (!is_callable('acf_get_fields')) {
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
