<?php
namespace YOOtheme\GraphQL\Language\AST;

class DocumentNode extends Node
{
    public $kind = NodeKind::DOCUMENT;

    /**
     * @var DefinitionNode[]
     */
    public $definitions;
}
