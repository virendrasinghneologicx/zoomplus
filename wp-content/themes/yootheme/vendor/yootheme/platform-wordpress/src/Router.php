<?php

namespace YOOtheme\Wordpress;

use YOOtheme\Url;

class Router
{
    public static function generate($pattern = '', array $parameters = [], $secure = null)
    {
        if ($pattern) {
            $parameters = ['p' => $pattern] + $parameters;
        }

        return Url::to(admin_url('admin-ajax.php'), ['action' => 'kernel'] + $parameters, $secure);
    }
}
