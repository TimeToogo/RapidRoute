<?php

namespace RapidRoute\Tests\Integration;

use RapidRoute\MatchResult;
use RapidRoute\RouteCollection;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RootParameterRouterTest extends RouterTestBase
{
    protected function compiledFileName()
    {
        return 'root-parameter';
    }

    protected function definitions(RouteCollection $routes)
    {
        $routes->get('/{param}', 'root-param');
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
            ['GET', '/abc', MatchResult::found('root-param', ['param' => 'abc'])],
        ];
    }
}