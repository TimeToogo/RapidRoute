<?php

namespace RapidRoute\Tests\Integration;

use RapidRoute\Pattern;
use RapidRoute\RouteCollection;
use RapidRoute\MatchResult;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class BasicParameterPatternsRouterTest extends RouterTestBase
{
    protected function compiledFileName()
    {
        return 'patterns.basic';
    }

    protected function definitions(RouteCollection $routes)
    {
        $routes->get(['/digits/{param}', 'param' => Pattern::DIGITS], ['name' => 'digits']);

        $routes->get(['/alpha/{param}', 'param' => Pattern::ALPHA], ['name' => 'alpha']);

        $routes->get(['/alpha_low/{param}', 'param' => Pattern::ALPHA_LOWER], ['name' => 'alpha_low']);

        $routes->get(['/alpha_up/{param}', 'param' => Pattern::ALPHA_UPPER], ['name' => 'alpha_up']);

        $routes->get(['/alpha_num/{param}', 'param' => Pattern::ALPHA_NUM], ['name' => 'alpha_num']);

        $routes->get(['/alpha_num_dash/{param}', 'param' => Pattern::ALPHA_NUM_DASH], ['name' => 'alpha_num_dash']);

        $routes->get(['/any/{param}', 'param' => Pattern::ANY], ['name' => 'any']);

        $routes->get(['/custom/{param}', 'param' => '[\!]{3,5}'], ['name' => 'custom']);
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
            ['GET', '/digits/', MatchResult::notFound()],
            ['GET', '/digits/abc', MatchResult::notFound()],
            ['GET', '/digits/-1', MatchResult::notFound()],
            ['GET', '/digits/1.0', MatchResult::notFound()],
            ['GET', '/digits/123470!', MatchResult::notFound()],
            ['GET', '/digits/1234-70', MatchResult::notFound()],
            ['GET', '/digits/0', MatchResult::found(['name' => 'digits'], ['param' => '0'])],
            ['GET', '/digits/00001', MatchResult::found(['name' => 'digits'], ['param' => '00001'])],
            ['GET', '/digits/1234', MatchResult::found(['name' => 'digits'], ['param' => '1234'])],

            ['GET', '/alpha/', MatchResult::notFound()],
            ['GET', '/alpha/1', MatchResult::notFound()],
            ['GET', '/alpha/abc:def', MatchResult::notFound()],
            ['GET', '/alpha/abc-dEf', MatchResult::notFound()],
            ['GET', '/alpha/|', MatchResult::notFound()],
            ['GET', '/alpha/a',  MatchResult::found(['name' => 'alpha'], ['param' => 'a'])],
            ['GET', '/alpha/A',  MatchResult::found(['name' => 'alpha'], ['param' => 'A'])],
            ['GET', '/alpha/abcdefqwerty',  MatchResult::found(['name' => 'alpha'], ['param' => 'abcdefqwerty'])],
            ['GET', '/alpha/abcAdefCqwBerty',  MatchResult::found(['name' => 'alpha'], ['param' => 'abcAdefCqwBerty'])],

            ['GET', '/alpha_low/', MatchResult::notFound()],
            ['GET', '/alpha_low/1', MatchResult::notFound()],
            ['GET', '/alpha_low/abc:def', MatchResult::notFound()],
            ['GET', '/alpha_low/abc-dEf', MatchResult::notFound()],
            ['GET', '/alpha_low/A',  MatchResult::notFound()],
            ['GET', '/alpha_low/abcAdefCqwBerty',  MatchResult::notFound()],
            ['GET', '/alpha_low/a',  MatchResult::found(['name' => 'alpha_low'], ['param' => 'a'])],
            ['GET', '/alpha_low/abcdefqwerty',  MatchResult::found(['name' => 'alpha_low'], ['param' => 'abcdefqwerty'])],

            ['GET', '/alpha_up/', MatchResult::notFound()],
            ['GET', '/alpha_up/1', MatchResult::notFound()],
            ['GET', '/alpha_up/abc:def', MatchResult::notFound()],
            ['GET', '/alpha_up/abc-dEf', MatchResult::notFound()],
            ['GET', '/alpha_up/a',  MatchResult::notFound()],
            ['GET', '/alpha_up/abcAdefCqwBerty',  MatchResult::notFound()],
            ['GET', '/alpha_up/A',  MatchResult::found(['name' => 'alpha_up'], ['param' => 'A'])],
            ['GET', '/alpha_up/AWBCDEFG',  MatchResult::found(['name' => 'alpha_up'], ['param' => 'AWBCDEFG'])],

            ['GET', '/alpha_num/', MatchResult::notFound()],
            ['GET', '/alpha_num/abc:def', MatchResult::notFound()],
            ['GET', '/alpha_num/|', MatchResult::notFound()],
            ['GET', '/alpha_num/1',  MatchResult::found(['name' => 'alpha_num'], ['param' => '1'])],
            ['GET', '/alpha_num/a',  MatchResult::found(['name' => 'alpha_num'], ['param' => 'a'])],
            ['GET', '/alpha_num/A',  MatchResult::found(['name' => 'alpha_num'], ['param' => 'A'])],
            ['GET', '/alpha_num/abcAdefCqwBerty',  MatchResult::found(['name' => 'alpha_num'], ['param' => 'abcAdefCqwBerty'])],
            ['GET', '/alpha_num/abcdef123qerty8',  MatchResult::found(['name' => 'alpha_num'], ['param' => 'abcdef123qerty8'])],

            ['GET', '/alpha_num_dash/', MatchResult::notFound()],
            ['GET', '/alpha_num_dash/abc:def', MatchResult::notFound()],
            ['GET', '/alpha_num_dash/|', MatchResult::notFound()],
            ['GET', '/alpha_num_dash/1',  MatchResult::found(['name' => 'alpha_num_dash'], ['param' => '1'])],
            ['GET', '/alpha_num_dash/a',  MatchResult::found(['name' => 'alpha_num_dash'], ['param' => 'a'])],
            ['GET', '/alpha_num_dash/A',  MatchResult::found(['name' => 'alpha_num_dash'], ['param' => 'A'])],
            ['GET', '/alpha_num_dash/abcAdefCqwBerty',  MatchResult::found(['name' => 'alpha_num_dash'], ['param' => 'abcAdefCqwBerty'])],
            ['GET', '/alpha_num_dash/abcdef123qerty8',  MatchResult::found(['name' => 'alpha_num_dash'], ['param' => 'abcdef123qerty8'])],
            ['GET', '/alpha_num_dash/ab2--3231c-dEf', MatchResult::found(['name' => 'alpha_num_dash'], ['param' => 'ab2--3231c-dEf'])],

            ['GET', '/any/', MatchResult::notFound()],
            ['GET', '/any/1',  MatchResult::found(['name' => 'any'], ['param' => '1'])],
            ['GET', '/any/a',  MatchResult::found(['name' => 'any'], ['param' => 'a'])],
            ['GET', '/any/A',  MatchResult::found(['name' => 'any'], ['param' => 'A'])],
            ['GET', '/any/abcAdefCqwBerty',  MatchResult::found(['name' => 'any'], ['param' => 'abcAdefCqwBerty'])],
            ['GET', '/any/abcdef123qerty8',  MatchResult::found(['name' => 'any'], ['param' => 'abcdef123qerty8'])],
            ['GET', '/any/ab2--3231c-dEf', MatchResult::found(['name' => 'any'], ['param' => 'ab2--3231c-dEf'])],
            ['GET', '/any/abvdGF&##HGJD$%6%~jnk];[', MatchResult::found(['name' => 'any'], ['param' => 'abvdGF&##HGJD$%6%~jnk];['])],

            ['GET', '/custom/', MatchResult::notFound()],
            ['GET', '/custom/abc:def', MatchResult::notFound()],
            ['GET', '/custom/|', MatchResult::notFound()],
            ['GET', '/custom/!', MatchResult::notFound()],
            ['GET', '/custom/!!', MatchResult::notFound()],
            ['GET', '/custom/abcde', MatchResult::notFound()],
            ['GET', '/custom/!!!a', MatchResult::notFound()],
            ['GET', '/custom/!!!!!!', MatchResult::notFound()],
            ['GET', '/custom/!!!', MatchResult::found(['name' => 'custom'], ['param' => '!!!'])],
            ['GET', '/custom/!!!!', MatchResult::found(['name' => 'custom'], ['param' => '!!!!'])],
            ['GET', '/custom/!!!!!', MatchResult::found(['name' => 'custom'], ['param' => '!!!!!'])],
        ];
    }
}