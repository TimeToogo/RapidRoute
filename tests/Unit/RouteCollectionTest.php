<?php

namespace RapidRoute\Tests\Unit;

use RapidRoute\InvalidRoutePatternException;
use RapidRoute\Pattern;
use RapidRoute\Route;
use RapidRoute\RouteCollection;
use RapidRoute\RouteSegments\RouteSegment;
use RapidRoute\Tests\Helpers\DummyRoute;
use RapidRoute\Tests\Helpers\DummyRouteParser;
use RapidRoute\Tests\RapidRouteTest;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RouteCollectionTest extends RapidRouteTest
{
    /**
     * @var DummyRouteParser
     */
    protected $parserMock;

    /**
     * @var RouteCollection
     */
    protected $collection;

    public function setUp()
    {
        $this->parserMock = new DummyRouteParser();
        $this->collection = new RouteCollection($this->parserMock);

        $this->parserMock->patternSegmentsMap['/pattern'] = [$this->getMockForAbstractClass(RouteSegment::getType())];
    }

    public function testAddCreatesAddsNewRouteWithSuppliedData()
    {
        $this->collection->add(['GET'], ['/pattern', 'param' => 'regex'], ['route_data']);

        $this->assertCount(1, $this->collection->asArray());
        $this->assertEquals(
            new Route(['GET'], $this->parserMock->patternSegmentsMap['/pattern'], ['route_data']),
            $this->collection->asArray()[0]
        );
    }

    public function testAddValidPatternFormats()
    {
        $this->collection->add(['GET'], '/pattern', ['route_data']);
        $this->collection->add(['POST'], ['/pattern' , 'param' => 'value'], ['route_data']);
        $this->collection->add(['POST'], ['/pattern' , 'param' => 'value'], ['route_data']);
    }

    public function testAddInvalidPattern()
    {
        $invalidPatterns = [
            null,
            [],
            ['foo' => '/pattern'],
            [1 => '/pattern'],
            [false],
            [1, 'param' => 'regex'],
        ];

        foreach($invalidPatterns as $pattern) {
            try {
                $this->collection->add(['GET'], $pattern, ['route_data']);
                $this->fail('Expected exception');
            } catch(InvalidRoutePatternException $e) {}
        }
    }

    public function testAddPassesPatternAndConditionsToRouteParser()
    {
        $this->collection->add(['GET'], ['/pattern', 'param' => 'regex'], ['data']);

        $this->assertSame('/pattern', $this->parserMock->lastParsed[0]);
        $this->assertSame(['param' => 'regex'], $this->parserMock->lastParsed[1]);
    }

    public function testAddPassesGlobalParameterConditionsToRouteParser()
    {
        $this->collection->param('global_param', Pattern::APLHA);
        $this->collection->params([
            'another_global_param' => Pattern::DIGITS,
            'removed_param' => Pattern::ANY,
        ]);
        $this->collection->removeParam('removed_param');

        $this->collection->add(['GET'], ['/pattern', 'param' => 'regex'], ['data']);

        $this->assertSame('/pattern', $this->parserMock->lastParsed[0]);
        $this->assertSame([
            'param'                => 'regex',
            'global_param'         => Pattern::APLHA,
            'another_global_param' => Pattern::DIGITS,
        ], $this->parserMock->lastParsed[1]);
    }

    public function testAddRouteAddsTheRouteToTheCollection()
    {
        $mock = new DummyRoute();
        $this->collection->addRoute($mock);

        $this->assertCount(1, $this->collection->asArray());
        $this->assertSame($mock, $this->collection->asArray()[0]);
    }

    public function methodMap()
    {
        return [
            ['get', 'GET'],
            ['head', 'HEAD'],
            ['post', 'POST'],
            ['put', 'PUT'],
            ['patch', 'PATCH'],
            ['delete', 'DELETE'],
        ];
    }

    /**
     * @dataProvider methodMap
     */
    public function testHttpMethodShortcuts($collectionMethod, $httpMethod)
    {
        $this->collection->{$collectionMethod}(['/pattern', 'param' => 'regex'], ['route_data']);

        $this->assertCount(1, $this->collection->asArray());
        $this->assertEquals(
            new Route([$httpMethod], $this->parserMock->patternSegmentsMap['/pattern'], ['route_data']),
            $this->collection->asArray()[0]
        );
    }

    public function testAnyHttpMethodShortcut()
    {
        $this->collection->any(['/pattern', 'param' => 'regex'], ['route_data']);

        $this->assertCount(1, $this->collection->asArray());
        $this->assertEquals(
            new Route(Route::ALLOW_ANY_METHOD, $this->parserMock->patternSegmentsMap['/pattern'], ['route_data']),
            $this->collection->asArray()[0]
        );
    }
}