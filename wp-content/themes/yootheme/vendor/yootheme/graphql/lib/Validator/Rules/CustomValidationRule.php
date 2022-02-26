<?php
namespace YOOtheme\GraphQL\Validator\Rules;

use YOOtheme\GraphQL\Error\Error;
use YOOtheme\GraphQL\Validator\ValidationContext;

class CustomValidationRule extends AbstractValidationRule
{
    private $visitorFn;

    public function __construct($name, callable $visitorFn)
    {
        $this->name = $name;
        $this->visitorFn = $visitorFn;
    }

    /**
     * @param ValidationContext $context
     * @return Error[]
     */
    public function getVisitor(ValidationContext $context)
    {
        $fn = $this->visitorFn;
        return $fn($context);
    }
}
