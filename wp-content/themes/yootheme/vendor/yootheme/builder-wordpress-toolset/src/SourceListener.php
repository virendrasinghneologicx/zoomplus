<?php

namespace YOOtheme\Builder\Wordpress\Toolset;

use YOOtheme\Builder\Wordpress\Source\Helper as SourceHelper;
use YOOtheme\Builder\Wordpress\Toolset\Type\ValueType;
use YOOtheme\Config;
use YOOtheme\Str;
use function YOOtheme\trans;

class SourceListener
{
    public static function initSource($source)
    {
        $source->objectType('ToolsetValueField', ValueType::config());

        $source->objectType('ToolsetValueStringField', ValueType::configString());

        $source->objectType('ToolsetDateField', ValueType::configDate());

        // add user fields
        if ($fields = Helper::fieldsGroups('user')) {
            static::configFields($source, 'User', $fields);
        }

        // add post fields
        foreach (SourceHelper::getPostTypes() as $type) {
            if ($fields = Helper::fieldsGroups('post', $type->name)) {
                static::configFields($source, $type->name, $fields);
            }

            if ($relationships = Helper::getRelationships($type->name)) {
                static::configRelationshipFields($source, $type->name, $relationships);
            }
        }

        // add taxonomy fields
        foreach (SourceHelper::getTaxonomies() as $taxonomy) {
            if ($fields = Helper::fieldsGroups('term', $taxonomy->name)) {
                static::configFields($source, $taxonomy->name, $fields);
            }
        }
    }

    public static function configFields($source, $name, array $fields)
    {
        $type = Str::camelCase([$name, 'Toolset'], true);

        // add field on type
        $source->objectType(Str::camelCase($name, true), [
            'fields' => [
                'toolset' => [
                    'type' => $type,
                    'metadata' => [
                        'label' => trans('Fields'),
                    ],
                    'extensions' => [
                        'call' => Type\FieldsType::class . '::toolset',
                    ],
                ],
            ],
        ]);

        $source->objectType($type, Type\FieldsType::config($source, $fields));
    }

    public static function initCustomizer(Config $config)
    {
        foreach (SourceHelper::getPostTypes() as $type) {
            $groups = [];

            foreach (Helper::groups('post', $type->name) as $group) {
                $fields = Helper::fields('post', $group->get_field_slugs(), false);

                $options = [];
                foreach (array_column($fields, 'slug', 'name') as $name => $slug) {
                    $options[] = ['value' => "field:wpcf-{$slug}", 'text' => $name];
                }

                $groups[] = [
                    'label' => $group->get_display_name(),
                    'options' => $options,
                ];
            }

            $config->update("customizer.sources.{$type->name}OrderOptions", function (
                $options
            ) use ($groups) {
                return array_merge((array) $options, $groups);
            });
        }
    }

    public static function configRelationshipFields($source, $name, array $relationships)
    {
        $type = Str::camelCase([$name, 'Toolset'], true);

        // add field on type
        $source->objectType(Str::camelCase($name, true), [
            'fields' => [
                'toolset' => [
                    'type' => $type,
                    'metadata' => [
                        'label' => trans('Fields'),
                    ],
                    'extensions' => [
                        'call' => Type\FieldsType::class . '::toolset',
                    ],
                ],
            ],
        ]);

        $source->objectType(
            $type,
            Type\RelationshipFieldsType::config($source, $name, $relationships)
        );
    }
}
