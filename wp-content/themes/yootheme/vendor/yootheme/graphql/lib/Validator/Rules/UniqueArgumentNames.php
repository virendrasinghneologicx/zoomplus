<?php
namespace YOOtheme\GraphQL\Validator\Rules;

use YOOtheme\GraphQL\Error\Error;
use YOOtheme\GraphQL\Language\AST\ArgumentNode;
use YOOtheme\GraphQL\Language\AST\Node;
use YOOtheme\GraphQL\Language\AST\NodeKind;
use YOOtheme\GraphQL\Language\Visitor;
use YOOtheme\GraphQL\Validator\ValidationContext;

class UniqueArgumentNames extends AbstractValidationRule
{
    static function duplicateArgMessage($argName)
    {
        return "There can be only one argument named \"$argName\".";
    }

    public $knownArgNames;

    public function getVisitor(ValidationContext $context)
    {
        $this->knownArgNames = [];

        return [
            NodeKind::FIELD => function () {
                $this->knownArgNames = [];;
            },
            NodeKind::DIRECTIVE => function () {
                $this->knownArgNames = [];
            },
            NodeKind::ARGUMENT => function (ArgumentNode $node) use ($context) {
                $argName = $node->name->value;
                if (!empty($this->knownArgNames[$argName])) {
                    $context->reportError(new Error(
                        self::duplicateArgMessage($argName),
                        [$this->knownArgNames[$argName], $node->name]
                    ));
                } else {
                    $this->knownArgNames[$argName] = $node->name;
                }
                return Visitor::skipNode();
            }
        ];
    }
}
