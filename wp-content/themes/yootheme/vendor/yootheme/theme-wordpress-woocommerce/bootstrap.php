<?php

namespace YOOtheme\Theme\Wordpress;

use YOOtheme\Path;

$config = [
    'events' => [
        'customizer.init' => [
            WooCommerceListener::class => 'initCustomizer',
        ],

        'styler.imports' => [
            WooCommerceListener::class => 'stylerImports',
        ],
    ],
];

if (!class_exists('WooCommerce', false)) {
    return $config;
}

return array_merge_recursive($config, [
    'theme' => function () {
        return [
            'styles' => [
                'imports' => [
                    'search' => Path::get('../../assets/uikit/src/images/icons/search.svg'),
                ],
            ],
        ];
    },

    'events' => [
        'theme.breadcrumbs' => [
            WooCommerceListener::class => 'breadcrumbs',
        ],
    ],

    'actions' => [
        'wp_enqueue_scripts' => [
            WooCommerceListener::class => ['removeSelect', 100],
        ],

        'woocommerce_before_add_to_cart_form' => [
            WooCommerceListener::class => 'beforeAddToCartForm',
        ],
    ],

    'filters' => [
        'woocommerce_enqueue_styles' => [
            WooCommerceListener::class => 'removeStyle',
        ],

        'woocommerce_product_review_comment_form_args' => [
            WooCommerceListener::class => 'reviewCommentFormArgs',
        ],

        'wp_nav_menu_objects' => [
            WooCommerceListener::class => ['navMenuObjects', 10, 2],
        ],

        'woocommerce_add_to_cart_fragments' => [
            WooCommerceListener::class => 'addToCartFragments',
        ],

        'woocommerce_variable_price_html' => [
            WooCommerceListener::class => ['variablePriceHtml', 10, 2],
        ],

        'woocommerce_grouped_price_html' => [
            WooCommerceListener::class => ['groupedPriceHtml', 10, 3],
        ],

        'woocommerce_format_sale_price' => [
            WooCommerceListener::class => ['formatSalePrice', 10, 3],
        ],

        'woocommerce_product_thumbnails_columns' => [
            WooCommerceListener::class => 'productThumbnailsColumns',
        ],

        'woocommerce_product_review_list_args' => [
            WooCommerceListener::class => 'productReviewListArgs',
        ],
    ],
]);
