<?php

namespace YOOtheme\Builder\Wordpress\Woocommerce;

class Helper
{
    public static function addFilter($name, $fn, $priority = 10, $args = 1)
    {
        add_filter($name, $fn, $priority, $args);

        return function () use ($name, $fn, $priority) {
            remove_filter($name, $fn, $priority);
        };
    }

    public static function removeFilter($name, $priority = false)
    {
        global $wp_filter;

        if ($filter = isset($wp_filter[$name]) ? $wp_filter[$name] : null) {
            $clone = $wp_filter[$name] = clone $filter;
            $clone->remove_all_filters($priority);
        }

        return function () use (&$wp_filter, $name, $filter) {
            return $wp_filter[$name] = $filter;
        };
    }

    public static function renderTemplate(callable $function, array $args = [])
    {
        ob_start();

        $function(...$args);

        return ob_get_clean();
    }

    public static function isPageSource($product)
    {
        return absint(get_the_ID()) === $product->get_id();
    }
}
