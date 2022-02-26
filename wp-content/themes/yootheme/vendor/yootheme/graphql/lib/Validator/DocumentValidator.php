<?php
namespace YOOtheme\GraphQL\Validator;

use YOOtheme\GraphQL\Error\Error;
use YOOtheme\GraphQL\Language\AST\DocumentNode;
use YOOtheme\GraphQL\Language\Visitor;
use YOOtheme\GraphQL\Type\Schema;
use YOOtheme\GraphQL\Type\Definition\Type;
use YOOtheme\GraphQL\Utils\TypeInfo;
use YOOtheme\GraphQL\Validator\Rules\AbstractValidationRule;
use YOOtheme\GraphQL\Validator\Rules\ValuesOfCorrectType;
use YOOtheme\GraphQL\Validator\Rules\DisableIntrospection;
use YOOtheme\GraphQL\Validator\Rules\ExecutableDefinitions;
use YOOtheme\GraphQL\Validator\Rules\FieldsOnCorrectType;
use YOOtheme\GraphQL\Validator\Rules\FragmentsOnCompositeTypes;
use YOOtheme\GraphQL\Validator\Rules\KnownArgumentNames;
use YOOtheme\GraphQL\Validator\Rules\KnownDirectives;
use YOOtheme\GraphQL\Validator\Rules\KnownFragmentNames;
use YOOtheme\GraphQL\Validator\Rules\KnownTypeNames;
use YOOtheme\GraphQL\Validator\Rules\LoneAnonymousOperation;
use YOOtheme\GraphQL\Validator\Rules\NoFragmentCycles;
use YOOtheme\GraphQL\Validator\Rules\NoUndefinedVariables;
use YOOtheme\GraphQL\Validator\Rules\NoUnusedFragments;
use YOOtheme\GraphQL\Validator\Rules\NoUnusedVariables;
use YOOtheme\GraphQL\Validator\Rules\OverlappingFieldsCanBeMerged;
use YOOtheme\GraphQL\Validator\Rules\PossibleFragmentSpreads;
use YOOtheme\GraphQL\Validator\Rules\ProvidedNonNullArguments;
use YOOtheme\GraphQL\Validator\Rules\QueryComplexity;
use YOOtheme\GraphQL\Validator\Rules\QueryDepth;
use YOOtheme\GraphQL\Validator\Rules\ScalarLeafs;
use YOOtheme\GraphQL\Validator\Rules\UniqueArgumentNames;
use YOOtheme\GraphQL\Validator\Rules\UniqueDirectivesPerLocation;
use YOOtheme\GraphQL\Validator\Rules\UniqueFragmentNames;
use YOOtheme\GraphQL\Validator\Rules\UniqueInputFieldNames;
use YOOtheme\GraphQL\Validator\Rules\UniqueOperationNames;
use YOOtheme\GraphQL\Validator\Rules\UniqueVariableNames;
use YOOtheme\GraphQL\Validator\Rules\VariablesAreInputTypes;
use YOOtheme\GraphQL\Validator\Rules\VariablesDefaultValueAllowed;
use YOOtheme\GraphQL\Validator\Rules\VariablesInAllowedPosition;

/**
 * Implements the "Validation" section of the spec.
 *
 * Validation runs synchronously, returning an array of encountered errors, or
 * an empty array if no errors were encountered and the document is valid.
 *
 * A list of specific validation rules may be provided. If not provided, the
 * default list of rules defined by the GraphQL specification will be used.
 *
 * Each validation rule is an instance of GraphQL\Validator\Rules\AbstractValidationRule
 * which returns a visitor (see the [GraphQL\Language\Visitor API](reference.md#graphqllanguagevisitor)).
 *
 * Visitor methods are expected to return an instance of [GraphQL\Error\Error](reference.md#graphqlerrorerror),
 * or array of such instances when invalid.
 *
 * Optionally a custom TypeInfo instance may be provided. If not provided, one
 * will be created from the provided schema.
 */
class DocumentValidator
{
    private static $rules = [];

    private static $defaultRules;

    private static $securityRules;

    private static $initRules = false;

    /**
     * Primary method for query validation. See class description for details.
     *
     * @api
     * @param Schema $schema
     * @param DocumentNode $ast
     * @param AbstractValidationRule[]|null $rules
     * @param TypeInfo|null $typeInfo
     * @return Error[]
     */
    public static function validate(
        Schema $schema,
        DocumentNode $ast,
        array $rules = null,
        TypeInfo $typeInfo = null
    )
    {
        if (null === $rules) {
            $rules = static::allRules();
        }

        if (true === is_array($rules) && 0 === count($rules)) {
            // Skip validation if there are no rules
            return [];
        }

        $typeInfo = $typeInfo ?: new TypeInfo($schema);
        $errors = static::visitUsingRules($schema, $typeInfo, $ast, $rules);
        return $errors;
    }


    /**
     * Returns all global validation rules.
     *
     * @api
     * @return AbstractValidationRule[]
     */
    public static function allRules()
    {
        if (!self::$initRules) {
            static::$rules = array_merge(static::defaultRules(), self::securityRules(), self::$rules);
            static::$initRules = true;
        }

        return self::$rules;
    }

    public static function defaultRules()
    {
        if (null === self::$defaultRules) {
            self::$defaultRules = [
                ExecutableDefinitions::class => new ExecutableDefinitions(),
                UniqueOperationNames::class => new UniqueOperationNames(),
                LoneAnonymousOperation::class => new LoneAnonymousOperation(),
                KnownTypeNames::class => new KnownTypeNames(),
                FragmentsOnCompositeTypes::class => new FragmentsOnCompositeTypes(),
                VariablesAreInputTypes::class => new VariablesAreInputTypes(),
                ScalarLeafs::class => new ScalarLeafs(),
                FieldsOnCorrectType::class => new FieldsOnCorrectType(),
                UniqueFragmentNames::class => new UniqueFragmentNames(),
                KnownFragmentNames::class => new KnownFragmentNames(),
                NoUnusedFragments::class => new NoUnusedFragments(),
                PossibleFragmentSpreads::class => new PossibleFragmentSpreads(),
                NoFragmentCycles::class => new NoFragmentCycles(),
                UniqueVariableNames::class => new UniqueVariableNames(),
                NoUndefinedVariables::class => new NoUndefinedVariables(),
                NoUnusedVariables::class => new NoUnusedVariables(),
                KnownDirectives::class => new KnownDirectives(),
                UniqueDirectivesPerLocation::class => new UniqueDirectivesPerLocation(),
                KnownArgumentNames::class => new KnownArgumentNames(),
                UniqueArgumentNames::class => new UniqueArgumentNames(),
                ValuesOfCorrectType::class => new ValuesOfCorrectType(),
                ProvidedNonNullArguments::class => new ProvidedNonNullArguments(),
                VariablesDefaultValueAllowed::class => new VariablesDefaultValueAllowed(),
                VariablesInAllowedPosition::class => new VariablesInAllowedPosition(),
                OverlappingFieldsCanBeMerged::class => new OverlappingFieldsCanBeMerged(),
                UniqueInputFieldNames::class => new UniqueInputFieldNames(),
            ];
        }

        return self::$defaultRules;
    }

    /**
     * @return array
     */
    public static function securityRules()
    {
        // This way of defining rules is deprecated
        // When custom security rule is required - it should be just added via DocumentValidator::addRule();
        // TODO: deprecate this

        if (null === self::$securityRules) {
            self::$securityRules = [
                DisableIntrospection::class => new DisableIntrospection(DisableIntrospection::DISABLED), // DEFAULT DISABLED
                QueryDepth::class => new QueryDepth(QueryDepth::DISABLED), // default disabled
                QueryComplexity::class => new QueryComplexity(QueryComplexity::DISABLED), // default disabled
            ];
        }
        return self::$securityRules;
    }

    /**
     * Returns global validation rule by name. Standard rules are named by class name, so
     * example usage for such rules:
     *
     * $rule = DocumentValidator::getRule(GraphQL\Validator\Rules\QueryComplexity::class);
     *
     * @api
     * @param string $name
     * @return AbstractValidationRule
     */
    public static function getRule($name)
    {
        $rules = static::allRules();

        if (isset($rules[$name])) {
            return $rules[$name];
        }

        $name = "GraphQL\\Validator\\Rules\\$name";
        return isset($rules[$name]) ? $rules[$name] : null ;
    }

    /**
     * Add rule to list of global validation rules
     *
     * @api
     * @param AbstractValidationRule $rule
     */
    public static function addRule(AbstractValidationRule $rule)
    {
        self::$rules[$rule->getName()] = $rule;
    }

    public static function isError($value)
    {
        return is_array($value)
            ? count(array_filter($value, function($item) { return $item instanceof \Exception || $item instanceof \Throwable;})) === count($value)
            : ($value instanceof \Exception || $value instanceof \Throwable);
    }

    public static function append(&$arr, $items)
    {
        if (is_array($items)) {
            $arr = array_merge($arr, $items);
        } else {
            $arr[] = $items;
        }
        return $arr;
    }

    /**
     * Utility which determines if a value literal node is valid for an input type.
     *
     * Deprecated. Rely on validation for documents containing literal values.
     *
     * @deprecated
     * @return Error[]
     */
    public static function isValidLiteralValue(Type $type, $valueNode)
    {
        $emptySchema = new Schema([]);
        $emptyDoc = new DocumentNode(['definitions' => []]);
        $typeInfo = new TypeInfo($emptySchema, $type);
        $context = new ValidationContext($emptySchema, $emptyDoc, $typeInfo);
        $validator = new ValuesOfCorrectType();
        $visitor = $validator->getVisitor($context);
        Visitor::visit($valueNode, Visitor::visitWithTypeInfo($typeInfo, $visitor));
        return $context->getErrors();
    }

    /**
     * This uses a specialized visitor which runs multiple visitors in parallel,
     * while maintaining the visitor skip and break API.
     *
     * @param Schema $schema
     * @param TypeInfo $typeInfo
     * @param DocumentNode $documentNode
     * @param AbstractValidationRule[] $rules
     * @return array
     */
    public static function visitUsingRules(Schema $schema, TypeInfo $typeInfo, DocumentNode $documentNode, array $rules)
    {
        $context = new ValidationContext($schema, $documentNode, $typeInfo);
        $visitors = [];
        foreach ($rules as $rule) {
            $visitors[] = $rule->getVisitor($context);
        }
        Visitor::visit($documentNode, Visitor::visitWithTypeInfo($typeInfo, Visitor::visitInParallel($visitors)));
        return $context->getErrors();
    }
}
