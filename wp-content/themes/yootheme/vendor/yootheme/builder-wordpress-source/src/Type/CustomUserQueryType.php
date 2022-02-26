<?php

namespace YOOtheme\Builder\Wordpress\Source\Type;

use function YOOtheme\trans;

class CustomUserQueryType
{
    /**
     * @return array
     */
    public static function config()
    {
        return [
            'fields' => [
                'customUser' => [
                    'type' => 'User',

                    'args' => [
                        'id' => [
                            'type' => 'Int',
                        ],
                    ],

                    'metadata' => [
                        'label' => trans('Custom User'),
                        'group' => 'Custom',
                        'fields' => [
                            'id' => [
                                'label' => trans('User'),
                                'type' => 'select',
                                'defaultIndex' => 0,
                                'options' => [['evaluate' => 'config.users']],
                            ],
                        ],
                    ],

                    'extensions' => [
                        'call' => __CLASS__ . '::resolve',
                    ],
                ],
            ],
        ];
    }

    public static function resolve($root, array $args)
    {
        if (empty($args['id'])) {
            return;
        }

        return get_userdata($args['id']);
    }
}
