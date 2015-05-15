<?php

namespace RapidRoute\Tests\Integration;

use RapidRoute\Pattern;
use RapidRoute\RapidRouteException;
use RapidRoute\RouteCollection;
use RapidRoute\RouterResult;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InvalidMatchRouterTest extends RouterTestBase
{
    protected function compiledFileName()
    {
        return 'invalid-match-test';
    }

    protected function definitions(RouteCollection $routes)
    {
        $routes->get('/', ['name' => 'home']);
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
            ['GET', '/', RouterResult::found(['name' => 'home'], [])],
        ];
    }

    public function testRouteWithoutPrefixedSlashThrows()
    {
        $this->setExpectedExceptionRegExp(RapidRouteException::getType(), '#/#');

        $this->router->match('GET', 'abc');
    }
}