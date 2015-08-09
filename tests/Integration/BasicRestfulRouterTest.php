<?php

namespace RapidRoute\Tests\Integration;

use RapidRoute\Pattern;
use RapidRoute\RouteCollection;
use RapidRoute\MatchResult;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class BasicRestfulRouterTest extends RouterTestBase
{
    protected function compiledFileName()
    {
        return 'rest.basic';
    }

    protected function definitions(RouteCollection $routes)
    {
        $routes->param('id', Pattern::DIGITS);

        $routes->get('/user', ['name' => 'user.index']);

        $routes->get('/user/create', ['name' => 'user.create']);

        $routes->post('/user', ['name' => 'user.save']);

        $routes->get('/user/{id}', ['name' => 'user.show']);

        $routes->get('/user/{id}/edit', ['name' => 'user.edit']);

        $routes->put('/user/{id}', ['name' => 'user.update']);

        $routes->delete('/user/{id}', ['name' => 'user.delete']);
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
            ['GET', '', MatchResult::notFound()],
            ['GET', '/', MatchResult::notFound()],
            ['GET', '/users', MatchResult::notFound()],
            ['GET', '/users/1', MatchResult::notFound()],
            ['GET', '/user/', MatchResult::notFound()],

            ['GET', '/user', MatchResult::found(['name' => 'user.index'], [])],
            ['PUT', '/user', MatchResult::httpMethodNotAllowed(['GET', 'HEAD','POST'])],

            ['GET', '/user/create', MatchResult::found(['name' => 'user.create'], [])],
            ['DELETE', '/user', MatchResult::httpMethodNotAllowed(['GET', 'HEAD', 'POST'])],
            ['PATCH', '/user', MatchResult::httpMethodNotAllowed(['GET', 'HEAD', 'POST'])],

            ['POST', '/user', MatchResult::found(['name' => 'user.save'], [])],
            ['DELETE', '/user', MatchResult::httpMethodNotAllowed(['GET', 'HEAD', 'POST'])],

            ['GET', '/user/1', MatchResult::found(['name' => 'user.show'], ['id' => '1'])],
            ['GET', '/user/123', MatchResult::found(['name' => 'user.show'], ['id' => '123'])],
            ['HEAD', '/user/123', MatchResult::found(['name' => 'user.show'], ['id' => '123'])],
            ['POST', '/user/123', MatchResult::httpMethodNotAllowed(['GET', 'HEAD', 'PUT', 'DELETE'])],
            ['GET', '/user/abc', MatchResult::notFound()],
            ['GET', '/user/-1', MatchResult::notFound()],
            ['GET', '/user/1.0', MatchResult::notFound()],
            ['GET', '/user/1/', MatchResult::notFound()],

            ['GET', '/user/0/edit', MatchResult::found(['name' => 'user.edit'], ['id' => '0'])],
            ['GET', '/user/123/edit', MatchResult::found(['name' => 'user.edit'], ['id' => '123'])],
            ['PATCH', '/user/1/edit', MatchResult::httpMethodNotAllowed(['GET', 'HEAD'])],
            ['GET', '/user//edit', MatchResult::notFound()],
            ['GET', '/user/1/edit/', MatchResult::notFound()],
            ['GET', '/user/abc/edit', MatchResult::notFound()],
            ['GET', '/user/-1/edit', MatchResult::notFound()],

            ['PUT', '/user/1', MatchResult::found(['name' => 'user.update'], ['id' => '1'])],
            ['PATCH', '/user/1', MatchResult::httpMethodNotAllowed(['GET', 'HEAD', 'PUT', 'DELETE'])],
            ['PATCH', '/user/123321', MatchResult::httpMethodNotAllowed(['GET', 'HEAD', 'PUT', 'DELETE'])],
        ];
    }
}