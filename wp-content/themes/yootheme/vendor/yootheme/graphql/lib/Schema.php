<?php
namespace YOOtheme\GraphQL;

trigger_error(
    'GraphQL\Schema is moved to GraphQL\Type\Schema',
    E_USER_DEPRECATED
);

/**
 * Schema Definition
 *
 * @deprecated moved to GraphQL\Type\Schema
 */
class Schema extends \YOOtheme\GraphQL\Type\Schema
{
}
