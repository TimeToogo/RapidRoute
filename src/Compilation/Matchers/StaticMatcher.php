<?php

namespace RapidRoute\Compilation\Matchers;

use RapidRoute\Compilation\VarExporter;

/**
 * The static matcher matches the route segment against a static string.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class StaticMatcher extends SegmentMatcher
{
    /**
     * The static string
     *
     * @var string
     */
    public $segment;

    public function __construct($segment, $parameterKey = null)
    {
        parent::__construct($parameterKey === null ? [] : [$parameterKey]);
        $this->segment     = $segment;
    }

    public function getConditionExpression($segmentVariable, $uniqueKey)
    {
        return $segmentVariable . ' === ' . VarExporter::export($this->segment);
    }

    public function getMatchedParameterExpressions($segmentVariable, $uniqueKey)
    {
        return $this->parameterKeys ? [$this->parameterKeys[0] => $segmentVariable] : [];
    }

    /**
     * Returns a unique hash for the matching criteria of the segment.
     *
     * @return string
     */
    protected function getMatchHash()
    {
        return $this->segment;
    }
}