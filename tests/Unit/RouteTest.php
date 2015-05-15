<?php

namespace RapidRoute\Tests\Unit;

use RapidRoute\InvalidRouteDataException;
use RapidRoute\RapidRouteException;
use RapidRoute\Route;
use RapidRoute\RouteSegments\RouteSegment;
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
        $route = new Route(['get', 'pAtch'], [], ['data']);

        $this->assertSame(['GET', 'PATCH'], $route->getHttpMethods());
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
}