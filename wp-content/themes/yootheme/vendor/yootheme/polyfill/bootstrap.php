<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use YOOtheme\Polyfill\Php80;

if (PHP_VERSION_ID >= 80000) {
    return;
}

if (!defined('FILTER_VALIDATE_BOOL') && defined('FILTER_VALIDATE_BOOLEAN')) {
    define('FILTER_VALIDATE_BOOL', FILTER_VALIDATE_BOOLEAN);
}

if (!function_exists('fdiv')) {
    function fdiv($num1, $num2)
    {
        return Php80::fdiv($num1, $num2);
    }
}
if (!function_exists('preg_last_error_msg')) {
    function preg_last_error_msg()
    {
        return Php80::preg_last_error_msg();
    }
}
if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle)
    {
        return Php80::str_contains(
            isset($haystack) ? $haystack : '',
            isset($needle) ? $needle : ''
        );
    }
}
if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle)
    {
        return Php80::str_starts_with(
            isset($haystack) ? $haystack : '',
            isset($needle) ? $needle : ''
        );
    }
}
if (!function_exists('str_ends_with')) {
    function str_ends_with($haystack, $needle)
    {
        return Php80::str_ends_with(
            isset($haystack) ? $haystack : '',
            isset($needle) ? $needle : ''
        );
    }
}
if (!function_exists('get_debug_type')) {
    function get_debug_type($value)
    {
        return Php80::get_debug_type($value);
    }
}
if (!function_exists('get_resource_id') && PHP_VERSION_ID >= 70000) {
    function get_resource_id($resource)
    {
        return Php80::get_resource_id($resource);
    }
}
