<?php

namespace YOOtheme;

return [
    'updates' => [
        '2.7.3.1' => function ($node) {
            if (empty($node->props['panel_style']) && empty($node->props['panel_padding'])) {
                foreach ($node->children as $child) {
                    if (
                        isset($child->props->panel_style) &&
                        preg_match('/^card-/', $child->props->panel_style)
                    ) {
                        $node->props['panel_padding'] = 'default';
                        break;
                    }
                }
            }
        },

        '2.7.0-beta.0.5' => function ($node) {
            if (
                isset($node->props['panel_style']) &&
                preg_match('/^card-/', $node->props['panel_style'])
            ) {
                if (empty($node->props['panel_card_size'])) {
                    $node->props['panel_card_size'] = 'default';
                }
                $node->props['panel_padding'] = $node->props['panel_card_size'];
                unset($node->props['panel_card_size']);
            }
        },

        '2.7.0-beta.0.1' => function ($node) {
            if (isset($node->props['panel_content_padding'])) {
                $node->props['panel_padding'] = $node->props['panel_content_padding'];
                unset($node->props['panel_content_padding']);
            }

            if (isset($node->props['panel_size'])) {
                $node->props['panel_card_size'] = $node->props['panel_size'];
                unset($node->props['panel_size']);
            }

            if (isset($node->props['panel_card_image'])) {
                $node->props['panel_image_no_padding'] = $node->props['panel_card_image'];
                unset($node->props['panel_card_image']);
            }
        },
    ],
];
