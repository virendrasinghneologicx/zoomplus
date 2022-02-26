<?php

namespace YOOtheme\Builder\Wordpress\Woocommerce\Type;

use function YOOtheme\trans;

class AttributeFieldType
{
    public static function config()
    {
        return [
            'fields' => [
                'name' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => trans('Name'),
                    ],
                    'extensions' => [
                        'call' => __CLASS__ . '::name',
                    ],
                ],

                'value' => [
                    'type' => 'String',
                    'args' => [
                        'separator' => [
                            'type' => 'String',
                        ],
                    ],
                    'metadata' => [
                        'label' => trans('Value'),
                        'arguments' => [
                            'separator' => [
                                'label' => trans('Separator'),
                                'description' => trans('Set the separator between tags.'),
                                'default' => ', ',
                            ],
                        ],
                    ],
                    'extensions' => [
                        'call' => __CLASS__ . '::value',
                    ],
                ],
            ],
        ];
    }

    public static function name($attribute)
    {
        if (is_array($attribute)) {
            return $attribute['name'];
        }

        return wc_attribute_label($attribute->get_name());
    }

    public static function value($attribute, array $args)
    {
        if (is_array($attribute)) {
            return $attribute['value'];
        }

        $args += [
            'separator' => ', ',
        ];

        $values = [];

        if ($taxonomy = $attribute->get_taxonomy_object()) {
            foreach ($attribute->get_terms() as $value) {
                $values[] = $taxonomy->attribute_public
                    ? '<a href="' .
                        esc_url(get_term_link($value->term_id, $attribute->get_name())) .
                        '" rel="tag">' .
                        esc_html($value->name) .
                        '</a>'
                    : esc_html($value->name);
            }
        } else {
            foreach ($attribute->get_options() as $value) {
                $values[] = esc_html($value);
            }
        }

        return apply_filters(
            'woocommerce_attribute',
            wptexturize(implode($args['separator'], $values)),
            $attribute,
            $values
        );
    }
}
