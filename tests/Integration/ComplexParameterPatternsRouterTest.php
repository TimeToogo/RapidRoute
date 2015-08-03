<?php

namespace RapidRoute\Tests\Integration;

use RapidRoute\Pattern;
use RapidRoute\RouteCollection;
use RapidRoute\MatchResult;

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
            'alpha' => Pattern::ALPHA,
            'exclaim' => '!{3,5}'
        ], ['name' => 'filtered-multi-param']);

        $routes->get(['/f/{name}-is-awesome-at-{thing}', 'name' => '[A-Z]?[a-z]+', 'thing' => Pattern::ALPHA_LOWER], ['name' => 'sentence-multi-param']);
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
            ['GET', '/a/', MatchResult::notFound()],
            ['GET', '/a/abc', MatchResult::notFound()],
            ['GET', '/a/prefix:', MatchResult::notFound()],
            ['GET', '/a/prefix:abc/', MatchResult::notFound()],
            ['GET', '/a/prefix:abc', MatchResult::found(['name' => 'prefix'], ['param' => 'abc'])],
            ['GET', '/a/prefix:aqwery12345', MatchResult::found(['name' => 'prefix'], ['param' => 'aqwery12345'])],

            ['GET', '/b/', MatchResult::notFound()],
            ['GET', '/b/abc', MatchResult::notFound()],
            ['GET', '/b/:suffix', MatchResult::notFound()],
            ['GET', '/b/abc:suffix/', MatchResult::notFound()],
            ['GET', '/b/abc:suffix', MatchResult::found(['name' => 'suffix'], ['param' => 'abc'])],
            ['GET', '/b/aqwery12345:suffix', MatchResult::found(['name' => 'suffix'], ['param' => 'aqwery12345'])],

            ['GET', '/c/', MatchResult::notFound()],
            ['GET', '/c/abc', MatchResult::notFound()],
            ['GET', '/c/:suffix', MatchResult::notFound()],
            ['GET', '/c/prefix:', MatchResult::notFound()],
            ['GET', '/c/prefix::suffix', MatchResult::notFound()],
            ['GET', '/c/prefix:abc:suffix/', MatchResult::notFound()],
            ['GET', '/c/prefix:abc:suffix', MatchResult::found(['name' => 'prefix-and-suffix'], ['param' => 'abc'])],
            ['GET', '/c/prefix:aqwery12345:suffix', MatchResult::found(['name' => 'prefix-and-suffix'], ['param' => 'aqwery12345'])],

            ['GET', '/d/', MatchResult::notFound()],
            ['GET', '/d/abc', MatchResult::notFound()],
            ['GET', '/d/-:', MatchResult::notFound()],
            ['GET', '/d/abc-', MatchResult::notFound()],
            ['GET', '/d/abc-:', MatchResult::notFound()],
            ['GET', '/d/abc-:abc', MatchResult::notFound()],
            ['GET', '/d/abc-abc:abc', MatchResult::found(['name' => 'multi-param'], ['param1' => 'abc', 'param2' => 'abc', 'param3' => 'abc'])],
            ['GET', '/d/abc-def:ghi', MatchResult::found(['name' => 'multi-param'], ['param1' => 'abc', 'param2' => 'def', 'param3' => 'ghi'])],

            ['GET', '/e/', MatchResult::notFound()],
            ['GET', '/e/abc', MatchResult::notFound()],
            ['GET', '/e/-:', MatchResult::notFound()],
            ['GET', '/e/abc-', MatchResult::notFound()],
            ['GET', '/e/abc-:', MatchResult::notFound()],
            ['GET', '/e/abc-:abc', MatchResult::notFound()],
            ['GET', '/e/alpha-abc:!!!', MatchResult::notFound()],
            ['GET', '/e/123-abc123:!!!', MatchResult::notFound()],
            ['GET', '/e/123-abc:!!', MatchResult::notFound()],
            ['GET', '/e/123-abc:!!!', MatchResult::found(['name' => 'filtered-multi-param'], ['digits' => '123', 'alpha' => 'abc', 'exclaim' => '!!!'])],
            ['GET', '/e/42-AaQqWw:!!!!!', MatchResult::found(['name' => 'filtered-multi-param'], ['digits' => '42', 'alpha' => 'AaQqWw', 'exclaim' => '!!!!!'])],

            ['GET', '/f/', MatchResult::notFound()],
            ['GET', '/f/abc', MatchResult::notFound()],
            ['GET', '/f/-is-awesome-at-soccer', MatchResult::notFound()],
            ['GET', '/f/jeff-is-awesome-at-', MatchResult::notFound()],
            ['GET', '/f/jeff-is-awesome-at-soCCer', MatchResult::notFound()],
            ['GET', '/f/jEff-is-awesome-at-soccer', MatchResult::notFound()],
            ['GET', '/f/123-is-awesome-at-soccer', MatchResult::notFound()],
            ['GET', '/f/jeff-is-awesome-at-abc123', MatchResult::notFound()],
            ['GET', '/f/jeff-is-awesome-at-soccer',  MatchResult::found(['name' => 'sentence-multi-param'], ['name' => 'jeff', 'thing' => 'soccer'])],
            ['GET', '/f/Jeff-is-awesome-at-soccer',  MatchResult::found(['name' => 'sentence-multi-param'], ['name' => 'Jeff', 'thing' => 'soccer'])],
            ['GET', '/f/Ben-is-awesome-at-tennis',  MatchResult::found(['name' => 'sentence-multi-param'], ['name' => 'Ben', 'thing' => 'tennis'])],
        ];
    }
}