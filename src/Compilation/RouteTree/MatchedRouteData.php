<?php

namespace RapidRoute\Compilation\RouteTree;

use RapidRoute\Route;
use RapidRoute\RouteSegments\ParameterSegment;

/**
 * The matched route data class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MatchedRouteData
{
    /**
     * @var string[]
     */
    protected $parameterIndexNameMap;

    /**
     * @var array|object
     */
    protected $routeData;

    public function __construct(array $parameterIndexNameMap, $routeData)
    {
        $this->parameterIndexNameMap = $parameterIndexNameMap;
        $this->routeData             = $routeData;
    }

    /**
     * @param array $parameterIndexNameMap
     * @param Route $route
     *
     * @return MatchedRouteData
     */
    public static function from(array $parameterIndexNameMap, Route $route)
    {
        return new self($parameterIndexNameMap, $route->getData());
    }

    /**
     * @return string[]
     */
    public function getParameterIndexNameMap()
    {
        return $this->parameterIndexNameMap;
    }

    /**
     * @return array|object
     */
    public function getRouteData()
    {
        return $this->routeData;
    }
}