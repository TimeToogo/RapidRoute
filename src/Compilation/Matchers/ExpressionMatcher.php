<?php

namespace RapidRoute\Compilation\Matchers;

/**
 * The any matcher matches any (non-empty) route segment.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ExpressionMatcher extends SingleParameterSegmentMatcher
{
    const SEGMENT_PLACEHOLDER = '{segment}';

    /**
     * @var string
     */
    public $expression;

    public function __construct($expression, $parameterKey)
    {
        parent::__construct($parameterKey);
        $this->expression = $expression;
    }

    /**
     * @param string $segmentVariable
     * @param int    $uniqueKey
     *
     * @return string
     */
    public function getConditionExpression($segmentVariable, $uniqueKey)
    {
        return str_replace(self::SEGMENT_PLACEHOLDER, $segmentVariable, $this->expression);
    }

    /**
     * Returns a unique hash for the matching criteria of the segment.
     *
     * @return string
     */
    protected function getMatchHash()
    {
        return $this->expression;
    }
}