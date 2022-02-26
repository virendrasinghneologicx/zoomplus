<?php

namespace YOOtheme;

use YOOtheme\Builder\Wordpress\Woocommerce\Helper;

return [
    'transforms' => [
        'render' => function ($node) {
            $content = Helper::renderTemplate('woocommerce_template_single_rating');

            if (empty($content)) {
                return false;
            }

            $node->props['content'] = $content;
        },
    ],
];
