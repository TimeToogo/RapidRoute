<?php

namespace RapidRoute\Tests\Integration;

use RapidRoute\Pattern;
use RapidRoute\RouteCollection;
use RapidRoute\RouterResult;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ComplexParameterPatternsRouterTest extends RouterTestBase
{
    protected function compiledFileName()
    {
        return 'patterns.complex';
    }

    protected function definitions(RouteCollection $routes)
    {
        $routes->get(['/a/prefix:{param}'], ['name' => 'prefix']);

        $routes->get(['/b/{param}:suffix'], ['name' => 'suffix']);

        $routes->get(['/c/prefix:{param}:suffix'], ['name' => 'prefix-and-suffix']);

        $routes->get(['/d/{param1}-{param2}:{param3}'], ['name' => 'multi-param']);

        $routes->get([
            '/e/{digits}-{alpha}:{exclaim}',
            'digits' => Pattern::DIGITS,
            'alpha' => Pattern::APLHA,
            'exclaim' => '!{3,5}'
        ], ['name' => 'filtered-multi-param']);

        $routes->get(['/f/{name}-is-awesome-at-{thing}', 'name' => '[A-Z]?[a-z]+', 'thing' => Pattern::APLHA_LOWER], ['name' => 'sentence-multi-param']);
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
            ['GET', '/a/', RouterResult::notFound()],
            ['GET', '/a/abc', RouterResult::notFound()],
            ['GET', '/a/prefix:', RouterResult::notFound()],
            ['GET', '/a/prefix:abc/', RouterResult::notFound()],
            ['GET', '/a/prefix:abc', RouterResult::found(['name' => 'prefix'], ['param' => 'abc'])],
            ['GET', '/a/prefix:aqwery12345', RouterResult::found(['name' => 'prefix'], ['param' => 'aqwery12345'])],

            ['GET', '/b/', RouterResult::notFound()],
            ['GET', '/b/abc', RouterResult::notFound()],
            ['GET', '/b/:suffix', RouterResult::notFound()],
            ['GET', '/b/abc:suffix/', RouterResult::notFound()],
            ['GET', '/b/abc:suffix', RouterResult::found(['name' => 'suffix'], ['param' => 'abc'])],
            ['GET', '/b/aqwery12345:suffix', RouterResult::found(['name' => 'suffix'], ['param' => 'aqwery12345'])],

            ['GET', '/c/', RouterResult::notFound()],
            ['GET', '/c/abc', RouterResult::notFound()],
            ['GET', '/c/:suffix', RouterResult::notFound()],
            ['GET', '/c/prefix:', RouterResult::notFound()],
            ['GET', '/c/prefix::suffix', RouterResult::notFound()],
            ['GET', '/c/prefix:abc:suffix/', RouterResult::notFound()],
            ['GET', '/c/prefix:abc:suffix', RouterResult::found(['name' => 'prefix-and-suffix'], ['param' => 'abc'])],
            ['GET', '/c/prefix:aqwery12345:suffix', RouterResult::found(['name' => 'prefix-and-suffix'], ['param' => 'aqwery12345'])],

            ['GET', '/d/', RouterResult::notFound()],
            ['GET', '/d/abc', RouterResult::notFound()],
            ['GET', '/d/-:', RouterResult::notFound()],
            ['GET', '/d/abc-', RouterResult::notFound()],
            ['GET', '/d/abc-:', RouterResult::notFound()],
            ['GET', '/d/abc-:abc', RouterResult::notFound()],
            ['GET', '/d/abc-abc:abc', RouterResult::found(['name' => 'multi-param'], ['param1' => 'abc', 'param2' => 'abc', 'param3' => 'abc'])],
            ['GET', '/d/abc-def:ghi', RouterResult::found(['name' => 'multi-param'], ['param1' => 'abc', 'param2' => 'def', 'param3' => 'ghi'])],

            ['GET', '/e/', RouterResult::notFound()],
            ['GET', '/e/abc', RouterResult::notFound()],
            ['GET', '/e/-:', RouterResult::notFound()],
            ['GET', '/e/abc-', RouterResult::notFound()],
            ['GET', '/e/abc-:', RouterResult::notFound()],
            ['GET', '/e/abc-:abc', RouterResult::notFound()],
            ['GET', '/e/alpha-abc:!!!', RouterResult::notFound()],
            ['GET', '/e/123-abc123:!!!', RouterResult::notFound()],
            ['GET', '/e/123-abc:!!', RouterResult::notFound()],
            ['GET', '/e/123-abc:!!!', RouterResult::found(['name' => 'filtered-multi-param'], ['digits' => '123', 'alpha' => 'abc', 'exclaim' => '!!!'])],
            ['GET', '/e/42-AaQqWw:!!!!!', RouterResult::found(['name' => 'filtered-multi-param'], ['digits' => '42', 'alpha' => 'AaQqWw', 'exclaim' => '!!!!!'])],

            ['GET', '/f/', RouterResult::notFound()],
            ['GET', '/f/abc', RouterResult::notFound()],
            ['GET', '/f/-is-awesome-at-soccer', RouterResult::notFound()],
            ['GET', '/f/jeff-is-awesome-at-', RouterResult::notFound()],
            ['GET', '/f/jeff-is-awesome-at-soCCer', RouterResult::notFound()],
            ['GET', '/f/jEff-is-awesome-at-soccer', RouterResult::notFound()],
            ['GET', '/f/123-is-awesome-at-soccer', RouterResult::notFound()],
            ['GET', '/f/jeff-is-awesome-at-abc123', RouterResult::notFound()],
            ['GET', '/f/jeff-is-awesome-at-soccer',  RouterResult::found(['name' => 'sentence-multi-param'], ['name' => 'jeff', 'thing' => 'soccer'])],
            ['GET', '/f/Jeff-is-awesome-at-soccer',  RouterResult::found(['name' => 'sentence-multi-param'], ['name' => 'Jeff', 'thing' => 'soccer'])],
            ['GET', '/f/Ben-is-awesome-at-tennis',  RouterResult::found(['name' => 'sentence-multi-param'], ['name' => 'Ben', 'thing' => 'tennis'])],
        ];
    }
}