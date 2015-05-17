<?php

namespace RapidRoute\Tests\Unit\Compilation;

use RapidRoute\Compilation\Matchers\CompoundMatcher;
use RapidRoute\Compilation\Matchers\RegexMatcher;
use RapidRoute\Compilation\Matchers\StaticMatcher;
use RapidRoute\Pattern;
use RapidRoute\Tests\RapidRouteTest;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SegmentMatcherTest extends RapidRouteTest
{
    public function testCompoundSegmentMatcher()
    {
        $matcher1 = new CompoundMatcher([new StaticMatcher('a'), new StaticMatcher('b', 0)]);
        $matcher2 = new CompoundMatcher([new StaticMatcher('a', 0), new StaticMatcher('c', 1)]);

        $this->assertSame([0], $matcher1->getParameterKeys());
        $this->assertInternalType('string', $matcher1->getHash());
        $this->assertNotEquals($matcher2->getHash(), $matcher1->getHash());

        $this->assertSame('$segment === \'a\' && $segment === \'b\'', $matcher1->getConditionExpression('$segment', 0));
        $this->assertSame([0 => '$segment'], $matcher1->getMatchedParameterExpressions('$segment', 0));
        $this->assertSame('$segment === \'a\' && $segment === \'c\'', $matcher2->getConditionExpression('$segment', 0));
        $this->assertSame([0 => '$segment', 1 => '$segment'], $matcher2->getMatchedParameterExpressions('$segment', 0));
    }

    public function testRegexMatcherFromFactoryMethod()
    {
        $matcher = RegexMatcher::from(Pattern::ANY, 12);

        $this->assertSame('/^(.+)$/', $matcher->regex);
        $this->assertSame([12], $matcher->getParameterKeys());
    }
}