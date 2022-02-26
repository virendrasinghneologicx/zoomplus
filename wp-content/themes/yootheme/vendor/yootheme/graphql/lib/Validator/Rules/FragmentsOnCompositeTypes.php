<?php
namespace YOOtheme\GraphQL\Validator\Rules;

use YOOtheme\GraphQL\Error\Error;
use YOOtheme\GraphQL\Language\AST\FragmentDefinitionNode;
use YOOtheme\GraphQL\Language\AST\InlineFragmentNode;
use YOOtheme\GraphQL\Language\AST\NodeKind;
use YOOtheme\GraphQL\Language\Printer;
use YOOtheme\GraphQL\Type\Definition\Type;
use YOOtheme\GraphQL\Utils\TypeInfo;
use YOOtheme\GraphQL\Validator\ValidationContext;

class FragmentsOnCompositeTypes extends AbstractValidationRule
{
    static function inlineFragmentOnNonCompositeErrorMessage($type)
    {
        return "Fragment cannot condition on non composite type \"$type\".";
    }

    static function fragmentOnNonCompositeErrorMessage($fragName, $type)
    {
        return "Fragment \"$fragName\" cannot condition on non composite type \"$type\".";
    }

    public function getVisitor(ValidationContext $context)
    {
        return [
            NodeKind::INLINE_FRAGMENT => function(InlineFragmentNode $node) use ($context) {
                if ($node->typeCondition) {
                    $type = TypeInfo::typeFromAST($context->getSchema(), $node->typeCondition);
                    if ($type && !Type::isCompositeType($type)) {
                        $context->reportError(new Error(
                            static::inlineFragmentOnNonCompositeErrorMessage($type),
                            [$node->typeCondition]
                        ));
                    }
                }
            },
            NodeKind::FRAGMENT_DEFINITION => function(FragmentDefinitionNode $node) use ($context) {
                $type = TypeInfo::typeFromAST($context->getSchema(), $node->typeCondition);

                if ($type && !Type::isCompositeType($type)) {
                    $context->reportError(new Error(
                        static::fragmentOnNonCompositeErrorMessage($node->name->value, Printer::doPrint($node->typeCondition)),
                        [$node->typeCondition]
                    ));
                }
            }
        ];
    }
}
