<?php

namespace RapidRoute\Tests\Unit\Compilation\RouteTree;

use RapidRoute\Compilation\RouteTree\ChildrenNodeCollection;
use RapidRoute\Compilation\RouteTree\MatchedRouteDataMap;
use RapidRoute\Compilation\RouteTree\RouteTree;
use RapidRoute\Tests\RapidRouteTest;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RouteTreeTest extends RapidRouteTest
{
    public function testRouteTree()
    {
        $segmentDepthNodeMap = [
            1 => $this->mockChildrenNodes()
        ];

        $tree = new RouteTree(null, $segmentDepthNodeMap);

        $this->assertFalse($tree->hasRootRoute());
        $this->assertNull($tree->getRootRouteData());
        $this->assertSame($segmentDepthNodeMap, $tree->getSegmentDepthNodesMap());
    }

    public function testRouteTreeWithRootRoute()
    {
        $rootRoute = $this->getMockBuilder(MatchedRouteDataMap::getType())->disableOriginalConstructor()->getMock();
        $segmentDepthNodeMap = [
            1 => $this->mockChildrenNodes()
        ];

        $tree = new RouteTree($rootRoute, $segmentDepthNodeMap);

        $this->assertTrue($tree->hasRootRoute());
        $this->assertSame($rootRoute, $tree->getRootRouteData());
        $this->assertSame($segmentDepthNodeMap, $tree->getSegmentDepthNodesMap());
    }

    protected function mockChildrenNodes()
    {
        return $this->getMockBuilder(ChildrenNodeCollection::getType())->disableOriginalConstructor()->getMock();
    }
}