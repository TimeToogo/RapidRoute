<?php

namespace RapidRoute\Tests\Integration;

use RapidRoute\MatchResult;
use RapidRoute\RouteCollection;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TwoParameterRouterIssue1Test extends RouterTestBase
{
    protected function compiledFileName()
    {
        return 'bug.issue1.two-parameter-route';
    }

    protected function definitions(RouteCollection $routes)
    {
        $routes->get('/', 'News@index');
        $routes->get('/news/page/{slug}', 'News@article');
        $routes->get('/news/feed', 'News@article');
        $routes->get('/news/{num}/{slug}', 'News@article'); // bug here
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
            ['GET', '/',  MatchResult::found('News@index', [])],
            ['GET', '/news/page/post',  MatchResult::found('News@article', ['slug' => 'post'])],
            ['GET', '/news/feed',  MatchResult::found('News@article', [])],
            ['GET', '/news/123/hello',  MatchResult::found('News@article', ['num' => '123', 'slug' => 'hello'])],
        ];
    }
}