<?php
namespace YOOtheme\GraphQL\Validator\Rules;

use YOOtheme\GraphQL\Error\Error;
use YOOtheme\GraphQL\Language\AST\FragmentSpreadNode;
use YOOtheme\GraphQL\Language\AST\NodeKind;
use YOOtheme\GraphQL\Validator\ValidationContext;

class KnownFragmentNames extends AbstractValidationRule
{
    static function unknownFragmentMessage($fragName)
    {
        return "Unknown fragment \"$fragName\".";
    }

    public function getVisitor(ValidationContext $context)
    {
        return [
            NodeKind::FRAGMENT_SPREAD => function(FragmentSpreadNode $node) use ($context) {
                $fragmentName = $node->name->value;
                $fragment = $context->getFragment($fragmentName);
                if (!$fragment) {
                    $context->reportError(new Error(
                        self::unknownFragmentMessage($fragmentName),
                        [$node->name]
                    ));
                }
            }
        ];
    }
}
