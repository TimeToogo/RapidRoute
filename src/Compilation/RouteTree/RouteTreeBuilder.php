<?php

namespace RapidRoute\Compilation\RouteTree;

use RapidRoute\Route;
use RapidRoute\RouteCollection;
use RapidRoute\RouteSegments\RouteSegment;

/**
 * The route tree builder class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RouteTreeBuilder
{
    /**
     * Creates a route tree from the supplied routes.
     *
     * @param RouteCollection $routes
     *
     * @return RouteTree
     */
    public function build(RouteCollection $routes)
    {
        $rootRouteData = null;
        $nodes         = [];
        $groupedRoutes = [];

        foreach ($routes->asArray() as $route) {
            $groupedRoutes[count($route->getSegments())][] = $route;
        }

        if (isset($groupedRoutes[0])) {
            $rootRouteData = new MatchedRouteDataMap();
            $rootRouteData->addRoute($groupedRoutes[0][0], []);
            unset($groupedRoutes[0]);
        }

        foreach ($groupedRoutes as $segmentDepth => $group) {
            /** @var Route[] $group */
            $groupNodes = [];

            foreach ($group as $route) {
                $parameterIndexNameMap = array();
                $segments              = $route->getSegments();
                /** @var RouteSegment $firstSegment */
                $firstSegment     = array_shift($segments);
                $firstSegmentHash = $firstSegment->getMatcher($parameterIndexNameMap)->getHash();

                if (!isset($groupNodes[$firstSegmentHash])) {
                    $groupNodes[$firstSegmentHash] = RouteTreeNode::create(
                        [0 => $firstSegment->getMatcher($parameterIndexNameMap)],
                        $segmentDepth === 1
                    );
                }

                $this->addRouteToNode($groupNodes[$firstSegmentHash], $route, $segments, 1, $parameterIndexNameMap);
            }

            $nodes[$segmentDepth] = new ChildrenNodeCollection($groupNodes);
        }

        return new RouteTree($rootRouteData, $nodes);
    }

    protected function addRouteToNode(
        RouteTreeNode $node,
        Route $route,
        array $segments,
        $segmentDepth,
        array $parameterIndexNameMap
    ) {
        if (empty($segments)) {
            $node->getContents()->addRoute($route, $parameterIndexNameMap);

            return;
        }

        /** @var RouteSegment $segment */
        $segment             = array_shift($segments);
        $childSegmentMatcher = $segment->getMatcher($parameterIndexNameMap);

        if ($node->getContents()->hasChildFor($childSegmentMatcher)) {
            $child = $node->getContents()->getChild($childSegmentMatcher);
        } else {
            $child = RouteTreeNode::create([$segmentDepth => $childSegmentMatcher], empty($segments));
            $node->getContents()->addChild($child);
        }

        self::addRouteToNode($child, $route, $segments, $segmentDepth + 1, $parameterIndexNameMap);
    }
}