<?php

namespace RapidRoute\Tests\Unit;

use RapidRoute\InvalidRoutePatternException;
use RapidRoute\Pattern;
use RapidRoute\RapidRouteException;
use RapidRoute\RouteParser;
use RapidRoute\RouteSegments\ParameterSegment;
use RapidRoute\RouteSegments\StaticSegment;
use RapidRoute\Tests\RapidRouteTest;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RouteParserTest extends RapidRouteTest
{
    /**
     * @var RouteParser
     */
    protected $parser;

    public function setUp()
    {
        $this->parser = new RouteParser();
    }

    protected function assertParsesTo(array $expectedSegments, $pattern, array $conditions)
    {
        $this->assertEquals($expectedSegments, $this->parser->parse($pattern, $conditions));
    }

    public function parsingExamples()
    {
        return [
            [
                // Empty route
                '',
                [],
                []
            ],
            [
                // Empty route
                '/',
                [],
                [new StaticSegment('')]
            ],
            [
                '/user',
                [],
                [new StaticSegment('user')]
            ],
            [
                '/user/',
                [],
                [new StaticSegment('user'), new StaticSegment('')]
            ],
            [
                '/user/profile',
                [],
                [new StaticSegment('user'), new StaticSegment('profile')]
            ],
            [
                '/{parameter}',
                [],
                [ParameterSegment::from('parameter', RouteParser::DEFAULT_PARAMETER_PATTERN)]
            ],
            [
                '/{param}',
                ['param' => Pattern::APLHA_NUM],
                [ParameterSegment::from('param', Pattern::APLHA_NUM)]
            ],
            [
                '/user/{id}/profile/{type}',
                ['id' => Pattern::DIGITS, 'type' => Pattern::APLHA_LOWER],
                [
                    new StaticSegment('user'),
                    ParameterSegment::from('id', Pattern::DIGITS),
                    new StaticSegment('profile'),
                    ParameterSegment::from('type', Pattern::APLHA_LOWER),
                ]
            ],
            [
                '/prefix{param}',
                ['param' => Pattern::APLHA_NUM],
                [new ParameterSegment(['param'], '/^prefix(' . Pattern::APLHA_NUM . ')$/')]
            ],
            [
                '/{param}suffix',
                ['param' => Pattern::APLHA_NUM],
                [new ParameterSegment(['param'], '/^(' . Pattern::APLHA_NUM . ')suffix$/')]
            ],
            [
                '/abc{param1}:{param2}',
                ['param1' => Pattern::ANY, 'param2' => Pattern::APLHA],
                [new ParameterSegment(['param1', 'param2'], '/^abc(' . Pattern::ANY . ')\:(' . Pattern::APLHA . ')$/')]
            ],
            [
                '/shop/{category}:{product}/buy/quantity:{quantity}',
                ['category' => Pattern::APLHA, 'product' => Pattern::APLHA, 'quantity' => Pattern::DIGITS],
                [
                    new StaticSegment('shop'),
                    new ParameterSegment(['category', 'product'], '/^(' . Pattern::APLHA . ')\:(' . Pattern::APLHA . ')$/'),
                    new StaticSegment('buy'),
                    new ParameterSegment(['quantity'], '/^quantity\:(' . Pattern::DIGITS . ')$/'),
                ]
            ],
        ];
    }

    /**
     * @dataProvider parsingExamples
     */
    public function testRouteParsing($pattern, array $conditions = [], array $expectedSegments)
    {
        $this->assertParsesTo($expectedSegments, $pattern, $conditions);
    }

    public function invalidParsingExamples()
    {
        return [
            [
                'abc',
                [],
                InvalidRoutePatternException::getType(),
            ],
            [
                '/test/{a/bc}',
                [],
                InvalidRoutePatternException::getType(),
            ],
            [
                '/test/{a{bc}',
                [],
                InvalidRoutePatternException::getType(),
            ],
            [
                '/test/{abc}}',
                [],
                InvalidRoutePatternException::getType(),
            ],
            [
                '/test/{a{bc}}',
                [],
                InvalidRoutePatternException::getType(),
            ],
        ];
    }

    /**
     * @dataProvider invalidParsingExamples
     */
    public function testInvalidRouteParsing(
        $pattern,
        array $conditions = [],
        $expectedExceptionType = null,
        $messagePattern = null
    ) {
        $this->setExpectedExceptionRegExp(
            $expectedExceptionType ?: RapidRouteException::getType(),
            $messagePattern ?: '/.*/'
        );

        $this->parser->parse($pattern, $conditions);
    }
}