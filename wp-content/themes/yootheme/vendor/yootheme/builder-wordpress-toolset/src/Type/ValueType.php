<?php

namespace YOOtheme\Builder\Wordpress\Toolset\Type;

use function YOOtheme\trans;

class ValueType
{
    public static function config()
    {
        return [
            'fields' => [
                'value' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => trans('Value'),
                    ],
                ],
            ],
        ];
    }

    public static function configDate()
    {
        return [
            'fields' => [
                'value' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => trans('Date'),
                        'filters' => ['date'],
                    ],
                ],
            ],
        ];
    }

    public static function configString()
    {
        $config = self::config();

        return [
            'extensions' => [
                'call' => __CLASS__ . '::resolveString',
            ],
        ] + $config;
    }

    public static function resolveString($item, $args, $context, $info)
    {
        $args += ['separator' => ', '];

        return join($args['separator'], array_column($item, $info->fieldName));
    }
}
