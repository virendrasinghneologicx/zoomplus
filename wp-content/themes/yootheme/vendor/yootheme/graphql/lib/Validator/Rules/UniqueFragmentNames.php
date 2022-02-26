<?php
namespace YOOtheme\GraphQL\Validator\Rules;

use YOOtheme\GraphQL\Error\Error;
use YOOtheme\GraphQL\Language\AST\FragmentDefinitionNode;
use YOOtheme\GraphQL\Language\AST\NodeKind;
use YOOtheme\GraphQL\Language\Visitor;
use YOOtheme\GraphQL\Validator\ValidationContext;

class UniqueFragmentNames extends AbstractValidationRule
{
    static function duplicateFragmentNameMessage($fragName)
    {
        return "There can be only one fragment named \"$fragName\".";
    }

    public $knownFragmentNames;

    public function getVisitor(ValidationContext $context)
    {
        $this->knownFragmentNames = [];

        return [
            NodeKind::OPERATION_DEFINITION => function () {
                return Visitor::skipNode();
            },
            NodeKind::FRAGMENT_DEFINITION => function (FragmentDefinitionNode $node) use ($context) {
                $fragmentName = $node->name->value;
                if (!empty($this->knownFragmentNames[$fragmentName])) {
                    $context->reportError(new Error(
                        self::duplicateFragmentNameMessage($fragmentName),
                        [ $this->knownFragmentNames[$fragmentName], $node->name ]
                    ));
                } else {
                    $this->knownFragmentNames[$fragmentName] = $node->name;
                }
                return Visitor::skipNode();
            }
        ];
    }
}
