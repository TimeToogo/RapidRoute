<?php

namespace RapidRoute\Compilation\Matchers;

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
        return [$this->parameterKeys[0] => $segmentVariable];
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
    protected abstract function getMatchHash();
}