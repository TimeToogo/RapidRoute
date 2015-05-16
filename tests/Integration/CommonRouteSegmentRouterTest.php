<?php

namespace RapidRoute\Tests\Integration;

use RapidRoute\Pattern;
use RapidRoute\RouteCollection;
use RapidRoute\MatchResult;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CommonRouteSegmentRouterTest extends RouterTestBase
{
    protected function compiledFileName()
    {
        return 'common-route-segments';
    }

    protected function definitions(RouteCollection $routes)
    {
        $routes->param('p2', Pattern::APLHA);

        $routes->get('/route1/{p1}/{p2}/{p3}', ['name' => 'route1']);
        $routes->get('/route2/{p1}/{p2}/{p3}', ['name' => 'route2']);
        $routes->get('/route3/{p1}/{p2}/{p3}', ['name' => 'route3']);
        $routes->get('/route4/{p1}/{p2}/{p3}', ['name' => 'route4']);
        $routes->get('/route5/{p_1}/{p_2}/{p_3}', ['name' => 'route5']);
        $routes->get('/route6/{p_1}/{p2}/{p_3}', ['name' => 'route6']);
    }

    /**
     * Should return each case in the format:
     *
     * [
     *      'GET',
     *      '/user/1',
     *      RouterResult::found(['route_data'], ['id' => '1'])
     * ]
     *
     * @return array[]
     */
    public function routerMatchingExamples()
    {
        return [
            ['GET', '/route1/a/b/c', MatchResult::found(['name' => 'route1'], ['p1' => 'a', 'p2' => 'b', 'p3' => 'c'])],
            ['GET', '/route2/a/b/c', MatchResult::found(['name' => 'route2'], ['p1' => 'a', 'p2' => 'b', 'p3' => 'c'])],
            ['GET', '/route3/a/b/c', MatchResult::found(['name' => 'route3'], ['p1' => 'a', 'p2' => 'b', 'p3' => 'c'])],
            ['GET', '/route4/a/b/c', MatchResult::found(['name' => 'route4'], ['p1' => 'a', 'p2' => 'b', 'p3' => 'c'])],
            ['GET', '/route5/a/b/c', MatchResult::found(['name' => 'route5'], ['p_1' => 'a', 'p_2' => 'b', 'p_3' => 'c'])],
            ['GET', '/route6/a/1/c', MatchResult::notFound()],
            ['GET', '/route6/a/b/c', MatchResult::found(['name' => 'route6'], ['p_1' => 'a', 'p2' => 'b', 'p_3' => 'c'])],
            ['GET', '/route1/a/123/c', MatchResult::notFound()],
        ];
    }
}