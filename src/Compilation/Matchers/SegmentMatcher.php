<?php

namespace RapidRoute\Compilation\Matchers;

use RapidRoute\RapidRouteException;

/**
 * The base route segment matcher class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class SegmentMatcher
{
    /**
     * @var int[]
     */
    protected $parameterKeys;

    public function __construct(array $parameterKeys)
    {
        $this->parameterKeys = $parameterKeys;
    }

    /**
     * @return int[]
     */
    public function getParameterKeys()
    {
        return $this->parameterKeys;
    }

    /**
     * @return string
     */
    public static function getType()
    {
        return get_called_class();
    }

    /**
     * @param string $segmentVariable
     * @param int    $uniqueKey
     *
     * @return string
     */
    abstract public function getConditionExpression($segmentVariable, $uniqueKey);

    /**
     * @param string $segmentVariable
     * @param int    $uniqueKey
     *
     * @return string[]
     */
    public function getMatchedParameterExpressions($segmentVariable, $uniqueKey)
    {
        return array_fill_keys($this->parameterKeys, $segmentVariable);
    }

    public function mergeParameterKeys(SegmentMatcher $matcher)
    {;
        if($matcher->getHash() !== $this->getHash()) {
            throw new RapidRouteException(
                sprintf('Cannot merge parameters: matchers must be equivalent, \'%s\' expected, \'%s\' given', get_class($matcher), $this->getHash())
            );
        }

        $this->parameterKeys = array_unique(
            array_merge($this->parameterKeys, $matcher->parameterKeys),
            SORT_NUMERIC
        );
    }

    /**
     * Returns a unique hash for the segment matcher
     *
     * @return string
     */
    public function getHash()
    {
        return get_class($this) . ':' . $this->getMatchHash();
    }

    /**
     * Returns a unique hash for the matching criteria of the segment.
     *
     * @return string
     */
    abstract protected function getMatchHash();
}