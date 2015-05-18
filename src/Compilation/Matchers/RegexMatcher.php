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

    /**
     * @var int[]
     */
    public $parameterKeyGroupMap;

    public function __construct($regex, array $parameterKeyGroupMap)
    {
        parent::__construct(array_keys($parameterKeyGroupMap));
        $this->regex      = $regex;
        $this->parameterKeyGroupMap = $parameterKeyGroupMap;
    }

    /**
     * @return int
     */
    public function getGroupCount()
    {
        return count(array_unique($this->parameterKeyGroupMap, SORT_NUMERIC));
    }

    /**
     * @param string $pattern
     * @param int    $parameterKey
     *
     * @return RegexMatcher
     */
    public static function from($pattern, $parameterKey)
    {
        return new self(Pattern::asRegex($pattern), [$parameterKey => 0]);
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

    public function mergeParameterKeys(SegmentMatcher $matcher)
    {
        /** @var RegexMatcher $matcher */
        parent::mergeParameterKeys($matcher);
        $this->parameterKeyGroupMap += $matcher->parameterKeyGroupMap;
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

        foreach($this->parameterKeyGroupMap as $parameterKey => $group) {
            // Use $group + 1 as the first $matches element is the full text that matched,
            // we want the groups
            $matches[$parameterKey] = '$matches' . $uniqueKey . '[' . ($group + 1) . ']';
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