<?php

namespace RapidRoute\RouteSegments;

use RapidRoute\Compilation\Matchers\SegmentMatcher;
use RapidRoute\Compilation\Matchers\StaticMatcher;
use RapidRoute\RapidRouteException;

/**
 * Route segment for a static segment of the uri.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class StaticSegment extends RouteSegment
{
    /**
     * @var string
     */
    protected $value;

    public function __construct($value)
    {
        if(strpos($value, '/') !== false) {
            throw new RapidRouteException(
                sprintf('Cannot create %s: value cannot contain \'/\', \'%s\' given', __CLASS__, $value)
            );
        }

        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    public function getMatcher(array &$parameterIndexNameMap)
    {
        return new StaticMatcher($this->value);
    }
}