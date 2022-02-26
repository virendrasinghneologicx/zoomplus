<?php
namespace YOOtheme\GraphQL\Validator\Rules;

use YOOtheme\GraphQL\Error\Error;
use YOOtheme\GraphQL\Language\AST\NodeKind;
use YOOtheme\GraphQL\Language\AST\OperationDefinitionNode;
use YOOtheme\GraphQL\Language\AST\VariableDefinitionNode;
use YOOtheme\GraphQL\Validator\ValidationContext;

/**
 * Class NoUndefinedVariables
 *
 * A GraphQL operation is only valid if all variables encountered, both directly
 * and via fragment spreads, are defined by that operation.
 *
 * @package GraphQL\Validator\Rules
 */
class NoUndefinedVariables extends AbstractValidationRule
{
    static function undefinedVarMessage($varName, $opName = null)
    {
        return $opName
            ? "Variable \"$$varName\" is not defined by operation \"$opName\"."
            : "Variable \"$$varName\" is not defined.";
    }

    public function getVisitor(ValidationContext $context)
    {
        $variableNameDefined = [];

        return [
            NodeKind::OPERATION_DEFINITION => [
                'enter' => function() use (&$variableNameDefined) {
                    $variableNameDefined = [];
                },
                'leave' => function(OperationDefinitionNode $operation) use (&$variableNameDefined, $context) {
                    $usages = $context->getRecursiveVariableUsages($operation);

                    foreach ($usages as $usage) {
                        $node = $usage['node'];
                        $varName = $node->name->value;

                        if (empty($variableNameDefined[$varName])) {
                            $context->reportError(new Error(
                                self::undefinedVarMessage(
                                    $varName,
                                    $operation->name ? $operation->name->value : null
                                ),
                                [ $node, $operation ]
                            ));
                        }
                    }
                }
            ],
            NodeKind::VARIABLE_DEFINITION => function(VariableDefinitionNode $def) use (&$variableNameDefined) {
                $variableNameDefined[$def->variable->name->value] = true;
            }
        ];
    }
}
