<?php

namespace YOOtheme\Builder\Wordpress\Source;

use YOOtheme\Event;

class Helper
{
    protected static $arguments = [
        'public' => true,
        'show_ui' => true,
        'show_in_nav_menus' => true,
    ];

    public static function getBase($type)
    {
        if (!$type->rest_base || $type->rest_base === $type->name) {
            return strtr($type->name . 's', '-', '_');
        }

        return strtr($type->rest_base, '-', '_');
    }

    public static function getPostTypes(array $arguments = [])
    {
        return get_post_types($arguments + static::$arguments, 'objects');
    }

    public static function getTaxonomies(array $arguments = [])
    {
        return get_taxonomies($arguments + static::$arguments, 'objects');
    }

    public static function getObjectTaxonomies($object, array $arguments = [])
    {
        $taxonomies = get_object_taxonomies($object, 'objects');
        $taxonomies = wp_filter_object_list($taxonomies, $arguments + static::$arguments);

        return Event::emit('source.object.taxonomies|filter', $taxonomies, $object);
    }

    public static function orderAlphanum(array $query)
    {
        return function ($orderby) use ($query) {
            if (!str_contains((string) $orderby, ',')) {
                $replace = str_replace(
                    ':ORDER',
                    $query['order'],
                    "(SUBSTR($1, 1, 1) > '9') :ORDER, $1+0 :ORDER, $1 :ORDER"
                );
                $orderby = preg_replace('/([^\s]+).*/', $replace, $orderby, 1);
            }

            return $orderby;
        };
    }

    public static function filterOnce($tag, $callback)
    {
        add_filter(
            $tag,
            $filter = function (...$args) use ($tag, $callback, &$filter) {
                remove_filter($tag, $filter);
                return $callback(...$args);
            }
        );
    }

    public static function isPageSource($post)
    {
        return get_the_ID() === $post->ID;
    }
}
