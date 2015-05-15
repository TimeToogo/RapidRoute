<?php

namespace RapidRoute\Compilation\Matchers;

/**
 * The compound matcher matches against all the inner matchers.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CompoundMatcher extends SegmentMatcher
{
    /**
     * @var SegmentMatcher[]
     */
    protected $matchers;

    public function __construct(array $matchers)
    {
        $parameterKeys = [];

        foreach($matchers as $matcher) {
            $parameterKeys = array_merge($parameterKeys, $matcher->getParameterKeys());
        }

        parent::__construct($parameterKeys);
        $this->matchers = $matchers;
    }

    /**
     * @param string $segmentVariable
     * @param int    $uniqueKey
     *
     * @return string
     */
    public function getConditionExpression($segmentVariable, $uniqueKey)
    {
        $conditions = [];

        foreach($this->matchers as $key => $matcher) {
            $conditions[] = $matcher->getConditionExpression($segmentVariable, $uniqueKey . '_' . $key);
        }

        return implode(' && ', $conditions);
    }

    public function getMatchedParameterExpressions($segmentVariable, $uniqueKey)
    {
        $expressions = [];

        foreach($this->matchers as $key => $matcher) {
            $matchedParameterExpressions = $matcher->getMatchedParameterExpressions(
                $segmentVariable,
                $uniqueKey . '_' . $key
            );

            foreach($matchedParameterExpressions as $parameter => $expression) {
                $expressions[$parameter] = $expression;
            }
        }

        return $expressions;
    }

    /**
     * Returns a unique hash for the matching criteria of the segment.
     *
     * @return string
     */
    protected function getMatchHash()
    {
        $hashes = [];

        foreach($this->matchers as $matcher) {
            $hashes[] = $matcher->getHash();
        }

        return implode('::', $hashes);
    }
}