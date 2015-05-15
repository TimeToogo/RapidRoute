<?php

namespace RapidRoute\Tests\Integration;

use RapidRoute\Pattern;
use RapidRoute\RouteCollection;
use RapidRoute\RouterResult;

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

        $routes->get(['/alpha/{param}', 'param' => Pattern::APLHA], ['name' => 'alpha']);

        $routes->get(['/alpha_low/{param}', 'param' => Pattern::APLHA_LOWER], ['name' => 'alpha_low']);

        $routes->get(['/alpha_up/{param}', 'param' => Pattern::APLHA_UPPER], ['name' => 'alpha_up']);

        $routes->get(['/alpha_num/{param}', 'param' => Pattern::APLHA_NUM], ['name' => 'alpha_num']);

        $routes->get(['/alpha_num_dash/{param}', 'param' => Pattern::APLHA_NUM_DASH], ['name' => 'alpha_num_dash']);

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
            ['GET', '/digits/', RouterResult::notFound()],
            ['GET', '/digits/abc', RouterResult::notFound()],
            ['GET', '/digits/-1', RouterResult::notFound()],
            ['GET', '/digits/1.0', RouterResult::notFound()],
            ['GET', '/digits/123470!', RouterResult::notFound()],
            ['GET', '/digits/1234-70', RouterResult::notFound()],
            ['GET', '/digits/0', RouterResult::found(['name' => 'digits'], ['param' => '0'])],
            ['GET', '/digits/00001', RouterResult::found(['name' => 'digits'], ['param' => '00001'])],
            ['GET', '/digits/1234', RouterResult::found(['name' => 'digits'], ['param' => '1234'])],

            ['GET', '/alpha/', RouterResult::notFound()],
            ['GET', '/alpha/1', RouterResult::notFound()],
            ['GET', '/alpha/abc:def', RouterResult::notFound()],
            ['GET', '/alpha/abc-dEf', RouterResult::notFound()],
            ['GET', '/alpha/|', RouterResult::notFound()],
            ['GET', '/alpha/a',  RouterResult::found(['name' => 'alpha'], ['param' => 'a'])],
            ['GET', '/alpha/A',  RouterResult::found(['name' => 'alpha'], ['param' => 'A'])],
            ['GET', '/alpha/abcdefqwerty',  RouterResult::found(['name' => 'alpha'], ['param' => 'abcdefqwerty'])],
            ['GET', '/alpha/abcAdefCqwBerty',  RouterResult::found(['name' => 'alpha'], ['param' => 'abcAdefCqwBerty'])],

            ['GET', '/alpha_low/', RouterResult::notFound()],
            ['GET', '/alpha_low/1', RouterResult::notFound()],
            ['GET', '/alpha_low/abc:def', RouterResult::notFound()],
            ['GET', '/alpha_low/abc-dEf', RouterResult::notFound()],
            ['GET', '/alpha_low/A',  RouterResult::notFound()],
            ['GET', '/alpha_low/abcAdefCqwBerty',  RouterResult::notFound()],
            ['GET', '/alpha_low/a',  RouterResult::found(['name' => 'alpha_low'], ['param' => 'a'])],
            ['GET', '/alpha_low/abcdefqwerty',  RouterResult::found(['name' => 'alpha_low'], ['param' => 'abcdefqwerty'])],

            ['GET', '/alpha_up/', RouterResult::notFound()],
            ['GET', '/alpha_up/1', RouterResult::notFound()],
            ['GET', '/alpha_up/abc:def', RouterResult::notFound()],
            ['GET', '/alpha_up/abc-dEf', RouterResult::notFound()],
            ['GET', '/alpha_up/a',  RouterResult::notFound()],
            ['GET', '/alpha_up/abcAdefCqwBerty',  RouterResult::notFound()],
            ['GET', '/alpha_up/A',  RouterResult::found(['name' => 'alpha_up'], ['param' => 'A'])],
            ['GET', '/alpha_up/AWBCDEFG',  RouterResult::found(['name' => 'alpha_up'], ['param' => 'AWBCDEFG'])],

            ['GET', '/alpha_num/', RouterResult::notFound()],
            ['GET', '/alpha_num/abc:def', RouterResult::notFound()],
            ['GET', '/alpha_num/|', RouterResult::notFound()],
            ['GET', '/alpha_num/1',  RouterResult::found(['name' => 'alpha_num'], ['param' => '1'])],
            ['GET', '/alpha_num/a',  RouterResult::found(['name' => 'alpha_num'], ['param' => 'a'])],
            ['GET', '/alpha_num/A',  RouterResult::found(['name' => 'alpha_num'], ['param' => 'A'])],
            ['GET', '/alpha_num/abcAdefCqwBerty',  RouterResult::found(['name' => 'alpha_num'], ['param' => 'abcAdefCqwBerty'])],
            ['GET', '/alpha_num/abcdef123qerty8',  RouterResult::found(['name' => 'alpha_num'], ['param' => 'abcdef123qerty8'])],

            ['GET', '/alpha_num_dash/', RouterResult::notFound()],
            ['GET', '/alpha_num_dash/abc:def', RouterResult::notFound()],
            ['GET', '/alpha_num_dash/|', RouterResult::notFound()],
            ['GET', '/alpha_num_dash/1',  RouterResult::found(['name' => 'alpha_num_dash'], ['param' => '1'])],
            ['GET', '/alpha_num_dash/a',  RouterResult::found(['name' => 'alpha_num_dash'], ['param' => 'a'])],
            ['GET', '/alpha_num_dash/A',  RouterResult::found(['name' => 'alpha_num_dash'], ['param' => 'A'])],
            ['GET', '/alpha_num_dash/abcAdefCqwBerty',  RouterResult::found(['name' => 'alpha_num_dash'], ['param' => 'abcAdefCqwBerty'])],
            ['GET', '/alpha_num_dash/abcdef123qerty8',  RouterResult::found(['name' => 'alpha_num_dash'], ['param' => 'abcdef123qerty8'])],
            ['GET', '/alpha_num_dash/ab2--3231c-dEf', RouterResult::found(['name' => 'alpha_num_dash'], ['param' => 'ab2--3231c-dEf'])],

            ['GET', '/any/', RouterResult::notFound()],
            ['GET', '/any/1',  RouterResult::found(['name' => 'any'], ['param' => '1'])],
            ['GET', '/any/a',  RouterResult::found(['name' => 'any'], ['param' => 'a'])],
            ['GET', '/any/A',  RouterResult::found(['name' => 'any'], ['param' => 'A'])],
            ['GET', '/any/abcAdefCqwBerty',  RouterResult::found(['name' => 'any'], ['param' => 'abcAdefCqwBerty'])],
            ['GET', '/any/abcdef123qerty8',  RouterResult::found(['name' => 'any'], ['param' => 'abcdef123qerty8'])],
            ['GET', '/any/ab2--3231c-dEf', RouterResult::found(['name' => 'any'], ['param' => 'ab2--3231c-dEf'])],
            ['GET', '/any/abvdGF&##HGJD$%6%~jnk];[', RouterResult::found(['name' => 'any'], ['param' => 'abvdGF&##HGJD$%6%~jnk];['])],

            ['GET', '/custom/', RouterResult::notFound()],
            ['GET', '/custom/abc:def', RouterResult::notFound()],
            ['GET', '/custom/|', RouterResult::notFound()],
            ['GET', '/custom/!', RouterResult::notFound()],
            ['GET', '/custom/!!', RouterResult::notFound()],
            ['GET', '/custom/abcde', RouterResult::notFound()],
            ['GET', '/custom/!!!a', RouterResult::notFound()],
            ['GET', '/custom/!!!!!!', RouterResult::notFound()],
            ['GET', '/custom/!!!', RouterResult::found(['name' => 'custom'], ['param' => '!!!'])],
            ['GET', '/custom/!!!!', RouterResult::found(['name' => 'custom'], ['param' => '!!!!'])],
            ['GET', '/custom/!!!!!', RouterResult::found(['name' => 'custom'], ['param' => '!!!!!'])],
        ];
    }
}