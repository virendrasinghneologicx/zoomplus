<?php
namespace YOOtheme\GraphQL\Validator\Rules;

use YOOtheme\GraphQL\Error\Error;
use YOOtheme\GraphQL\Language\AST\NodeKind;
use YOOtheme\GraphQL\Language\AST\ObjectFieldNode;
use YOOtheme\GraphQL\Language\Visitor;
use YOOtheme\GraphQL\Validator\ValidationContext;

class UniqueInputFieldNames extends AbstractValidationRule
{
    static function duplicateInputFieldMessage($fieldName)
    {
      return "There can be only one input field named \"$fieldName\".";
    }

    public $knownNames;
    public $knownNameStack;

    public function getVisitor(ValidationContext $context)
    {
        $this->knownNames = [];
        $this->knownNameStack = [];

        return [
            NodeKind::OBJECT => [
                'enter' => function() {
                    $this->knownNameStack[] = $this->knownNames;
                    $this->knownNames = [];
                },
                'leave' => function() {
                    $this->knownNames = array_pop($this->knownNameStack);
                }
            ],
            NodeKind::OBJECT_FIELD => function(ObjectFieldNode $node) use ($context) {
                $fieldName = $node->name->value;

                if (!empty($this->knownNames[$fieldName])) {
                    $context->reportError(new Error(
                        self::duplicateInputFieldMessage($fieldName),
                        [ $this->knownNames[$fieldName], $node->name ]
                    ));
                } else {
                    $this->knownNames[$fieldName] = $node->name;
                }
                return Visitor::skipNode();
            }
        ];
    }
}
