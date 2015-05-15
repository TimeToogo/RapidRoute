<?php

namespace RapidRoute\Compilation\Matchers;

use RapidRoute\Compilation\VarExporter;
use RapidRoute\Pattern;

/**
 * The regex matcher matches the route segment against a regular expression.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RegexMatcher extends SegmentMatcher
{
    /**
     * @var string
     */
    public $regex;

    public function __construct($regex, array $parameterKeys)
    {
        parent::__construct($parameterKeys);
        $this->regex      = $regex;
    }

    /**
     * @param string $pattern
     * @param int    $parameterKey
     *
     * @return RegexMatcher
     */
    public static function from($pattern, $parameterKey)
    {
        return new self(Pattern::asRegex($pattern), [$parameterKey]);
    }

    /**
     * @param string $segmentVariable
     * @param int    $uniqueKey
     *
     * @return string
     */
    public function getConditionExpression($segmentVariable, $uniqueKey)
    {
        return 'preg_match('
            . VarExporter::export($this->regex)
            . ', '
            . $segmentVariable
            . ', '
            . '$matches' . $uniqueKey
            . ')';
    }

    /**
     * @param string $segmentVariable
     * @param int    $uniqueKey
     *
     * @return string[]
     */
    public function getMatchedParameterExpressions($segmentVariable, $uniqueKey)
    {
        $matches = [];

        foreach(array_values($this->parameterKeys) as $i => $parameterKey) {
            // Use $i + 1 as the first $matches element is the full text that matched,
            // we want the groups
            $matches[$parameterKey] = '$matches' . $uniqueKey . '[' . ($i + 1) . ']';
        }

        return $matches;
    }

    /**
     * Returns a unique hash for the matching criteria of the segment.
     *
     * @return string
     */
    protected function getMatchHash()
    {
        return $this->regex;
    }
}