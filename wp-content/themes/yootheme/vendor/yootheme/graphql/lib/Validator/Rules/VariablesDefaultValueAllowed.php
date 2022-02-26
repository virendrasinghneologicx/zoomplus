<?php
namespace YOOtheme\GraphQL\Validator\Rules;

use YOOtheme\GraphQL\Error\Error;
use YOOtheme\GraphQL\Language\AST\FragmentDefinitionNode;
use YOOtheme\GraphQL\Language\AST\NodeKind;
use YOOtheme\GraphQL\Language\AST\SelectionSetNode;
use YOOtheme\GraphQL\Language\AST\VariableDefinitionNode;
use YOOtheme\GraphQL\Language\Visitor;
use YOOtheme\GraphQL\Type\Definition\NonNull;
use YOOtheme\GraphQL\Validator\ValidationContext;

/**
 * Variable's default value is allowed
 *
 * A GraphQL document is only valid if all variable default values are allowed
 * due to a variable not being required.
 */
class VariablesDefaultValueAllowed extends AbstractValidationRule
{
    static function defaultForRequiredVarMessage($varName, $type, $guessType)
    {
        return (
            "Variable \"\${$varName}\" of type \"{$type}\" is required and " .
            'will not use the default value. ' .
            "Perhaps you meant to use type \"{$guessType}\"."
        );
    }

    public function getVisitor(ValidationContext $context)
    {
        return [
            NodeKind::VARIABLE_DEFINITION => function(VariableDefinitionNode $node) use ($context) {
                $name = $node->variable->name->value;
                $defaultValue = $node->defaultValue;
                $type = $context->getInputType();
                if ($type instanceof NonNull && $defaultValue) {
                    $context->reportError(
                      new Error(
                          self::defaultForRequiredVarMessage(
                              $name,
                              $type,
                              $type->getWrappedType()
                          ),
                          [$defaultValue]
                      )
                    );
                }

                return Visitor::skipNode();
            },
            NodeKind::SELECTION_SET => function(SelectionSetNode $node) {
                return Visitor::skipNode();
            },
            NodeKind::FRAGMENT_DEFINITION => function(FragmentDefinitionNode $node) {
                return Visitor::skipNode();
            },
        ];
    }
}
