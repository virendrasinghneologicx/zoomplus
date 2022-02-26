<?php

namespace YOOtheme\Composer;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\NodeVisitorAbstract;

class NamespaceRenamer extends NodeVisitorAbstract
{
    protected $namespaces;

    public function __construct(array $namespaces = [])
    {
        krsort($namespaces);

        // remove trailing backslash
        foreach ($namespaces as $key => $value) {
            $this->namespaces[rtrim($key, '\\')] = rtrim($value, '\\');
        }
    }

    public function enterNode(Node $node)
    {
        if (!$node instanceof Name) {
            return;
        }

        if (!($name = $this->resolveName($node))) {
            return;
        }

        if ($match = $this->matchNamespace($node, $name)) {
            $node->parts = explode('\\', $match);
        }
    }

    protected function resolveName(Name $node)
    {
        $types = ['Stmt_Namespace', 'Stmt_UseUse'];
        $parent = $node->getAttribute('parent');

        if (in_array($parent->getType(), $types, true)) {
            return $node->toString();
        }

        return (string) $node->getAttribute('resolvedName');
    }

    protected function matchNamespace(Name $node, string $name)
    {
        foreach ($this->namespaces as $oldNamespace => $newNamespace) {
            $length = strlen($oldNamespace);

            if ($oldNamespace === substr($name, 0, $length)) {
                $newName = $newNamespace . substr($name, $length);

                // is partial namespace ?
                if ($node->toString() !== $name) {
                    $newParts = explode('\\', $newName);
                    $nameParts = explode('\\', $node->toString());

                    return join('\\', array_slice($newParts, -count($nameParts)));
                }

                return $newName;
            }
        }
    }
}
