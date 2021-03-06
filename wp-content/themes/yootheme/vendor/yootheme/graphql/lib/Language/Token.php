<?php
namespace YOOtheme\GraphQL\Language;

/**
 * Represents a range of characters represented by a lexical token
 * within a Source.
 */
class Token
{
    // Each kind of token.
    const SOF = '<SOF>';
    const EOF = '<EOF>';
    const BANG = '!';
    const DOLLAR = '$';
    const PAREN_L = '(';
    const PAREN_R = ')';
    const SPREAD = '...';
    const COLON = ':';
    const EQUALS = '=';
    const AT = '@';
    const BRACKET_L = '[';
    const BRACKET_R = ']';
    const BRACE_L = '{';
    const PIPE = '|';
    const BRACE_R = '}';
    const NAME = 'Name';
    const INT = 'Int';
    const FLOAT = 'Float';
    const STRING = 'String';
    const BLOCK_STRING = 'BlockString';
    const COMMENT = 'Comment';

    /**
     * The kind of Token (see one of constants above).
     *
     * @var string
     */
    public $kind;

    /**
     * The character offset at which this Node begins.
     *
     * @var int
     */
    public $start;

    /**
     * The character offset at which this Node ends.
     *
     * @var int
     */
    public $end;

    /**
     * The 1-indexed line number on which this Token appears.
     *
     * @var int
     */
    public $line;

    /**
     * The 1-indexed column number at which this Token begins.
     *
     * @var int
     */
    public $column;

    /**
     * @var string|null
     */
    public $value;

    /**
     * Tokens exist as nodes in a double-linked-list amongst all tokens
     * including ignored tokens. <SOF> is always the first node and <EOF>
     * the last.
     *
     * @var Token
     */
    public $prev;

    /**
     * @var Token
     */
    public $next;

    /**
     * Token constructor.
     * @param $kind
     * @param $start
     * @param $end
     * @param $line
     * @param $column
     * @param Token $previous
     * @param null $value
     */
    public function __construct($kind, $start, $end, $line, $column, Token $previous = null, $value = null)
    {
        $this->kind = $kind;
        $this->start = (int) $start;
        $this->end = (int) $end;
        $this->line = (int) $line;
        $this->column = (int) $column;
        $this->prev = $previous;
        $this->next = null;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->kind . ($this->value ? ' "' . $this->value  . '"' : '');
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'kind' => $this->kind,
            'value' => $this->value,
            'line' => $this->line,
            'column' => $this->column
        ];
    }
}
