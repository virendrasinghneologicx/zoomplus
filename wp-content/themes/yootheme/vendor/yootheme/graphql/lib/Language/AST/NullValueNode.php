<?php
namespace YOOtheme\GraphQL\Language\AST;

class NullValueNode extends Node implements ValueNode
{
    public $kind = NodeKind::NULL;
}
