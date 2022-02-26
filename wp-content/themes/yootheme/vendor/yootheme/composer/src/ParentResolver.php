<?php

namespace YOOtheme\Composer;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class ParentResolver extends NodeVisitorAbstract
{
    protected $stack;

    public function beginTraverse(array $nodes)
    {
        $this->stack = [];
    }

    public function enterNode(Node $node)
    {
        if (!empty($this->stack)) {
            $node->setAttribute('parent', $this->stack[count($this->stack) - 1]);
        }

        $this->stack[] = $node;
    }

    public function leaveNode(Node $node)
    {
        array_pop($this->stack);
    }
}
