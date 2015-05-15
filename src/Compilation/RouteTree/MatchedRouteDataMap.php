<?php

namespace RapidRoute\Compilation\RouteTree;

use RapidRoute\Route;

/**
 * The matched route data content class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MatchedRouteDataMap extends NodeContentsBase
{
    /**
     * @var array
     */
    protected $httpMethodRouteDataMap = [];

    /**
     * @var MatchedRouteData|null
     */
    protected $defaultRouteData = null;

    public function __construct(array $httpMethodRouteDataMap = [], MatchedRouteData $defaultRouteData = null)
    {
        $this->httpMethodRouteDataMap = $httpMethodRouteDataMap;
        $this->defaultRouteData       = $defaultRouteData;
    }

    /**
     * @return array
     */
    public function getHttpMethodRouteDataMap()
    {
        return $this->httpMethodRouteDataMap;
    }

    /**
     * @return array|null
     */
    public function getAllowedHttpMethods()
    {
        if($this->hasDefaultRouteData()) {
            return null;
        }

        $allowedHttpMethods = [];

        foreach($this->httpMethodRouteDataMap as $item) {
            foreach($item[0] as $method) {
                $allowedHttpMethods[$method] = true;
            }
        }

        return array_keys($allowedHttpMethods);
    }

    /**
     * @return MatchedRouteData|null
     */
    public function getDefaultRouteData()
    {
        return $this->defaultRouteData;
    }

    /**
     * @return bool
     */
    public function hasDefaultRouteData()
    {
        return $this->defaultRouteData !== null;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->defaultRouteData === null && empty($this->httpMethodRouteDataMap);
    }

    /**
     * Adds the supplied route to the matched route data map
     *
     * @param Route $route
     * @param array $parameterIndexNameMap
     *
     * @return void
     */
    public function addRoute(Route $route, array $parameterIndexNameMap)
    {
        if ($route->allowsAnyHttpMethod()) {
            $this->defaultRouteData   = MatchedRouteData::from($parameterIndexNameMap, $route);
        } else {
            $this->httpMethodRouteDataMap[] = [$route->getHttpMethods(), MatchedRouteData::from($parameterIndexNameMap, $route)];
        }
    }
}