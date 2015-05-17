<?php

namespace RapidRoute\Tests\Unit;

use RapidRoute\MatchResult;
use RapidRoute\RapidRouteException;
use RapidRoute\Tests\RapidRouteTest;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MatchResultTest extends RapidRouteTest
{
    public function found()
    {
        return [
            [MatchResult::found(['some_data'], ['param' => 'value'])],
            [MatchResult::fromArray([MatchResult::FOUND, ['some_data'], ['param' => 'value']])]
        ];
    }

    /**
     * @dataProvider found
     */
    public function testFound(MatchResult $result)
    {
        $this->assertSame($result->getStatus(), MatchResult::FOUND);

        $this->assertTrue($result->isFound());
        $this->assertFalse($result->isNotFound());
        $this->assertFalse($result->isDisallowedHttpMethod());

        $this->assertNull($result->getAllowedHttpMethods());
        $this->assertSame(['param' => 'value'], $result->getParameters());
        $this->assertSame(['some_data'], $result->getRouteData());

        $this->assertSame([MatchResult::FOUND, ['some_data'], ['param' => 'value']], $result->toArray());
        $this->assertEquals($result, MatchResult::found(['some_data'], ['param' => 'value']));
    }

    public function notFound()
    {
        return [
            [MatchResult::notFound()],
            [MatchResult::fromArray([MatchResult::NOT_FOUND])]
        ];
    }

    /**
     * @dataProvider notFound
     */
    public function testNotFound(MatchResult $result)
    {
        $this->assertSame($result->getStatus(), MatchResult::NOT_FOUND);

        $this->assertTrue($result->isNotFound());
        $this->assertFalse($result->isFound());
        $this->assertFalse($result->isDisallowedHttpMethod());

        $this->assertNull($result->getAllowedHttpMethods());
        $this->assertNull($result->getParameters());
        $this->assertNull($result->getRouteData());

        $this->assertSame([MatchResult::NOT_FOUND], $result->toArray());
        $this->assertEquals($result, MatchResult::notFound());
    }


    public function httpMethodNotAllowed()
    {
        return [
            [MatchResult::httpMethodNotAllowed(['PUT', 'DELETE'])],
            [MatchResult::fromArray([MatchResult::HTTP_METHOD_NOT_ALLOWED, ['PUT', 'DELETE']])]
        ];
    }

    /**
     * @dataProvider httpMethodNotAllowed
     */
    public function testHttpMethodNotAllowed(MatchResult $result)
    {
        $this->assertSame($result->getStatus(), MatchResult::HTTP_METHOD_NOT_ALLOWED);

        $this->assertTrue($result->isDisallowedHttpMethod());
        $this->assertFalse($result->isFound());
        $this->assertFalse($result->isNotFound());

        $this->assertSame(['PUT', 'DELETE'], $result->getAllowedHttpMethods());
        $this->assertNull($result->getParameters());
        $this->assertNull($result->getRouteData());

        $this->assertSame([MatchResult::HTTP_METHOD_NOT_ALLOWED, ['PUT', 'DELETE']], $result->toArray());
        $this->assertEquals($result, MatchResult::httpMethodNotAllowed(['PUT', 'DELETE']));
    }

    public function testFromArrayThrowsForInvalidStatus()
    {
        $this->setExpectedExceptionRegExp(RapidRouteException::getType(), '/123/');

        MatchResult::fromArray([123, ['some_data'], ['param' => 'value']]);
    }
}