<?php
namespace YOOtheme\GraphQL\Language\AST;

interface TypeExtensionNode extends TypeSystemDefinitionNode
{
    /**
    export type TypeExtensionNode =
      | ScalarTypeExtensionNode
      | ObjectTypeExtensionNode
      | InterfaceTypeExtensionNode
      | UnionTypeExtensionNode
      | EnumTypeExtensionNode
      | InputObjectTypeExtensionNode;
     */
}
