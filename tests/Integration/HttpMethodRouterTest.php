<?php

namespace RapidRoute\Tests\Integration;

use RapidRoute\RouteCollection;
use RapidRoute\RouterResult;

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
     *      RouterResult::found(['route_data'], ['id' => '1'])
     * ]
     *
     * @return array[]
     */
    public function routerMatchingExamples()
    {
        return [
            ['GET', '/', RouterResult::found(['name' => 'home.get'], [])],
            ['HEAD', '/', RouterResult::found(['name' => 'home.get'], [])],
            ['POST', '/', RouterResult::found(['name' => 'home.post-or-patch'], [])],
            ['PATCH', '/', RouterResult::found(['name' => 'home.post-or-patch'], [])],
            ['DELETE', '/', RouterResult::found(['name' => 'home.delete'], [])],

            ['BOGUS', '/', RouterResult::found(['name' => 'home.fallback'], [])],
            ['', '/', RouterResult::found(['name' => 'home.fallback'], [])],
            ['get', '/', RouterResult::found(['name' => 'home.fallback'], [])],
            ['Get', '/', RouterResult::found(['name' => 'home.fallback'], [])],
            ['Patch', '/', RouterResult::found(['name' => 'home.fallback'], [])],
            ['!@@!', '/', RouterResult::found(['name' => 'home.fallback'], [])],

            ['GET', '', RouterResult::notFound()],
        ];
    }
}