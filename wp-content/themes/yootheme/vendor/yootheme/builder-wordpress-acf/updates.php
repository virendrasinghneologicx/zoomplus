<?php

namespace YOOtheme;

return [
    '2.6.3' => function ($node, array $params) {
        if (
            !empty($node->source->query->field->name) &&
            str_contains($node->source->query->field->name, 'field.')
        ) {
            $node->source->query->field->name = implode(
                '.',
                array_map(
                    [Str::class, 'snakeCase'],
                    explode('.', $node->source->query->field->name)
                )
            );
        }

        if (!empty($node->source->props)) {
            foreach ((array) $node->source->props as $prop) {
                if (!empty($prop->name) && str_contains($prop->name, 'field.')) {
                    $prop->name = implode(
                        '.',
                        array_map([Str::class, 'snakeCase'], explode('.', $prop->name))
                    );
                }
            }
        }
    },
];
