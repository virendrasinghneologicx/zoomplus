<?php

namespace YOOtheme\GraphQL\Validator\Rules;

use YOOtheme\GraphQL\Language\AST\FieldNode;
use YOOtheme\GraphQL\Language\AST\FragmentDefinitionNode;
use YOOtheme\GraphQL\Language\AST\FragmentSpreadNode;
use YOOtheme\GraphQL\Language\AST\InlineFragmentNode;
use YOOtheme\GraphQL\Language\AST\Node;
use YOOtheme\GraphQL\Language\AST\NodeKind;
use YOOtheme\GraphQL\Language\AST\SelectionSetNode;
use YOOtheme\GraphQL\Type\Definition\Type;
use YOOtheme\GraphQL\Type\Introspection;
use YOOtheme\GraphQL\Utils\TypeInfo;
use YOOtheme\GraphQL\Validator\ValidationContext;

abstract class AbstractQuerySecurity extends AbstractValidationRule
{
    const DISABLED = 0;

    /**
     * @var FragmentDefinitionNode[]
     */
    private $fragments = [];

    /**
     * @return \GraphQL\Language\AST\FragmentDefinitionNode[]
     */
    protected function getFragments()
    {
        return $this->fragments;
    }

    /**
     * check if equal to 0 no check is done. Must be greater or equal to 0.
     *
     * @param $value
     */
    protected function checkIfGreaterOrEqualToZero($name, $value)
    {
        if ($value < 0) {
            throw new \InvalidArgumentException(sprintf('$%s argument must be greater or equal to 0.', $name));
        }
    }

    protected function gatherFragmentDefinition(ValidationContext $context)
    {
        // Gather all the fragment definition.
        // Importantly this does not include inline fragments.
        $definitions = $context->getDocument()->definitions;
        foreach ($definitions as $node) {
            if ($node instanceof FragmentDefinitionNode) {
                $this->fragments[$node->name->value] = $node;
            }
        }
    }

    protected function getFragment(FragmentSpreadNode $fragmentSpread)
    {
        $spreadName = $fragmentSpread->name->value;
        $fragments = $this->getFragments();

        return isset($fragments[$spreadName]) ? $fragments[$spreadName] : null;
    }

    protected function invokeIfNeeded(ValidationContext $context, array $validators)
    {
        // is disabled?
        if (!$this->isEnabled()) {
            return [];
        }

        $this->gatherFragmentDefinition($context);

        return $validators;
    }

    /**
     * Given a selectionSet, adds all of the fields in that selection to
     * the passed in map of fields, and returns it at the end.
     *
     * Note: This is not the same as execution's collectFields because at static
     * time we do not know what object type will be used, so we unconditionally
     * spread in all fragments.
     *
     * @see \GraphQL\Validator\Rules\OverlappingFieldsCanBeMerged
     *
     * @param ValidationContext $context
     * @param Type|null         $parentType
     * @param SelectionSetNode      $selectionSet
     * @param \ArrayObject      $visitedFragmentNames
     * @param \ArrayObject      $astAndDefs
     *
     * @return \ArrayObject
     */
    protected function collectFieldASTsAndDefs(ValidationContext $context, $parentType, SelectionSetNode $selectionSet, \ArrayObject $visitedFragmentNames = null, \ArrayObject $astAndDefs = null)
    {
        $_visitedFragmentNames = $visitedFragmentNames ?: new \ArrayObject();
        $_astAndDefs = $astAndDefs ?: new \ArrayObject();

        foreach ($selectionSet->selections as $selection) {
            switch ($selection->kind) {
                case NodeKind::FIELD:
                    /* @var FieldNode $selection */
                    $fieldName = $selection->name->value;
                    $fieldDef = null;
                    if ($parentType && method_exists($parentType, 'getFields')) {
                        $tmp = $parentType->getFields();
                        $schemaMetaFieldDef = Introspection::schemaMetaFieldDef();
                        $typeMetaFieldDef = Introspection::typeMetaFieldDef();
                        $typeNameMetaFieldDef = Introspection::typeNameMetaFieldDef();

                        if ($fieldName === $schemaMetaFieldDef->name && $context->getSchema()->getQueryType() === $parentType) {
                            $fieldDef = $schemaMetaFieldDef;
                        } elseif ($fieldName === $typeMetaFieldDef->name && $context->getSchema()->getQueryType() === $parentType) {
                            $fieldDef = $typeMetaFieldDef;
                        } elseif ($fieldName === $typeNameMetaFieldDef->name) {
                            $fieldDef = $typeNameMetaFieldDef;
                        } elseif (isset($tmp[$fieldName])) {
                            $fieldDef = $tmp[$fieldName];
                        }
                    }
                    $responseName = $this->getFieldName($selection);
                    if (!isset($_astAndDefs[$responseName])) {
                        $_astAndDefs[$responseName] = new \ArrayObject();
                    }
                    // create field context
                    $_astAndDefs[$responseName][] = [$selection, $fieldDef];
                    break;
                case NodeKind::INLINE_FRAGMENT:
                    /* @var InlineFragmentNode $selection */
                    $_astAndDefs = $this->collectFieldASTsAndDefs(
                        $context,
                        TypeInfo::typeFromAST($context->getSchema(), $selection->typeCondition),
                        $selection->selectionSet,
                        $_visitedFragmentNames,
                        $_astAndDefs
                    );
                    break;
                case NodeKind::FRAGMENT_SPREAD:
                    /* @var FragmentSpreadNode $selection */
                    $fragName = $selection->name->value;

                    if (empty($_visitedFragmentNames[$fragName])) {
                        $_visitedFragmentNames[$fragName] = true;
                        $fragment = $context->getFragment($fragName);

                        if ($fragment) {
                            $_astAndDefs = $this->collectFieldASTsAndDefs(
                                $context,
                                TypeInfo::typeFromAST($context->getSchema(), $fragment->typeCondition),
                                $fragment->selectionSet,
                                $_visitedFragmentNames,
                                $_astAndDefs
                            );
                        }
                    }
                    break;
            }
        }

        return $_astAndDefs;
    }

    protected function getFieldName(FieldNode $node)
    {
        $fieldName = $node->name->value;
        $responseName = $node->alias ? $node->alias->value : $fieldName;

        return $responseName;
    }

    abstract protected function isEnabled();
}
