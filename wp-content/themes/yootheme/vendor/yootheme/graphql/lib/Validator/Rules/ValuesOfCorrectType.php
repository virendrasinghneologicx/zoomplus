<?php
namespace YOOtheme\GraphQL\Validator\Rules;

use YOOtheme\GraphQL\Error\Error;
use YOOtheme\GraphQL\Language\AST\BooleanValueNode;
use YOOtheme\GraphQL\Language\AST\EnumValueNode;
use YOOtheme\GraphQL\Language\AST\FloatValueNode;
use YOOtheme\GraphQL\Language\AST\IntValueNode;
use YOOtheme\GraphQL\Language\AST\ListValueNode;
use YOOtheme\GraphQL\Language\AST\NodeKind;
use YOOtheme\GraphQL\Language\AST\NullValueNode;
use YOOtheme\GraphQL\Language\AST\ObjectFieldNode;
use YOOtheme\GraphQL\Language\AST\ObjectValueNode;
use YOOtheme\GraphQL\Language\AST\StringValueNode;
use YOOtheme\GraphQL\Language\AST\ValueNode;
use YOOtheme\GraphQL\Language\Printer;
use YOOtheme\GraphQL\Language\Visitor;
use YOOtheme\GraphQL\Type\Definition\EnumType;
use YOOtheme\GraphQL\Type\Definition\EnumValueDefinition;
use YOOtheme\GraphQL\Type\Definition\InputObjectType;
use YOOtheme\GraphQL\Type\Definition\ListOfType;
use YOOtheme\GraphQL\Type\Definition\NonNull;
use YOOtheme\GraphQL\Type\Definition\ScalarType;
use YOOtheme\GraphQL\Type\Definition\Type;
use YOOtheme\GraphQL\Utils\Utils;
use YOOtheme\GraphQL\Validator\ValidationContext;

/**
 * Value literals of correct type
 *
 * A GraphQL document is only valid if all value literals are of the type
 * expected at their position.
 */
class ValuesOfCorrectType extends AbstractValidationRule
{
    static function badValueMessage($typeName, $valueName, $message = null)
    {
        return "Expected type {$typeName}, found {$valueName}"  .
            ($message ? "; ${message}" : '.');
    }

    static function requiredFieldMessage($typeName, $fieldName, $fieldTypeName)
    {
        return "Field {$typeName}.{$fieldName} of required type " .
            "{$fieldTypeName} was not provided.";
    }

    static function unknownFieldMessage($typeName, $fieldName, $message = null)
    {
        return (
            "Field \"{$fieldName}\" is not defined by type {$typeName}" .
            ($message ? "; {$message}" : '.')
        );
    }

    public function getVisitor(ValidationContext $context)
    {
        return [
            NodeKind::NULL => function(NullValueNode $node) use ($context) {
                $type = $context->getInputType();
                if ($type instanceof NonNull) {
                    $context->reportError(
                      new Error(
                          self::badValueMessage((string) $type, Printer::doPrint($node)),
                          $node
                      )
                    );
                }
            },
            NodeKind::LST => function(ListValueNode $node) use ($context) {
                // Note: TypeInfo will traverse into a list's item type, so look to the
                // parent input type to check if it is a list.
                $type = Type::getNullableType($context->getParentInputType());
                if (!$type instanceof ListOfType) {
                    $this->isValidScalar($context, $node);
                    return Visitor::skipNode();
                }
            },
            NodeKind::OBJECT => function(ObjectValueNode $node) use ($context) {
                // Note: TypeInfo will traverse into a list's item type, so look to the
                // parent input type to check if it is a list.
                $type = Type::getNamedType($context->getInputType());
                if (!$type instanceof InputObjectType) {
                    $this->isValidScalar($context, $node);
                    return Visitor::skipNode();
                }
                // Ensure every required field exists.
                $inputFields = $type->getFields();
                $nodeFields = iterator_to_array($node->fields);
                $fieldNodeMap = array_combine(
                    array_map(function ($field) { return $field->name->value; }, $nodeFields),
                    array_values($nodeFields)
                );
                foreach ($inputFields as $fieldName => $fieldDef) {
                    $fieldType = $fieldDef->getType();
                    if (!isset($fieldNodeMap[$fieldName]) && $fieldType instanceof NonNull) {
                        $context->reportError(
                            new Error(
                                self::requiredFieldMessage($type->name, $fieldName, (string) $fieldType),
                                $node
                            )
                        );
                    }
                }
            },
            NodeKind::OBJECT_FIELD => function(ObjectFieldNode $node) use ($context) {
                $parentType = Type::getNamedType($context->getParentInputType());
                $fieldType = $context->getInputType();
                if (!$fieldType && $parentType instanceof InputObjectType) {
                    $suggestions = Utils::suggestionList(
                        $node->name->value,
                        array_keys($parentType->getFields())
                    );
                    $didYouMean = $suggestions
                        ? "Did you mean " . Utils::orList($suggestions) . "?"
                        : null;

                    $context->reportError(
                        new Error(
                            self::unknownFieldMessage($parentType->name, $node->name->value, $didYouMean),
                            $node
                        )
                    );
                }
            },
            NodeKind::ENUM => function(EnumValueNode $node) use ($context) {
                $type = Type::getNamedType($context->getInputType());
                if (!$type instanceof EnumType) {
                    $this->isValidScalar($context, $node);
                } else if (!$type->getValue($node->value)) {
                    $context->reportError(
                        new Error(
                            self::badValueMessage(
                                $type->name,
                                Printer::doPrint($node),
                                $this->enumTypeSuggestion($type, $node)
                            ),
                            $node
                        )
                    );
                }
            },
            NodeKind::INT => function (IntValueNode $node) use ($context) { $this->isValidScalar($context, $node); },
            NodeKind::FLOAT => function (FloatValueNode $node) use ($context) { $this->isValidScalar($context, $node); },
            NodeKind::STRING => function (StringValueNode $node) use ($context) { $this->isValidScalar($context, $node); },
            NodeKind::BOOLEAN => function (BooleanValueNode $node) use ($context) { $this->isValidScalar($context, $node); },
        ];
    }

    private function isValidScalar(ValidationContext $context, ValueNode $node)
    {
        // Report any error at the full type expected by the location.
        $locationType = $context->getInputType();

        if (!$locationType) {
            return;
        }

        $type = Type::getNamedType($locationType);

        if (!$type instanceof ScalarType) {
            $context->reportError(
                new Error(
                    self::badValueMessage(
                        (string) $locationType,
                        Printer::doPrint($node),
                        $this->enumTypeSuggestion($type, $node)
                    ),
                    $node
                )
            );
            return;
        }

        // Scalars determine if a literal value is valid via parseLiteral() which
        // may throw to indicate failure.
        try {
            $type->parseLiteral($node);
        } catch (\Exception $error) {
            // Ensure a reference to the original error is maintained.
            $context->reportError(
                new Error(
                    self::badValueMessage(
                        (string) $locationType,
                        Printer::doPrint($node),
                        $error->getMessage()
                    ),
                    $node,
                    null,
                    null,
                    null,
                    $error
                )
            );
        } catch (\Throwable $error) {
            // Ensure a reference to the original error is maintained.
            $context->reportError(
                new Error(
                    self::badValueMessage(
                        (string) $locationType,
                        Printer::doPrint($node),
                        $error->getMessage()
                    ),
                    $node,
                    null,
                    null,
                    null,
                    $error
                )
            );
        }
    }

    private function enumTypeSuggestion($type, ValueNode $node)
    {
        if ($type instanceof EnumType) {
            $suggestions = Utils::suggestionList(
                Printer::doPrint($node),
                array_map(function (EnumValueDefinition $value) {
                    return $value->name;
                }, $type->getValues())
            );

            return $suggestions ? 'Did you mean the enum value ' . Utils::orList($suggestions) . '?' : null;
        }
    }
}
