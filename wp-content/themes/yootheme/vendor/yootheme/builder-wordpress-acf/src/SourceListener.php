<?php

namespace YOOtheme\Builder\Wordpress\Acf;

use YOOtheme\Builder\Wordpress\Source\Helper as SourceHelper;
use YOOtheme\Config;
use YOOtheme\Str;
use function YOOtheme\trans;

class SourceListener
{
    public static function initSource($source)
    {
        $ignore = ['clone', 'flexible_content'];

        $source->objectType('LinkField', Type\LinkFieldType::config());
        $source->objectType('ValueField', Type\ValueFieldType::config());
        $source->objectType('ChoiceField', Type\ChoiceFieldType::config());
        $source->objectType('ChoiceFieldString', Type\ChoiceFieldStringType::config());
        $source->objectType('GoogleMapsField', Type\GoogleMapsFieldType::config());

        if ($fields = AcfHelper::fields('user', '', $ignore)) {
            static::configFields($source, 'User', $fields);
        }

        foreach (SourceHelper::getPostTypes() as $type) {
            if ($fields = AcfHelper::fields('post', $type->name, $ignore)) {
                static::configFields($source, $type->name, $fields);
            }
        }

        foreach (SourceHelper::getTaxonomies() as $taxonomy) {
            if ($fields = AcfHelper::fields('term', $taxonomy->name, $ignore)) {
                static::configFields($source, $taxonomy->name, $fields);
            }
        }
    }

    protected static function configFields($source, $name, array $fields)
    {
        $type = Str::camelCase([$name, 'Fields'], true);

        // add field on type
        $source->objectType(Str::camelCase($name, true), [
            'fields' => [
                'field' => [
                    'type' => $type,
                    'metadata' => [
                        'label' => trans('Fields'),
                    ],
                    'extensions' => [
                        'call' => Type\FieldsType::class . '::field',
                    ],
                ],
            ],
        ]);

        // configure field type
        $source->objectType($type, Type\FieldsType::config($source, $fields));
    }

    public static function initCustomizer(Config $config)
    {
        foreach (SourceHelper::getPostTypes() as $type) {
            $groups = [];

            foreach (AcfHelper::groups('post', $type->name) as $group) {
                $options = [];
                foreach (array_column(acf_get_fields($group), 'name', 'label') as $label => $name) {
                    $options[] = ['value' => "field:{$name}", 'text' => $label];
                }

                $groups[] = [
                    'label' => $group['title'],
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
}
