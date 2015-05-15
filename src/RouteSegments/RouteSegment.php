<?php

namespace RapidRoute\RouteSegments;

use RapidRoute\Compilation\Matchers\SegmentMatcher;

/**
 * The base route segment class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class RouteSegment
{
    /**
     * @return string
     */
    public static function getType()
    {
        return get_called_class();
    }

    /**
     * Returns an equivalent segment matcher and adds the parameters to the map.
     *
     * @param array $parameterIndexNameMap
     *
     * @return SegmentMatcher
     */
    public abstract function getMatcher(array &$parameterIndexNameMap);
}