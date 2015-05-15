<?php

namespace RapidRoute\Tests\Unit\Compilation\RouteTree;

use RapidRoute\Compilation\Matchers\SegmentMatcher;
use RapidRoute\Compilation\RouteTree\ChildrenNodeCollection;
use RapidRoute\Compilation\RouteTree\MatchedRouteData;
use RapidRoute\Compilation\RouteTree\MatchedRouteDataMap;
use RapidRoute\Compilation\RouteTree\RouteTreeNode;
use RapidRoute\Pattern;
use RapidRoute\RapidRouteException;
use RapidRoute\Route;
use RapidRoute\RouteSegments\ParameterSegment;
use RapidRoute\RouteSegments\StaticSegment;
use RapidRoute\Tests\RapidRouteTest;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RouteTreeNodeTest extends RapidRouteTest
{
    public function testMaintainsMatcherOrder()
    {
        $matcher1 = $this->getMatcherMock();
        $matcher2 = $this->getMatcherMock();

        $node = new RouteTreeNode([1 => $matcher2, 0 => $matcher1], new ChildrenNodeCollection());

        $this->assertSame([1 => $matcher2, 0 => $matcher1], $node->getMatchers());
        $this->assertSame($matcher1, $node->getFirstMatcher());
    }

    public function testParentRouteTreeNode()
    {
        $matcher  = $this->getMatcherMock();
        $contents = new ChildrenNodeCollection();
        $node     = new RouteTreeNode([$matcher], $contents);

        $this->assertSame([$matcher], $node->getMatchers());
        $this->assertSame($matcher, $node->getFirstMatcher());
        $this->assertSame($contents, $node->getContents());
        $this->assertTrue($node->isParentNode());
        $this->assertFalse($node->isLeafNode());
    }

    public function testLeafRouteTreeNode()
    {
        $matcher  = $this->getMatcherMock();
        $contents = new MatchedRouteDataMap();
        $node     = new RouteTreeNode([$matcher], $contents);

        $this->assertSame([$matcher], $node->getMatchers());
        $this->assertSame($matcher, $node->getFirstMatcher());
        $this->assertSame($contents, $node->getContents());
        $this->assertTrue($node->isLeafNode());
        $this->assertFalse($node->isParentNode());
    }

    public function testChildrenCollectionOperations()
    {
        $node = new RouteTreeNode([$this->getMatcherMock()], new ChildrenNodeCollection());

        $child = new RouteTreeNode([$this->getMatcherMock('some-hash')], new ChildrenNodeCollection());

        $node->getContents()->addChild($child);

        $this->assertSame([$child->getFirstMatcher()->getHash() => $child], $node->getContents()->getChildren());
        $this->assertTrue($node->getContents()->hasChild($child));
        $this->assertTrue($node->getContents()->hasChildFor($child->getFirstMatcher()));
        $this->assertTrue($node->getContents()->hasChild(clone $child));
        $this->assertTrue($node->getContents()->hasChildFor(clone $child->getFirstMatcher()));
        $this->assertFalse($node->getContents()->hasChildFor($this->getMatcherMock('some-other-hash')));
        $this->assertSame($child, $node->getContents()->getChild($child->getFirstMatcher()));
    }

    public function testMatchedRouteDataMapOperations()
    {
        $node = new RouteTreeNode([$this->getMatcherMock()], new MatchedRouteDataMap());

        $node->getContents()->addRoute($this->mockRoute(['GET', 'POST'], ['first_route']), []);

        $this->assertSame(['GET', 'POST'], $node->getContents()->getAllowedHttpMethods());
        $this->assertEquals(
            [
                [['GET', 'POST'], new MatchedRouteData([], ['first_route'])],
            ],
            $node->getContents()->getHttpMethodRouteDataMap()
        );
        $this->assertNull($node->getContents()->getDefaultRouteData());
        $this->assertFalse($node->getContents()->hasDefaultRouteData());

        $node->getContents()->addRoute($this->mockRoute(['PATCH'], ['second_route']), [0 => 'param']);

        $this->assertSame(['GET', 'POST', 'PATCH'], $node->getContents()->getAllowedHttpMethods());
        $this->assertEquals(
            [
                [['GET', 'POST'], new MatchedRouteData([], ['first_route'])],
                [['PATCH'], new MatchedRouteData([0 => 'param'], ['second_route'])],
            ],
            $node->getContents()->getHttpMethodRouteDataMap()
        );
        $this->assertNull($node->getContents()->getDefaultRouteData());
        $this->assertFalse($node->getContents()->hasDefaultRouteData());

        $node->getContents()->addRoute($this->mockRoute(Route::ALLOW_ANY_METHOD, ['third_route']), []);

        $this->assertSame(Route::ALLOW_ANY_METHOD, $node->getContents()->getAllowedHttpMethods());
        $this->assertEquals(
            [
                [['GET', 'POST'], new MatchedRouteData([], ['first_route'])],
                [['PATCH'], new MatchedRouteData([0 => 'param'], ['second_route'])],
            ],
            $node->getContents()->getHttpMethodRouteDataMap()
        );
        $this->assertEquals(new MatchedRouteData([], ['third_route']), $node->getContents()->getDefaultRouteData());
        $this->assertTrue($node->getContents()->hasDefaultRouteData());
    }

    public function testThrowsExceptionForEmptyMatchers()
    {
        $this->setExpectedException(RapidRouteException::getType());
        new RouteTreeNode([], new ChildrenNodeCollection([]));
    }

    /**
     * @param string|null $hash
     * @param array       $parameterKeys
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|SegmentMatcher
     */
    protected function getMatcherMock($hash = null, array $parameterKeys = [])
    {
        $mock = $this->getMockForAbstractClass(SegmentMatcher::getType(), [$parameterKeys], '', true, true, true, ['getHash']);

        $mock->expects($this->any())
            ->method('getHash')
            ->willReturn($hash);

        return $mock;
    }

    /**
     * @param array $httpMethods
     * @param       $data
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|Route
     */
    protected function mockRoute(array $httpMethods = null, $data, array $segments = [])
    {
        $mock = $this->getMockBuilder(Route::getType())
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())
            ->method('getHttpMethods')
            ->willReturn($httpMethods);

        $mock->expects($this->any())
            ->method('allowsAnyHttpMethod')
            ->willReturn($httpMethods === null);

        $mock->expects($this->any())
            ->method('getData')
            ->willReturn($data);

        $mock->expects($this->any())
            ->method('getSegments')
            ->willReturn($segments);

        return $mock;
    }
}