<?php
namespace YOOtheme\GraphQL\Validator\Rules;

use YOOtheme\GraphQL\Error\Error;
use YOOtheme\GraphQL\Language\AST\ArgumentNode;
use YOOtheme\GraphQL\Language\AST\NodeKind;
use YOOtheme\GraphQL\Utils\Utils;
use YOOtheme\GraphQL\Validator\ValidationContext;

/**
 * Known argument names
 *
 * A GraphQL field is only valid if all supplied arguments are defined by
 * that field.
 */
class KnownArgumentNames extends AbstractValidationRule
{
    public static function unknownArgMessage($argName, $fieldName, $typeName, array $suggestedArgs)
    {
        $message = "Unknown argument \"$argName\" on field \"$fieldName\" of type \"$typeName\".";
        if ($suggestedArgs) {
            $message .= ' Did you mean ' . Utils::quotedOrList($suggestedArgs) . '?';
        }
        return $message;
    }

    public static function unknownDirectiveArgMessage($argName, $directiveName, array $suggestedArgs)
    {
        $message = "Unknown argument \"$argName\" on directive \"@$directiveName\".";
        if ($suggestedArgs) {
            $message .= ' Did you mean ' . Utils::quotedOrList($suggestedArgs) . '?';
        }
        return $message;
    }

    public function getVisitor(ValidationContext $context)
    {
        return [
            NodeKind::ARGUMENT => function(ArgumentNode $node, $key, $parent, $path, $ancestors) use ($context) {
                $argDef = $context->getArgument();
                if (!$argDef) {
                    $argumentOf = $ancestors[count($ancestors) - 1];
                    if ($argumentOf->kind === NodeKind::FIELD) {
                        $fieldDef = $context->getFieldDef();
                        $parentType = $context->getParentType();
                        if ($fieldDef && $parentType) {
                            $context->reportError(new Error(
                                self::unknownArgMessage(
                                    $node->name->value,
                                    $fieldDef->name,
                                    $parentType->name,
                                    Utils::suggestionList(
                                        $node->name->value,
                                        array_map(function ($arg) { return $arg->name; }, $fieldDef->args)
                                    )
                                ),
                                [$node]
                            ));
                        }
                    } else if ($argumentOf->kind === NodeKind::DIRECTIVE) {
                        $directive = $context->getDirective();
                        if ($directive) {
                            $context->reportError(new Error(
                                self::unknownDirectiveArgMessage(
                                    $node->name->value,
                                    $directive->name,
                                    Utils::suggestionList(
                                        $node->name->value,
                                        array_map(function ($arg) { return $arg->name; }, $directive->args)
                                    )
                                ),
                                [$node]
                            ));
                        }
                    }
                }
            }
        ];
    }
}
