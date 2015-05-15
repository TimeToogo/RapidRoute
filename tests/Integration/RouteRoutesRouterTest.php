<?php

namespace RapidRoute\Tests\Integration;

use RapidRoute\RouteCollection;
use RapidRoute\RouterResult;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RouteRoutesRouterTest extends RouterTestBase
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
     *      RouterResult::found(['route_data'], ['id' => '1'])
     * ]
     *
     * @return array[]
     */
    public function routerMatchingExamples()
    {
        return [
            ['GET', '', RouterResult::found(['name' => 'root'], [])],
            ['GET', '/', RouterResult::found(['name' => 'root-slash'], [])],
            ['GET', '/a', RouterResult::notFound()],
        ];
    }
}