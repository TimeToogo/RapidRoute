<?php

namespace RapidRoute\Tests\Integration;

use RapidRoute\RouteCollection;
use RapidRoute\MatchResult;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RootRoutesRouterTest extends RouterTestBase
{
    protected function compiledFileName()
    {
        return 'root-routes';
    }

    protected function definitions(RouteCollection $routes)
    {
        $routes->get('', ['name' => 'root']);

        $routes->get('/', ['name' => 'root-slash']);
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
            ['GET', '', MatchResult::found(['name' => 'root'], [])],
            ['GET', '/', MatchResult::found(['name' => 'root-slash'], [])],
            ['GET', '/a', MatchResult::notFound()],
        ];
    }
}