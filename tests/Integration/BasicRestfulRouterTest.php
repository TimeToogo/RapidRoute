<?php

namespace RapidRoute\Tests\Integration;

use RapidRoute\Pattern;
use RapidRoute\RouteCollection;
use RapidRoute\RouterResult;

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
     *      RouterResult::found(['route_data'], ['id' => '1'])
     * ]
     *
     * @return array[]
     */
    public function routerMatchingExamples()
    {
        return [
            ['GET', '', RouterResult::notFound()],
            ['GET', '/', RouterResult::notFound()],
            ['GET', '/users', RouterResult::notFound()],
            ['GET', '/users/1', RouterResult::notFound()],
            ['GET', '/user/', RouterResult::notFound()],

            ['GET', '/user', RouterResult::found(['name' => 'user.index'], [])],
            ['PUT', '/user', RouterResult::httpMethodNotAllowed(['GET', 'HEAD','POST'])],

            ['GET', '/user/create', RouterResult::found(['name' => 'user.create'], [])],
            ['DELETE', '/user', RouterResult::httpMethodNotAllowed(['GET', 'HEAD', 'POST'])],
            ['PATCH', '/user', RouterResult::httpMethodNotAllowed(['GET', 'HEAD', 'POST'])],

            ['POST', '/user', RouterResult::found(['name' => 'user.save'], [])],
            ['DELETE', '/user', RouterResult::httpMethodNotAllowed(['GET', 'HEAD', 'POST'])],

            ['GET', '/user/1', RouterResult::found(['name' => 'user.show'], ['id' => '1'])],
            ['GET', '/user/123', RouterResult::found(['name' => 'user.show'], ['id' => '123'])],
            ['HEAD', '/user/123', RouterResult::found(['name' => 'user.show'], ['id' => '123'])],
            ['POST', '/user/123', RouterResult::httpMethodNotAllowed(['GET', 'HEAD', 'PUT', 'DELETE'])],
            ['GET', '/user/abc', RouterResult::notFound()],
            ['GET', '/user/-1', RouterResult::notFound()],
            ['GET', '/user/1.0', RouterResult::notFound()],
            ['GET', '/user/1/', RouterResult::notFound()],

            ['GET', '/user/0/edit', RouterResult::found(['name' => 'user.edit'], ['id' => '0'])],
            ['GET', '/user/123/edit', RouterResult::found(['name' => 'user.edit'], ['id' => '123'])],
            ['PATCH', '/user/1/edit', RouterResult::httpMethodNotAllowed(['GET', 'HEAD'])],
            ['GET', '/user//edit', RouterResult::notFound()],
            ['GET', '/user/1/edit/', RouterResult::notFound()],
            ['GET', '/user/abc/edit', RouterResult::notFound()],
            ['GET', '/user/-1/edit', RouterResult::notFound()],

            ['PUT', '/user/1', RouterResult::found(['name' => 'user.update'], ['id' => '1'])],
            ['PATCH', '/user/1', RouterResult::httpMethodNotAllowed(['GET', 'HEAD', 'PUT', 'DELETE'])],
            ['PATCH', '/user/123321', RouterResult::httpMethodNotAllowed(['GET', 'HEAD', 'PUT', 'DELETE'])],
        ];
    }
}