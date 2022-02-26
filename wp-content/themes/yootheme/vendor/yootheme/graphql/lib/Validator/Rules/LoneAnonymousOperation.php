<?php
namespace YOOtheme\GraphQL\Validator\Rules;

use YOOtheme\GraphQL\Error\Error;
use YOOtheme\GraphQL\Language\AST\DocumentNode;
use YOOtheme\GraphQL\Language\AST\NodeKind;
use YOOtheme\GraphQL\Language\AST\OperationDefinitionNode;
use YOOtheme\GraphQL\Utils\Utils;
use YOOtheme\GraphQL\Validator\ValidationContext;

/**
 * Lone anonymous operation
 *
 * A GraphQL document is only valid if when it contains an anonymous operation
 * (the query short-hand) that it contains only that one operation definition.
 */
class LoneAnonymousOperation extends AbstractValidationRule
{
    static function anonOperationNotAloneMessage()
    {
        return 'This anonymous operation must be the only defined operation.';
    }

    public function getVisitor(ValidationContext $context)
    {
        $operationCount = 0;
        return [
            NodeKind::DOCUMENT => function(DocumentNode $node) use (&$operationCount) {
                $tmp = Utils::filter(
                    $node->definitions,
                    function ($definition) {
                        return $definition->kind === NodeKind::OPERATION_DEFINITION;
                    }
                );
                $operationCount = count($tmp);
            },
            NodeKind::OPERATION_DEFINITION => function(OperationDefinitionNode $node) use (&$operationCount, $context) {
                if (!$node->name && $operationCount > 1) {
                    $context->reportError(
                        new Error(self::anonOperationNotAloneMessage(), [$node])
                    );
                }
            }
        ];
    }
}
