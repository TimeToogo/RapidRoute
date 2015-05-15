<?php

namespace RapidRoute\Tests\Unit\RouteSegments;

use RapidRoute\Compilation\Matchers\SegmentMatcher;
use RapidRoute\Compilation\Matchers\StaticMatcher;
use RapidRoute\RapidRouteException;
use RapidRoute\RouteSegments\RouteSegment;
use RapidRoute\RouteSegments\StaticSegment;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class StaticSegmentTest extends RouteSegmentTestBase
{
    /**
     * @return RouteSegment
     */
    protected function buildRouteSegment()
    {
        return new StaticSegment('one');
    }

    /**
     * @return RouteSegment
     */
    protected function buildDifferentRouteSegment()
    {
        return new StaticSegment('two');
    }

    protected function assertBuildsCorrectMatcher(RouteSegment $segment, SegmentMatcher $matcher, array $parameters)
    {
        /** @var StaticSegment $segment */
        /** @var StaticMatcher $matcher */
        $this->assertInstanceOf(StaticMatcher::getType(), $matcher);
        $this->assertSame($segment->getValue(), $matcher->segment);
        $this->assertSame([], $parameters);
    }

    public function testCannotContainSlash()
    {
        $this->setExpectedException(RapidRouteException::getType());
        new StaticSegment('abc/foo');
    }
}