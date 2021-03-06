<?php
namespace YOOtheme\GraphQL\Utils;

use YOOtheme\GraphQL\Error\InvariantViolation;
use YOOtheme\GraphQL\Error\Warning;
use YOOtheme\GraphQL\Language\AST\FieldNode;
use YOOtheme\GraphQL\Language\AST\ListTypeNode;
use YOOtheme\GraphQL\Language\AST\NamedTypeNode;
use YOOtheme\GraphQL\Language\AST\Node;
use YOOtheme\GraphQL\Language\AST\NodeKind;
use YOOtheme\GraphQL\Language\AST\NonNullTypeNode;
use YOOtheme\GraphQL\Type\Schema;
use YOOtheme\GraphQL\Type\Definition\CompositeType;
use YOOtheme\GraphQL\Type\Definition\Directive;
use YOOtheme\GraphQL\Type\Definition\EnumType;
use YOOtheme\GraphQL\Type\Definition\FieldArgument;
use YOOtheme\GraphQL\Type\Definition\FieldDefinition;
use YOOtheme\GraphQL\Type\Definition\InputObjectType;
use YOOtheme\GraphQL\Type\Definition\InputType;
use YOOtheme\GraphQL\Type\Definition\InterfaceType;
use YOOtheme\GraphQL\Type\Definition\ListOfType;
use YOOtheme\GraphQL\Type\Definition\ObjectType;
use YOOtheme\GraphQL\Type\Definition\Type;
use YOOtheme\GraphQL\Type\Definition\UnionType;
use YOOtheme\GraphQL\Type\Definition\WrappingType;
use YOOtheme\GraphQL\Type\Introspection;

/**
 * Class TypeInfo
 * @package GraphQL\Utils
 */
class TypeInfo
{
    /**
     * @deprecated moved to GraphQL\Utils\TypeComparators
     */
    public static function isEqualType(Type $typeA, Type $typeB)
    {
        return TypeComparators::isEqualType($typeA, $typeB);
    }

    /**
     * @deprecated moved to GraphQL\Utils\TypeComparators
     */
    static function isTypeSubTypeOf(Schema $schema, Type $maybeSubType, Type $superType)
    {
        return TypeComparators::isTypeSubTypeOf($schema, $maybeSubType, $superType);
    }

    /**
     * @deprecated moved to GraphQL\Utils\TypeComparators
     */
    static function doTypesOverlap(Schema $schema, CompositeType $typeA, CompositeType $typeB)
    {
        return TypeComparators::doTypesOverlap($schema, $typeA, $typeB);
    }

    /**
     * @param Schema $schema
     * @param NamedTypeNode|ListTypeNode|NonNullTypeNode $inputTypeNode
     * @return Type
     * @throws InvariantViolation
     */
    public static function typeFromAST(Schema $schema, $inputTypeNode)
    {
        return AST::typeFromAST($schema, $inputTypeNode);
    }

    /**
     * Given root type scans through all fields to find nested types. Returns array where keys are for type name
     * and value contains corresponding type instance.
     *
     * Example output:
     * [
     *     'String' => $instanceOfStringType,
     *     'MyType' => $instanceOfMyType,
     *     ...
     * ]
     *
     * @param Type $type
     * @param array|null $typeMap
     * @return array
     */
    public static function extractTypes($type, array $typeMap = null)
    {
        if (!$typeMap) {
            $typeMap = [];
        }
        if (!$type) {
            return $typeMap;
        }

        if ($type instanceof WrappingType) {
            return self::extractTypes($type->getWrappedType(true), $typeMap);
        }
        if (!$type instanceof Type) {
            Warning::warnOnce(
                'One of the schema types is not a valid type definition instance. '.
                'Try running $schema->assertValid() to find out the cause of this warning.',
                Warning::WARNING_NOT_A_TYPE
            );
            return $typeMap;
        }

        if (!empty($typeMap[$type->name])) {
            Utils::invariant(
                $typeMap[$type->name] === $type,
                "Schema must contain unique named types but contains multiple types named \"$type\" ".
                "(see http://webonyx.github.io/graphql-php/type-system/#type-registry)."
            );
            return $typeMap;
        }
        $typeMap[$type->name] = $type;

        $nestedTypes = [];

        if ($type instanceof UnionType) {
            $nestedTypes = $type->getTypes();
        }
        if ($type instanceof ObjectType) {
            $nestedTypes = array_merge($nestedTypes, $type->getInterfaces());
        }
        if ($type instanceof ObjectType || $type instanceof InterfaceType) {
            foreach ((array) $type->getFields() as $fieldName => $field) {
                if (!empty($field->args)) {
                    $fieldArgTypes = array_map(function(FieldArgument $arg) { return $arg->getType(); }, $field->args);
                    $nestedTypes = array_merge($nestedTypes, $fieldArgTypes);
                }
                $nestedTypes[] = $field->getType();
            }
        }
        if ($type instanceof InputObjectType) {
            foreach ((array) $type->getFields() as $fieldName => $field) {
                $nestedTypes[] = $field->getType();
            }
        }
        foreach ($nestedTypes as $type) {
            $typeMap = self::extractTypes($type, $typeMap);
        }
        return $typeMap;
    }

    /**
     * Not exactly the same as the executor's definition of getFieldDef, in this
     * statically evaluated environment we do not always have an Object type,
     * and need to handle Interface and Union types.
     *
     * @return FieldDefinition
     */
    static private function getFieldDefinition(Schema $schema, Type $parentType, FieldNode $fieldNode)
    {
        $name = $fieldNode->name->value;
        $schemaMeta = Introspection::schemaMetaFieldDef();
        if ($name === $schemaMeta->name && $schema->getQueryType() === $parentType) {
            return $schemaMeta;
        }

        $typeMeta = Introspection::typeMetaFieldDef();
        if ($name === $typeMeta->name && $schema->getQueryType() === $parentType) {
            return $typeMeta;
        }
        $typeNameMeta = Introspection::typeNameMetaFieldDef();
        if ($name === $typeNameMeta->name && $parentType instanceof CompositeType) {
            return $typeNameMeta;
        }
        if ($parentType instanceof ObjectType ||
            $parentType instanceof InterfaceType) {
            $fields = $parentType->getFields();
            return isset($fields[$name]) ? $fields[$name] : null;
        }
        return null;
    }


    /**
     * @var Schema
     */
    private $schema;

    /**
     * @var \SplStack<OutputType>
     */
    private $typeStack;

    /**
     * @var \SplStack<CompositeType>
     */
    private $parentTypeStack;

    /**
     * @var \SplStack<InputType>
     */
    private $inputTypeStack;

    /**
     * @var \SplStack<FieldDefinition>
     */
    private $fieldDefStack;

    /**
     * @var Directive
     */
    private $directive;

    /**
     * @var FieldArgument
     */
    private $argument;

    /**
     * @var mixed
     */
    private $enumValue;

    /**
     * TypeInfo constructor.
     * @param Schema $schema
     * @param Type|null $initialType
     */
    public function __construct(Schema $schema, $initialType = null)
    {
        $this->schema = $schema;
        $this->typeStack = [];
        $this->parentTypeStack = [];
        $this->inputTypeStack = [];
        $this->fieldDefStack = [];
        if ($initialType) {
            if (Type::isInputType($initialType)) {
                $this->inputTypeStack[] = $initialType;
            }
            if (Type::isCompositeType($initialType)) {
                $this->parentTypeStack[] = $initialType;
            }
            if (Type::isOutputType($initialType)) {
                $this->typeStack[] = $initialType;
            }
        }
    }

    /**
     * @return Type
     */
    function getType()
    {
        if (!empty($this->typeStack)) {
            return $this->typeStack[count($this->typeStack) - 1];
        }
        return null;
    }

    /**
     * @return CompositeType
     */
    function getParentType()
    {
        if (!empty($this->parentTypeStack)) {
            return $this->parentTypeStack[count($this->parentTypeStack) - 1];
        }
        return null;
    }

    /**
     * @return InputType
     */
    function getInputType()
    {
        if (!empty($this->inputTypeStack)) {
            return $this->inputTypeStack[count($this->inputTypeStack) - 1];
        }
        return null;
    }

    /**
     * @return InputType|null
     */
    public function getParentInputType()
    {
        $inputTypeStackLength = count($this->inputTypeStack);
        if ($inputTypeStackLength > 1) {
            return $this->inputTypeStack[$inputTypeStackLength - 2];
        }
    }

    /**
     * @return FieldDefinition
     */
    function getFieldDef()
    {
        if (!empty($this->fieldDefStack)) {
            return $this->fieldDefStack[count($this->fieldDefStack) - 1];
        }
        return null;
    }

    /**
     * @return Directive|null
     */
    function getDirective()
    {
        return $this->directive;
    }

    /**
     * @return FieldArgument|null
     */
    function getArgument()
    {
        return $this->argument;
    }

    /**
     * @return mixed
     */
    function getEnumValue()
    {
        return $this->enumValue;
    }

    /**
     * @param Node $node
     */
    function enter(Node $node)
    {
        $schema = $this->schema;

        // Note: many of the types below are explicitly typed as "mixed" to drop
        // any assumptions of a valid schema to ensure runtime types are properly
        // checked before continuing since TypeInfo is used as part of validation
        // which occurs before guarantees of schema and document validity.
        switch ($node->kind) {
            case NodeKind::SELECTION_SET:
                $namedType = Type::getNamedType($this->getType());
                $this->parentTypeStack[] = Type::isCompositeType($namedType) ? $namedType : null;
                break;

            case NodeKind::FIELD:
                $parentType = $this->getParentType();
                $fieldDef = null;
                if ($parentType) {
                    $fieldDef = self::getFieldDefinition($schema, $parentType, $node);
                }
                $fieldType = null;
                if ($fieldDef) {
                    $fieldType = $fieldDef->getType();
                }
                $this->fieldDefStack[] = $fieldDef;
                $this->typeStack[] = Type::isOutputType($fieldType) ? $fieldType : null;
                break;

            case NodeKind::DIRECTIVE:
                $this->directive = $schema->getDirective($node->name->value);
                break;

            case NodeKind::OPERATION_DEFINITION:
                $type = null;
                if ($node->operation === 'query') {
                    $type = $schema->getQueryType();
                } else if ($node->operation === 'mutation') {
                    $type = $schema->getMutationType();
                } else if ($node->operation === 'subscription') {
                    $type = $schema->getSubscriptionType();
                }
                $this->typeStack[] = Type::isOutputType($type) ? $type : null;
                break;

            case NodeKind::INLINE_FRAGMENT:
            case NodeKind::FRAGMENT_DEFINITION:
                $typeConditionNode = $node->typeCondition;
                $outputType = $typeConditionNode ? self::typeFromAST($schema, $typeConditionNode) : Type::getNamedType($this->getType());
                $this->typeStack[] = Type::isOutputType($outputType) ? $outputType : null;
                break;

            case NodeKind::VARIABLE_DEFINITION:
                $inputType = self::typeFromAST($schema, $node->type);
                $this->inputTypeStack[] = Type::isInputType($inputType) ? $inputType : null; // push
                break;

            case NodeKind::ARGUMENT:
                $fieldOrDirective = $this->getDirective() ?: $this->getFieldDef();
                $argDef = $argType = null;
                if ($fieldOrDirective) {
                    $argDef = Utils::find($fieldOrDirective->args, function($arg) use ($node) {return $arg->name === $node->name->value;});
                    if ($argDef) {
                        $argType = $argDef->getType();
                    }
                }
                $this->argument = $argDef;
                $this->inputTypeStack[] = Type::isInputType($argType) ? $argType : null;
                break;

            case NodeKind::LST:
                $listType = Type::getNullableType($this->getInputType());
                $itemType = $listType instanceof ListOfType
                    ? $listType->getWrappedType()
                    : $listType;
                $this->inputTypeStack[] = Type::isInputType($itemType) ? $itemType : null;
                break;

            case NodeKind::OBJECT_FIELD:
                $objectType = Type::getNamedType($this->getInputType());
                $fieldType = null;
                $inputFieldType = null;
                if ($objectType instanceof InputObjectType) {
                    $tmp = $objectType->getFields();
                    $inputField = isset($tmp[$node->name->value]) ? $tmp[$node->name->value] : null;
                    $inputFieldType = $inputField ? $inputField->getType() : null;
                }
                $this->inputTypeStack[] = Type::isInputType($inputFieldType) ? $inputFieldType : null;
                break;

            case NodeKind::ENUM:
                $enumType = Type::getNamedType($this->getInputType());
                $enumValue = null;
                if ($enumType instanceof EnumType) {
                    $enumValue = $enumType->getValue($node->value);
                }
                $this->enumValue = $enumValue;
                break;
        }
    }

    /**
     * @param Node $node
     */
    function leave(Node $node)
    {
        switch ($node->kind) {
            case NodeKind::SELECTION_SET:
                array_pop($this->parentTypeStack);
                break;

            case NodeKind::FIELD:
                array_pop($this->fieldDefStack);
                array_pop($this->typeStack);
                break;

            case NodeKind::DIRECTIVE:
                $this->directive = null;
                break;

            case NodeKind::OPERATION_DEFINITION:
            case NodeKind::INLINE_FRAGMENT:
            case NodeKind::FRAGMENT_DEFINITION:
                array_pop($this->typeStack);
                break;
            case NodeKind::VARIABLE_DEFINITION:
                array_pop($this->inputTypeStack);
                break;
            case NodeKind::ARGUMENT:
                $this->argument = null;
                array_pop($this->inputTypeStack);
                break;
            case NodeKind::LST:
            case NodeKind::OBJECT_FIELD:
                array_pop($this->inputTypeStack);
                break;
            case NodeKind::ENUM:
                $this->enumValue = null;
                break;
        }
    }
}
