<?php

namespace YOOtheme\Builder\Wordpress\PopularPosts;

use WordPressPopularPosts\Helper;
use YOOtheme\Builder\Wordpress\Source\Helper as SourceHelper;
use YOOtheme\Str;

class SourceListener
{
    public static function initSource($source)
    {
        if (!class_exists(Helper::class)) {
            return;
        }

        foreach (SourceHelper::getPostTypes() as $type) {
            $query = Str::camelCase([SourceHelper::getBase($type), 'Query'], true);

            $source->objectType($query, Type\PopularPostsQueryType::config($type));
        }
    }
}
