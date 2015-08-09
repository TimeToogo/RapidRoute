<?php

namespace RapidRoute\Tests\Integration;

use RapidRoute\MatchResult;
use RapidRoute\RouteCollection;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CommonParameterRouterTest extends RouterTestBase
{
    protected function compiledFileName()
    {
        return 'bug.common-parameter-route';
    }

    protected function definitions(RouteCollection $routes)
    {
        $routes->get('/archives/events/{category}/{event}', ['name' => 'archives.event.show']);
        $routes->get('/auth/password/reset/{token}', ['name' => 'auth.password.reset']);
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
            ['GET', '/archives/events/category/event',  MatchResult::found(['name' => 'archives.event.show'], ['category' => 'category', 'event' => 'event'])],
            ['GET', '/auth/password/reset/abc123',  MatchResult::found(['name' => 'auth.password.reset'], ['token' => 'abc123'])],
        ];
    }
}