<?php

namespace YOOtheme;

use YOOtheme\Builder\UpdateTransform;
use YOOtheme\Builder\Wordpress\Woocommerce\RenderTransform;
use YOOtheme\Builder\Wordpress\Woocommerce\SourceListener;
use YOOtheme\Builder\Wordpress\Woocommerce\TemplateListener;

if (!class_exists('WooCommerce', false)) {
    return [];
}

return [
    'filters' => [
        'template_include' => [
            TemplateListener::class => ['onTemplateInclude', 80],
        ],
    ],

    'events' => [
        'source.init' => [
            SourceListener::class => ['initSource', -10],
        ],

        'source.object.taxonomies' => [
            SourceListener::class => 'filterTaxonomy',
        ],

        'customizer.init' => [
            SourceListener::class => 'initCustomizer',
        ],
    ],

    'extend' => [
        Builder::class => function (Builder $builder) {
            // add transform on single product page
            if (is_product()) {
                $builder->addTransform('render', new RenderTransform());
            }

            $builder->addTypePath(Path::get('./elements/*/element.json'));
        },

        UpdateTransform::class => function (UpdateTransform $transform) {
            $transform->addGlobals(require __DIR__ . '/updates.php');

            return $transform;
        },
    ],
];
