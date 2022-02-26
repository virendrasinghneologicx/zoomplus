<?php
namespace YOOtheme\GraphQL\Validator\Rules;

use YOOtheme\GraphQL\Error\Error;
use YOOtheme\GraphQL\Language\AST\FieldNode;
use YOOtheme\GraphQL\Language\AST\NodeKind;
use YOOtheme\GraphQL\Type\Definition\Type;
use YOOtheme\GraphQL\Validator\ValidationContext;

class ScalarLeafs extends AbstractValidationRule
{
    static function noSubselectionAllowedMessage($field, $type)
    {
        return "Field \"$field\" of type \"$type\" must not have a sub selection.";
    }

    static function requiredSubselectionMessage($field, $type)
    {
        return "Field \"$field\" of type \"$type\" must have a sub selection.";
    }

    public function getVisitor(ValidationContext $context)
    {
        return [
            NodeKind::FIELD => function(FieldNode $node) use ($context) {
                $type = $context->getType();
                if ($type) {
                    if (Type::isLeafType(Type::getNamedType($type))) {
                        if ($node->selectionSet) {
                            $context->reportError(new Error(
                                self::noSubselectionAllowedMessage($node->name->value, $type),
                                [$node->selectionSet]
                            ));
                        }
                    } else if (!$node->selectionSet) {
                        $context->reportError(new Error(
                            self::requiredSubselectionMessage($node->name->value, $type),
                            [$node]
                        ));
                    }
                }
            }
        ];
    }
}
