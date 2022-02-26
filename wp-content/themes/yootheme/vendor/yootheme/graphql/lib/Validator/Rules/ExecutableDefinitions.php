<?php
namespace YOOtheme\GraphQL\Validator\Rules;

use YOOtheme\GraphQL\Error\Error;
use YOOtheme\GraphQL\Language\AST\DocumentNode;
use YOOtheme\GraphQL\Language\AST\FragmentDefinitionNode;
use YOOtheme\GraphQL\Language\AST\Node;
use YOOtheme\GraphQL\Language\AST\NodeKind;
use YOOtheme\GraphQL\Language\AST\OperationDefinitionNode;
use YOOtheme\GraphQL\Language\Visitor;
use YOOtheme\GraphQL\Validator\ValidationContext;

/**
 * Executable definitions
 *
 * A GraphQL document is only valid for execution if all definitions are either
 * operation or fragment definitions.
 */
class ExecutableDefinitions extends AbstractValidationRule
{
    static function nonExecutableDefinitionMessage($defName)
    {
        return "The \"$defName\" definition is not executable.";
    }

    public function getVisitor(ValidationContext $context)
    {
        return [
            NodeKind::DOCUMENT => function (DocumentNode $node) use ($context) {
                /** @var Node $definition */
                foreach ($node->definitions as $definition) {
                    if (
                        !$definition instanceof OperationDefinitionNode &&
                        !$definition instanceof FragmentDefinitionNode
                    ) {
                        $context->reportError(new Error(
                            self::nonExecutableDefinitionMessage($definition->name->value),
                            [$definition->name]
                        ));
                    }
                }

                return Visitor::skipNode();
            }
        ];
    }
}
