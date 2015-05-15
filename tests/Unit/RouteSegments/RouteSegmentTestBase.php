<?php

namespace RapidRoute\Tests\Unit\RouteSegments;

use RapidRoute\Compilation\Matchers\SegmentMatcher;
use RapidRoute\RouteSegments\RouteSegment;
use RapidRoute\Tests\RapidRouteTest;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class RouteSegmentTestBase extends RapidRouteTest
{
    /**
     * @var RouteSegment
     */
    protected $segment;

    public function setUp()
    {
        $this->segment = $this->buildRouteSegment();
    }

    /**
     * @return RouteSegment
     */
    abstract protected function buildRouteSegment();

    /**
     * @return RouteSegment
     */
    abstract protected function buildDifferentRouteSegment();

    public function testMatcher()
    {
        foreach([$this->segment, $this->buildDifferentRouteSegment()] as $segment) {
            $parameters = [];
            /** @var RouteSegment $segment */
            $this->assertBuildsCorrectMatcher($segment, $segment->getMatcher($parameters), $parameters);
        }
    }

    abstract protected function assertBuildsCorrectMatcher(RouteSegment $segment, SegmentMatcher $matcher, array $parameters);
}