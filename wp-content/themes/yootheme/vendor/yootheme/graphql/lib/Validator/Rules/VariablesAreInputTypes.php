<?php
namespace YOOtheme\GraphQL\Validator\Rules;


use YOOtheme\GraphQL\Error\Error;
use YOOtheme\GraphQL\Language\AST\Node;
use YOOtheme\GraphQL\Language\AST\NodeKind;
use YOOtheme\GraphQL\Language\AST\VariableDefinitionNode;
use YOOtheme\GraphQL\Language\Printer;
use YOOtheme\GraphQL\Type\Definition\InputType;
use YOOtheme\GraphQL\Type\Definition\Type;
use YOOtheme\GraphQL\Utils\TypeInfo;
use YOOtheme\GraphQL\Validator\ValidationContext;

class VariablesAreInputTypes extends AbstractValidationRule
{
    static function nonInputTypeOnVarMessage($variableName, $typeName)
    {
        return "Variable \"\$$variableName\" cannot be non-input type \"$typeName\".";
    }

    public function getVisitor(ValidationContext $context)
    {
        return [
            NodeKind::VARIABLE_DEFINITION => function(VariableDefinitionNode $node) use ($context) {
                $type = TypeInfo::typeFromAST($context->getSchema(), $node->type);

                // If the variable type is not an input type, return an error.
                if ($type && !Type::isInputType($type)) {
                    $variableName = $node->variable->name->value;
                    $context->reportError(new Error(
                        self::nonInputTypeOnVarMessage($variableName, Printer::doPrint($node->type)),
                        [ $node->type ]
                    ));
                }
            }
        ];
    }
}
