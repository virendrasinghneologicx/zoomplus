<?php
namespace YOOtheme\GraphQL\Validator\Rules;

use YOOtheme\GraphQL\Error\Error;
use YOOtheme\GraphQL\Language\AST\DirectiveNode;
use YOOtheme\GraphQL\Language\AST\Node;
use YOOtheme\GraphQL\Validator\ValidationContext;

class UniqueDirectivesPerLocation extends AbstractValidationRule
{
    static function duplicateDirectiveMessage($directiveName)
    {
        return 'The directive "'.$directiveName.'" can only be used once at this location.';
    }

    public function getVisitor(ValidationContext $context)
    {
        return [
            'enter' => function(Node $node) use ($context) {
                if (isset($node->directives)) {
                    $knownDirectives = [];
                    foreach ($node->directives as $directive) {
                        /** @var DirectiveNode $directive */
                        $directiveName = $directive->name->value;
                        if (isset($knownDirectives[$directiveName])) {
                            $context->reportError(new Error(
                                self::duplicateDirectiveMessage($directiveName),
                                [$knownDirectives[$directiveName], $directive]
                            ));
                        } else {
                            $knownDirectives[$directiveName] = $directive;
                        }
                    }
                }
            }
        ];
    }
}
