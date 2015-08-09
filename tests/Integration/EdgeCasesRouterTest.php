<?php

namespace RapidRoute\Tests\Integration;

use RapidRoute\Pattern;
use RapidRoute\RapidRouteException;
use RapidRoute\RouteCollection;
use RapidRoute\MatchResult;
use RapidRoute\Tests\Helpers\CustomClass;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EdgeCasesRouterTest extends RouterTestBase
{
    protected function compiledFileName()
    {
        return 'edge-cases';
    }

    protected function definitions(RouteCollection $routes)
    {
        $routes->get('/abc/{param}/bar', ['name' => 'middle-param']);
        $routes->get(['/123/{param}/bar', 'param' => '.*'], ['name' => 'all-middle-param']);

        $routes->get(['/object'], (object)['name' => 'object-data']);
        $routes->get(['/custom-class'], new CustomClass());
        $routes->get(['/string'], 'some-string');
        $routes->get(['/int'], 123);
        $routes->get(['/bool'], false);

        // Order of precedence:
        //  - static route
        //  - static without HTTP method
        //  - dynamic routes
        //  - dynamic without HTTP method
        $routes->get('/http/method/fallback', ['name' => 'http-method-fallback.static']);
        $routes->any('/http/method/fallback', ['name' => 'http-method-fallback.static.fallback']);
        $routes->post('/http/method/{parameter}', ['name' => 'http-method-fallback.dynamic']);
        $routes->any('/http/method/{parameter}', ['name' => 'http-method-fallback.dynamic.fallback']);

        // Should detect allowed HTTP methods
        $routes->get('/allowed-methods/foo', ['name' => 'allowed-methods.static']);
        $routes->post('/allowed-methods/{parameter}', ['name' => 'allowed-methods.dynamic']);

        $routes->get(['/complex-methods/{param}/foo/bar', 'param' => Pattern::DIGITS], ['name' => 'complex-methods.first']);
        $routes->post(['/complex-methods/{param}/foo/{param2}', 'param' => Pattern::ALPHA_NUM], ['name' => 'complex-methods.second']);
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
            ['GET', '/abc//bar', MatchResult::notFound()],
            ['GET', '/abc/a/bar', MatchResult::found(['name' => 'middle-param'], ['param' => 'a'])],

            ['GET', '/123//bar', MatchResult::found(['name' => 'all-middle-param'], ['param' => ''])],
            ['GET', '/123/a/bar', MatchResult::found(['name' => 'all-middle-param'], ['param' => 'a'])],

            ['GET', '/object', MatchResult::found((object)['name' => 'object-data'], [])],
            ['GET', '/custom-class', MatchResult::found(new CustomClass(), [])],
            ['GET', '/string', MatchResult::found('some-string', [])],
            ['GET', '/int', MatchResult::found(123, [])],
            ['GET', '/bool', MatchResult::found(false, [])],

            ['GET', '/http/method/fallback', MatchResult::found(['name' => 'http-method-fallback.static'], [])],
            ['POST', '/http/method/fallback', MatchResult::found(['name' => 'http-method-fallback.static.fallback'], [])],
            ['DELETE', '/http/method/fallback', MatchResult::found(['name' => 'http-method-fallback.static.fallback'], [])],
            ['DELETE', '/http/method/some-other', MatchResult::found(['name' => 'http-method-fallback.dynamic.fallback'], ['parameter' => 'some-other'])],

            ['GET', '/allowed-methods/foo', MatchResult::found(['name' => 'allowed-methods.static'], [])],
            ['GET', '/allowed-methods/bar', MatchResult::httpMethodNotAllowed(['POST'])],
            ['POST', '/allowed-methods/bar', MatchResult::found(['name' => 'allowed-methods.dynamic'], ['parameter' => 'bar'])],
            ['DELETE', '/allowed-methods/foo', MatchResult::httpMethodNotAllowed(['GET', 'HEAD', 'POST'])],

            ['GET', '/complex-methods/123/foo/bar', MatchResult::found(['name' => 'complex-methods.first'], ['param' => '123'])],
            ['POST', '/complex-methods/123/foo/bar', MatchResult::found(['name' => 'complex-methods.second'], ['param' => '123', 'param2' => 'bar'])],
            ['PATCH', '/complex-methods/123/foo/bar', MatchResult::httpMethodNotAllowed(['GET', 'HEAD', 'POST'])],
            ['PATCH', '/complex-methods/abc123/foo/bar', MatchResult::httpMethodNotAllowed(['POST'])],
            ['PATCH', '/complex-methods/123/foo/abc', MatchResult::httpMethodNotAllowed(['POST'])],
        ];
    }

    public function testThrowsExceptionForNonEmptyRouteWithoutPrecedingSlash()
    {
        $this->setExpectedExceptionRegExp(RapidRouteException::getType(), '#prefixed with \'\\/\'#');
        $this->router->match('GET', 'abc/a/bar');
    }
}