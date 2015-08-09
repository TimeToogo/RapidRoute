<?php

namespace RapidRoute\Tests\Integration;

use RapidRoute\RouteCollection;
use RapidRoute\MatchResult;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class HttpMethodRouterTest extends RouterTestBase
{
    protected function compiledFileName()
    {
        return 'http-methods';
    }

    protected function definitions(RouteCollection $routes)
    {
        $routes->get('/', ['name' => 'home.get']);

        $routes->add(['POST', 'PATCH'], '/', ['name' => 'home.post-or-patch']);

        $routes->delete('/', ['name' => 'home.delete']);

        $routes->any('/', ['name' => 'home.fallback']);
    }

    /**
     * Should return each case in the format:
     *
     * [
     *      'GET',
     *      '/user/1',
     *      MatchResult::found(['route_data'], ['id' => '1'])
     * ]
     *
     * @return array[]
     */
    public function routerMatchingExamples()
    {
        return [
            ['GET', '/', MatchResult::found(['name' => 'home.get'], [])],
            ['HEAD', '/', MatchResult::found(['name' => 'home.get'], [])],
            ['POST', '/', MatchResult::found(['name' => 'home.post-or-patch'], [])],
            ['PATCH', '/', MatchResult::found(['name' => 'home.post-or-patch'], [])],
            ['DELETE', '/', MatchResult::found(['name' => 'home.delete'], [])],

            ['BOGUS', '/', MatchResult::found(['name' => 'home.fallback'], [])],
            ['', '/', MatchResult::found(['name' => 'home.fallback'], [])],
            ['get', '/', MatchResult::found(['name' => 'home.fallback'], [])],
            ['Get', '/', MatchResult::found(['name' => 'home.fallback'], [])],
            ['Patch', '/', MatchResult::found(['name' => 'home.fallback'], [])],
            ['!@@!', '/', MatchResult::found(['name' => 'home.fallback'], [])],

            ['GET', '', MatchResult::notFound()],
        ];
    }
}