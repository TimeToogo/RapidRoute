<?php

namespace RapidRoute\Compilation\RouteTree;

/**
 * The route tree class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RouteTree
{
    /**
     * @var MatchedRouteDataMap
     */
    protected $rootRouteData;

    /**
     * @var ChildrenNodeCollection[]
     */
    protected $segmentDepthNodeMap;

    public function __construct(MatchedRouteDataMap $rootRouteData = null, array $segmentDepthNodeMap)
    {
        $this->rootRouteData       = $rootRouteData;
        $this->segmentDepthNodeMap = $segmentDepthNodeMap;
    }

    /**
     * @return bool
     */
    public function hasRootRoute()
    {
        return $this->rootRouteData !== null && !$this->rootRouteData->isEmpty();
    }

    /**
     * @return MatchedRouteDataMap|null
     */
    public function getRootRouteData()
    {
        return $this->rootRouteData;
    }

    /**
     * @return ChildrenNodeCollection[]
     */
    public function getSegmentDepthNodesMap()
    {
        return $this->segmentDepthNodeMap;
    }
}