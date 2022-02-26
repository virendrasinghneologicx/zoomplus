<?php
namespace YOOtheme\GraphQL\Validator\Rules;

use YOOtheme\GraphQL\Error\Error;
use YOOtheme\GraphQL\Language\AST\FieldNode;
use YOOtheme\GraphQL\Language\AST\NodeKind;
use YOOtheme\GraphQL\Validator\ValidationContext;

class DisableIntrospection extends AbstractQuerySecurity
{
    const ENABLED = 1;
    private $isEnabled;

    public function __construct($enabled = self::ENABLED)
    {
        $this->setEnabled($enabled);
    }

    public function setEnabled($enabled)
    {
        $this->isEnabled = $enabled;
    }

    static function introspectionDisabledMessage()
    {
        return 'GraphQL introspection is not allowed, but the query contained __schema or __type';
    }

    protected function isEnabled()
    {
        return $this->isEnabled !== static::DISABLED;
    }

    public function getVisitor(ValidationContext $context)
    {
        return $this->invokeIfNeeded(
            $context,
            [
                NodeKind::FIELD => function (FieldNode $node) use ($context) {
                    if ($node->name->value === '__type' || $node->name->value === '__schema') {
                        $context->reportError(new Error(
                            static::introspectionDisabledMessage(),
                            [$node]
                        ));
                    }
                }
            ]
        );
    }
}
