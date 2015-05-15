<?php

namespace RapidRoute\Tests\Unit\RouteSegments;

use RapidRoute\Compilation\Matchers\RegexMatcher;
use RapidRoute\Compilation\Matchers\SegmentMatcher;
use RapidRoute\Pattern;
use RapidRoute\RouteSegments\ParameterSegment;
use RapidRoute\RouteSegments\RouteSegment;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ParameterSegmentTest extends RouteSegmentTestBase
{
    /**
     * @return RouteSegment
     */
    protected function buildRouteSegment()
    {
        return ParameterSegment::from('param', Pattern::ANY);
    }

    /**
     * @return RouteSegment
     */
    protected function buildDifferentRouteSegment()
    {
        return ParameterSegment::from('param', Pattern::APLHA_NUM);
    }

    protected function assertBuildsCorrectMatcher(RouteSegment $segment, SegmentMatcher $matcher, array $parameters)
    {
        /** @var ParameterSegment $segment */
        /** @var RegexMatcher $matcher */
        $this->assertInstanceOf(RegexMatcher::getType(), $matcher);
        $this->assertSame($segment->getRegex(), $matcher->regex);
        $this->assertSame([0 => 'param'], $parameters);
    }
}