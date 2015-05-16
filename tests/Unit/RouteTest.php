<?php

namespace RapidRoute\Tests\Unit;

use RapidRoute\InvalidRouteDataException;
use RapidRoute\RapidRouteException;
use RapidRoute\Route;
use RapidRoute\RouteSegments\ParameterSegment;
use RapidRoute\RouteSegments\RouteSegment;
use RapidRoute\RouteSegments\StaticSegment;
use RapidRoute\Tests\RapidRouteTest;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RouteTest extends RapidRouteTest
{
    public function testConstructorDoesNotThrowExceptionForValidData()
    {
        new Route(['GET'], [], ['data']);
        new Route(Route::ALLOW_ANY_METHOD, [], (object)['data']);
    }

    public function testConvertsHttpMethodToUppercase()
    {
        $route = new Route(['post', 'pAtch'], [], ['data']);

        $this->assertSame(['POST', 'PATCH'], $route->getHttpMethods());
    }

    public function testAllowsHeadRequestIfGetIsSpecified()
    {
        $route = new Route(['get'], [], ['data']);

        $this->assertSame(['GET', 'HEAD'], $route->getHttpMethods());
    }

    public function testWillNotDuplicatedHeadRequest()
    {
        $route = new Route(['GET', 'POST'], [], ['data']);

        $this->assertSame(['GET', 'POST', 'HEAD'], $route->getHttpMethods());
    }

    public function testConstructorThrowsExceptionForNoHttpMethods()
    {
        $this->setExpectedException(RapidRouteException::getType());
        new Route([], [], ['data']);
    }

    public function testConstructorThrowsExceptionForInvalidRouteData()
    {
        $this->setExpectedException(InvalidRouteDataException::getType());
        new Route(['GET'], [], null);
    }

    public function testGetters()
    {
        $requestSegment = $this->getMockForAbstractClass(RouteSegment::getType());

        $route = new Route(
            ['GET', 'HEAD'],
            [$requestSegment],
            ['data']
        );

        $this->assertFalse($route->allowsAnyHttpMethod());
        $this->assertSame(['GET', 'HEAD'], $route->getHttpMethods());
        $this->assertSame([$requestSegment], $route->getSegments());
        $this->assertSame(['data'], $route->getData());
    }

    public function testIsStatic()
    {
        $staticSegment = $this->getMock(StaticSegment::getType(), [], [], '', false);
        $parameterSegment = $this->getMock(ParameterSegment::getType(), [], [], '', false);

        $route = new Route(['GET'], [$staticSegment], ['data']);
        $this->assertTrue($route->isStatic());

        $route = new Route(['GET'], [$staticSegment, $staticSegment, $staticSegment], ['data']);
        $this->assertTrue($route->isStatic());

        $route = new Route(['GET'], [$parameterSegment], ['data']);
        $this->assertFalse($route->isStatic());

        $route = new Route(['GET'], [$staticSegment, $parameterSegment, $staticSegment], ['data']);
        $this->assertFalse($route->isStatic());
    }
}