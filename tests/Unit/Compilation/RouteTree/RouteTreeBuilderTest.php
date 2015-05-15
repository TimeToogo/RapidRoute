<?php

namespace RapidRoute\Tests\Unit\Compilation\RouteTree;

use RapidRoute\Compilation\Matchers\RegexMatcher;
use RapidRoute\Compilation\Matchers\StaticMatcher;
use RapidRoute\Compilation\RouteTree\ChildrenNodeCollection;
use RapidRoute\Compilation\RouteTree\MatchedRouteData;
use RapidRoute\Compilation\RouteTree\MatchedRouteDataMap;
use RapidRoute\Compilation\RouteTree\RouteTreeBuilder;
use RapidRoute\Compilation\RouteTree\RouteTreeNode;
use RapidRoute\Pattern;
use RapidRoute\Route;
use RapidRoute\RouteCollection;
use RapidRoute\RouteSegments\ParameterSegment;
use RapidRoute\RouteSegments\StaticSegment;
use RapidRoute\Tests\RapidRouteTest;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RouteTreeBuilderTest extends RapidRouteTest
{
    public function routeTreeBuilderCases()
    {
        return [
            [
                'routes'    => [new Route(null, [], ['home'])],
                'rootRoute' => new MatchedRouteDataMap([], new MatchedRouteData([], ['home'])),
                'tree'      => [],
            ],
            [
                'routes'    => [new Route(null, [new StaticSegment('')], ['route'])],
                'rootRoute' => null,
                'tree'      => [
                    1 => new ChildrenNodeCollection([
                        (new StaticMatcher(''))->getHash() => new RouteTreeNode(
                            [0 => new StaticMatcher('')],
                            new MatchedRouteDataMap([], new MatchedRouteData([], ['route']))
                        ),
                    ])
                ],
            ],
            [
                'routes'    => [
                    new Route(null, [], ['home']),
                    new Route(null, [new StaticSegment('main')], ['main.root']),
                    new Route(['GET'], [new StaticSegment('main'), new StaticSegment('place')], ['main.place-get']),
                    new Route(['POST'], [new StaticSegment('main'), new StaticSegment('place')], ['main.place-post']),
                    new Route(null, [new StaticSegment('main'), new StaticSegment('thing')], ['main.thing']),
                    new Route(null, [new StaticSegment('main'), new StaticSegment('thing'), new StaticSegment('abc')], ['main.thing.abc']),
                    new Route(null, [new StaticSegment('user'), ParameterSegment::from('name', Pattern::ANY)], ['user.show']),
                    new Route(null, [new StaticSegment('user'), ParameterSegment::from('name', Pattern::ANY), new StaticSegment('edit')], ['user.edit']),
                    new Route(null, [new StaticSegment('user'), new StaticSegment('create')], ['user.create']),
                ],
                'rootRoute' => new MatchedRouteDataMap([], new MatchedRouteData([], ['home'])),
                'tree'      => [
                    1 => new ChildrenNodeCollection([
                        (new StaticMatcher('main'))->getHash() => new RouteTreeNode(
                            [0 => new StaticMatcher('main')],
                            new MatchedRouteDataMap([], new MatchedRouteData([], ['main.root']))
                        )
                    ]),
                    2 => new ChildrenNodeCollection([
                        (new StaticMatcher('main'))->getHash() => new RouteTreeNode([0 => new StaticMatcher('main')], new ChildrenNodeCollection([

                            (new StaticMatcher('place'))->getHash() => new RouteTreeNode(
                                [1 => new StaticMatcher('place')],
                                new MatchedRouteDataMap([
                                    [['GET'], new MatchedRouteData([], ['main.place-get'])],
                                    [['POST'], new MatchedRouteData([], ['main.place-post'])],
                                ])
                            ),

                            (new StaticMatcher('thing'))->getHash() => new RouteTreeNode(
                                [1 => new StaticMatcher('thing')],
                                new MatchedRouteDataMap([], new MatchedRouteData([], ['main.thing']))
                            ),
                        ])),

                        (new StaticMatcher('user'))->getHash() => new RouteTreeNode([0 => new StaticMatcher('user')], new ChildrenNodeCollection([

                            RegexMatcher::from(Pattern::ANY, 0)->getHash() => new RouteTreeNode(
                                [1 => RegexMatcher::from(Pattern::ANY, 0)],
                                new MatchedRouteDataMap([], new MatchedRouteData([0 => 'name'], ['user.show']))
                            ),

                            (new StaticMatcher('create'))->getHash() => new RouteTreeNode(
                                [1 => new StaticMatcher('create')],
                                new MatchedRouteDataMap([], new MatchedRouteData([], ['user.create']))
                            ),
                        ])),
                    ]),
                    3 => new ChildrenNodeCollection([
                        (new StaticMatcher('main'))->getHash() => new RouteTreeNode([0 => new StaticMatcher('main')], new ChildrenNodeCollection([

                            (new StaticMatcher('thing'))->getHash() => new RouteTreeNode([1 => new StaticMatcher('thing')], new ChildrenNodeCollection([

                                (new StaticMatcher('abc'))->getHash() => new RouteTreeNode(
                                    [2 => new StaticMatcher('abc')],
                                    new MatchedRouteDataMap([], new MatchedRouteData([], ['main.thing.abc']))
                                ),
                            ]))
                        ])),

                        (new StaticMatcher('user'))->getHash() => new RouteTreeNode([0 => new StaticMatcher('user')], new ChildrenNodeCollection([

                            RegexMatcher::from(Pattern::ANY, 0)->getHash() => new RouteTreeNode([1 => RegexMatcher::from(Pattern::ANY, 0)], new ChildrenNodeCollection([
                                (new StaticMatcher('edit'))->getHash() => new RouteTreeNode(
                                    [2 => new StaticMatcher('edit')],
                                    new MatchedRouteDataMap([], new MatchedRouteData([0 => 'name'], ['user.edit']))
                                ),
                            ]))
                        ])),
                    ]),
                ],
            ],
        ];
    }

    /**
     * @dataProvider routeTreeBuilderCases
     */
    public function testRouteTreeBuilder($routes, $rootRoute, $segmentDepthNodesMap)
    {
        $builder = new RouteTreeBuilder();

        $routeCollection = $this->getMockBuilder(RouteCollection::getType())
            ->disableOriginalConstructor()
            ->getMock();

        $routeCollection->expects($this->once())
            ->method('asArray')
            ->willReturn($routes);

        $tree = $builder->build($routeCollection);

        $this->assertSame($rootRoute !== null, $tree->hasRootRoute());
        $this->assertEquals($rootRoute, $tree->getRootRouteData());
        $this->assertEquals($segmentDepthNodesMap, $tree->getSegmentDepthNodesMap());
    }
}