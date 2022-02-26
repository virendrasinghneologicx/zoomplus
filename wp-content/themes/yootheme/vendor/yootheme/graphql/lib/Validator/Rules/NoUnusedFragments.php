<?php
namespace YOOtheme\GraphQL\Validator\Rules;

use YOOtheme\GraphQL\Error\Error;
use YOOtheme\GraphQL\Language\AST\FragmentDefinitionNode;
use YOOtheme\GraphQL\Language\AST\NodeKind;
use YOOtheme\GraphQL\Language\Visitor;
use YOOtheme\GraphQL\Validator\ValidationContext;

class NoUnusedFragments extends AbstractValidationRule
{
    static function unusedFragMessage($fragName)
    {
        return "Fragment \"$fragName\" is never used.";
    }

    public $operationDefs;

    public $fragmentDefs;

    public function getVisitor(ValidationContext $context)
    {
        $this->operationDefs = [];
        $this->fragmentDefs = [];

        return [
            NodeKind::OPERATION_DEFINITION => function($node) {
                $this->operationDefs[] = $node;
                return Visitor::skipNode();
            },
            NodeKind::FRAGMENT_DEFINITION => function(FragmentDefinitionNode $def) {
                $this->fragmentDefs[] = $def;
                return Visitor::skipNode();
            },
            NodeKind::DOCUMENT => [
                'leave' => function() use ($context) {
                    $fragmentNameUsed = [];

                    foreach ($this->operationDefs as $operation) {
                        foreach ($context->getRecursivelyReferencedFragments($operation) as $fragment) {
                            $fragmentNameUsed[$fragment->name->value] = true;
                        }
                    }

                    foreach ($this->fragmentDefs as $fragmentDef) {
                        $fragName = $fragmentDef->name->value;
                        if (empty($fragmentNameUsed[$fragName])) {
                            $context->reportError(new Error(
                                self::unusedFragMessage($fragName),
                                [ $fragmentDef ]
                            ));
                        }
                    }
                }
            ]
        ];
    }
}
