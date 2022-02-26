<?php

namespace YOOtheme\Builder\Source;

class SourceQuery
{
    /**
     * Creates a source query.
     *
     * @param object $node
     *
     * @return array
     */
    public function create($node)
    {
        return $this->querySource($node->source, [
            'kind' => 'OperationDefinition',
            'operation' => 'query',
        ]);
    }

    /**
     * Query source definition.
     *
     * @param object $source
     * @param array  $parent
     *
     * @return array
     */
    public function querySource($source, array $parent)
    {
        $root = $query = $source->query;

        // add field selection
        if (isset($source->query->field)) {
            $query->selections = [$source->query->field];
            $query = $source->query->field;
        }

        // add source properties
        if (isset($source->props)) {
            $query->selections = $source->props;
            $props = [];

            // find properties to alias
            foreach ((array) $source->props as $prop) {
                if (in_array($prop->name, $props)) {
                    $prop->alias = count($props);
                }

                $props[] = $prop->name;
            }
        }

        return $this->queryField($root, $parent);
    }

    /**
     * Create nested fields AST.
     *
     * @param object $field
     * @param array  $parent
     *
     * @return array
     */
    public function queryField($field, array $parent)
    {
        $selections = null;

        foreach (array_reverse(explode('.', $field->name)) as $name) {
            $result = [
                'kind' => 'Field',
                'name' => [
                    'kind' => 'Name',
                    'value' => $name,
                ],
            ];

            if (!$selections) {
                $selection = isset($field->selections) ? $field->selections : [];

                if (isset($field->alias)) {
                    $result['alias'] = $this->createAlias($field, $name);
                }

                if (isset($field->arguments)) {
                    $result['arguments'] = $this->createArguments($field->arguments);
                }

                if (isset($field->directives)) {
                    $result['directives'] = $this->createDirectives($field->directives);
                }

                foreach ((array) $selection as $select) {
                    $result = $this->queryField($select, $result);
                }
            } else {
                $result['selectionSet']['kind'] = 'SelectionSet';
                $result['selectionSet']['selections'][] = $selections;
            }

            $selections = $result;
        }

        $parent['selectionSet']['kind'] = 'SelectionSet';
        $parent['selectionSet']['selections'][] = $selections;

        return $parent;
    }

    /**
     * Create field AST.
     *
     * @param mixed $directives
     *
     * @return array
     */
    public function createDirectives($directives)
    {
        $result = [];

        foreach ($directives as $directive) {
            $result[] = [
                'kind' => 'Directive',
                'name' => [
                    'kind' => 'Name',
                    'value' => $directive->name,
                ],
                'arguments' => isset($directive->arguments)
                    ? $this->createArguments($directive->arguments)
                    : null,
            ];
        }

        return $result;
    }

    /**
     * Create field AST.
     *
     * @param mixed $arguments
     *
     * @return array
     */
    public function createArguments($arguments)
    {
        $result = [];

        foreach ((array) $arguments as $name => $value) {
            $result[] = [
                'kind' => 'Argument',
                'name' => [
                    'kind' => 'Name',
                    'value' => $name,
                ],
                'value' => $this->createValue($value),
            ];
        }

        return $result;
    }

    /**
     * Create field AST.
     *
     * @param array $values
     *
     * @return array
     */
    public function createFields(array $values)
    {
        $fields = [];

        foreach ($values as $name => $value) {
            $fields[] = [
                'kind' => 'ObjectField',
                'name' => [
                    'kind' => 'Name',
                    'value' => $name,
                ],
                'value' => $this->createValue($value),
            ];
        }

        return $fields;
    }

    /**
     * Create field AST.
     *
     * @param mixed $value
     *
     * @return array
     */
    public function createValue($value)
    {
        $type = gettype($value);

        if ($type === 'array') {
            return [
                'kind' => 'ListValue',
                'values' => array_map([$this, 'createValue'], $value),
            ];
        }

        if ($type === 'object') {
            return [
                'kind' => 'ObjectValue',
                'fields' => $this->createFields(get_object_vars($value)),
            ];
        }

        return [
            'kind' => ucfirst(strtr($type, ['integer' => 'int']) . 'Value'),
            'value' => (string) $value,
        ];
    }

    /**
     * Create field AST.
     *
     * @param object $field
     * @param string $name
     *
     * @return array
     */
    public function createAlias($field, $name)
    {
        // append alias to field name
        $field->name .= $field->alias;

        return [
            'kind' => 'Name',
            'value' => $name . $field->alias,
        ];
    }
}
