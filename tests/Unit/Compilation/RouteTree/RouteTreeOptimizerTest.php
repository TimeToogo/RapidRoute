<?php

namespace RapidRoute\Tests\Unit\Compilation\RouteTree;

use RapidRoute\Compilation\Matchers\AnyMatcher;
use RapidRoute\Compilation\Matchers\CompoundMatcher;
use RapidRoute\Compilation\Matchers\ExpressionMatcher;
use RapidRoute\Compilation\Matchers\RegexMatcher;
use RapidRoute\Compilation\Matchers\SegmentMatcher;
use RapidRoute\Compilation\Matchers\StaticMatcher;
use RapidRoute\Compilation\RouteTree\ChildrenNodeCollection;
use RapidRoute\Compilation\RouteTree\RouteTree;
use RapidRoute\Compilation\RouteTree\RouteTreeNode;
use RapidRoute\Compilation\RouteTree\RouteTreeOptimizer;
use RapidRoute\Pattern;
use RapidRoute\Tests\RapidRouteTest;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RouteTreeOptimizerTest extends RapidRouteTest
{
    /**
     * @var RouteTreeOptimizer
     */
    protected $optimizer;

    public function setUp()
    {
        $this->optimizer = new RouteTreeOptimizer();
    }

    public function optimizationCases()
    {
        return [
            [
                'original' => new RouteTree(null, [
                    1 => new ChildrenNodeCollection([
                        new RouteTreeNode([0 => new StaticMatcher('abc')], new ChildrenNodeCollection([]))
                    ])
                ]),
                'optimized' => new RouteTree(null, [
                    1 => new ChildrenNodeCollection([
                        new RouteTreeNode([0 => new StaticMatcher('abc')], new ChildrenNodeCollection([]))
                    ])
                ]),
            ],
            [
                'original' => new RouteTree(null, [
                    1 => new ChildrenNodeCollection([
                        new RouteTreeNode([0 => new StaticMatcher('abc')], new ChildrenNodeCollection([
                            new RouteTreeNode([1 => new StaticMatcher('deg')], new ChildrenNodeCollection([

                            ]))
                        ]))
                    ])
                ]),
                'optimized' => new RouteTree(null, [
                    1 => new ChildrenNodeCollection([
                        new RouteTreeNode([0 => new StaticMatcher('abc'), 1 => new StaticMatcher('deg')], new ChildrenNodeCollection([

                        ]))
                    ])
                ]),
            ],
            [
                'original' => new RouteTree(null, [
                    1 => new ChildrenNodeCollection([
                        new RouteTreeNode([0 => new StaticMatcher('abc')], new ChildrenNodeCollection([
                            new RouteTreeNode([1 => new StaticMatcher('def')], new ChildrenNodeCollection([
                                new RouteTreeNode([2 => new StaticMatcher('ghi')], new ChildrenNodeCollection([

                                ]))
                            ]))
                        ]))
                    ])
                ]),
                'optimized' => new RouteTree(null, [
                    1 => new ChildrenNodeCollection([
                        new RouteTreeNode([0 => new StaticMatcher('abc'), 1 => new StaticMatcher('def'), 2 => new StaticMatcher('ghi')], new ChildrenNodeCollection([

                        ]))
                    ])
                ]),
            ],
            [
                'original' => new RouteTree(null, [
                    1 => new ChildrenNodeCollection([
                        new RouteTreeNode([0 => new StaticMatcher('abc')], new ChildrenNodeCollection([
                            new RouteTreeNode([1 => new StaticMatcher('def'), 2 => new StaticMatcher('ghi')], new ChildrenNodeCollection([
                            ]))
                        ]))
                    ])
                ]),
                'optimized' => new RouteTree(null, [
                    1 => new ChildrenNodeCollection([
                        new RouteTreeNode([0 => new StaticMatcher('abc'), 1 => new StaticMatcher('def'), 2 => new StaticMatcher('ghi')], new ChildrenNodeCollection([

                        ]))
                    ])
                ]),
            ],
            [
                // Should optimize common regex patterns to more efficient PHP equivalents
                'original' => new RouteTree(null, [
                    1 => new ChildrenNodeCollection([
                        new RouteTreeNode(
                            [
                                RegexMatcher::from(Pattern::ANY, 0),
                                RegexMatcher::from(Pattern::DIGITS, 1),
                                RegexMatcher::from(Pattern::APLHA, 2),
                                RegexMatcher::from(Pattern::APLHA_LOWER, 3),
                                RegexMatcher::from(Pattern::APLHA_UPPER, 4),
                                RegexMatcher::from(Pattern::APLHA_NUM, 5),
                                RegexMatcher::from(Pattern::APLHA_NUM_DASH, 6),
                                RegexMatcher::from('some\-custom\-pattern!{1,100}', 7),
                            ],
                            new ChildrenNodeCollection([])
                        )
                    ])
                ]),
                'optimized' => new RouteTree(null, [
                    1 => new ChildrenNodeCollection([
                        new RouteTreeNode(
                            [
                                new AnyMatcher(0),
                                new ExpressionMatcher('ctype_digit({segment})', 1),
                                new ExpressionMatcher('ctype_alpha({segment})', 2),
                                new ExpressionMatcher('ctype_lower({segment})', 3),
                                new ExpressionMatcher('ctype_upper({segment})', 4),
                                new ExpressionMatcher('ctype_alnum({segment})', 5),
                                new ExpressionMatcher('ctype_alnum(str_replace(\'-\', \'\', {segment}))', 6),
                                RegexMatcher::from('some\-custom\-pattern!{1,100}', 7),
                            ],
                            new ChildrenNodeCollection([])
                        )
                    ])
                ]),
            ],
            [
                // Should order checks from least expensive to most expensive
                'original' => new RouteTree(null, [
                    1 => new ChildrenNodeCollection([
                        new RouteTreeNode(
                            [
                                0 => $customSegmentMatcher = $this->mockRouteSegment(),
                                1 => new ExpressionMatcher('some_expression({segment})', 0),
                                2 => new StaticMatcher('fdsf'),
                                3 => new AnyMatcher(2),
                                4 => RegexMatcher::from('[1-5a-g]+', 3),
                                5 => new ExpressionMatcher('some_other_expression({segment})', 4),
                                6 => new AnyMatcher(5),
                                7 => new StaticMatcher('aqw'),
                            ],
                            new ChildrenNodeCollection([])
                        )
                    ])
                ]),
                'optimized' => new RouteTree(null, [
                    1 => new ChildrenNodeCollection([
                        new RouteTreeNode(
                            [
                                3 => new AnyMatcher(2),
                                6 => new AnyMatcher(5),
                                2 => new StaticMatcher('fdsf'),
                                7 => new StaticMatcher('aqw'),
                                1 => new ExpressionMatcher('some_expression({segment})', 0),
                                5 => new ExpressionMatcher('some_other_expression({segment})', 4),
                                4 => RegexMatcher::from('[1-5a-g]+', 3),
                                0 => $customSegmentMatcher
                            ],
                            new ChildrenNodeCollection([])
                        )
                    ])
                ]),
            ],
            [
                'original' => new RouteTree(null, [
                    1 => new ChildrenNodeCollection([
                        new RouteTreeNode([0 => new StaticMatcher('abc')], new ChildrenNodeCollection([
                            new RouteTreeNode([1 => RegexMatcher::from('[abc]+', 0)], new ChildrenNodeCollection([
                                new RouteTreeNode([2 => new StaticMatcher('def')], new ChildrenNodeCollection([

                                ]))
                            ]))
                        ]))
                    ])
                ]),
                'optimized' => new RouteTree(null, [
                    1 => new ChildrenNodeCollection([
                        new RouteTreeNode(
                            [
                                0 => new StaticMatcher('abc'),
                                2 => new StaticMatcher('def'),
                                1 => RegexMatcher::from('[abc]+', 0),
                            ],
                            new ChildrenNodeCollection([])
                        )
                    ])
                ]),
            ],
            [
                // Should factor out common matchers into a parent node
                'original' => new RouteTree(null, [
                    1 => new ChildrenNodeCollection([
                        new RouteTreeNode(
                            [
                                0 => new StaticMatcher('abc'),
                                1 => new AnyMatcher(0),
                                2 => new StaticMatcher('1'),
                                3 => new StaticMatcher('def'),
                            ],
                            new ChildrenNodeCollection([])
                        ),
                        new RouteTreeNode(
                            [
                                0 => new StaticMatcher('abc'),
                                1 => new AnyMatcher(0),
                                2 => new StaticMatcher('1'),
                                3 => new StaticMatcher('def'),
                            ],
                            new ChildrenNodeCollection([])
                        ),
                        new RouteTreeNode(
                            [
                                0 => new StaticMatcher('abc'),
                                1 => new AnyMatcher(0),
                                2 => new StaticMatcher('3'),
                                3 => new StaticMatcher('def'),
                            ],
                            new ChildrenNodeCollection([])
                        ),
                    ])
                ]),
                'optimized' => new RouteTree(null, [
                    1 => new ChildrenNodeCollection([
                        new RouteTreeNode(
                            [
                                1 => new AnyMatcher(0),
                                0 => new StaticMatcher('abc'),
                                3 => new StaticMatcher('def'),
                            ],
                            new ChildrenNodeCollection([
                                new RouteTreeNode(
                                    [
                                        2 => new StaticMatcher('1'),
                                    ],
                                    new ChildrenNodeCollection([])),
                                new RouteTreeNode(
                                    [
                                        2 => new StaticMatcher('3'),
                                    ],
                                    new ChildrenNodeCollection([])
                                ),
                            ])
                        )
                    ])
                ]),
            ],
            [
                'original'  => new RouteTree(null, [
                    1 => new ChildrenNodeCollection([
                        new RouteTreeNode(
                            [0 => new StaticMatcher('abc'),],
                            new ChildrenNodeCollection([
                                new RouteTreeNode(
                                    [0 => new StaticMatcher('def'),],
                                    new ChildrenNodeCollection([])
                                )
                            ])
                        ),
                    ])
                ]),
                'optimized' => new RouteTree(null, [
                    1 => new ChildrenNodeCollection([
                        new RouteTreeNode(
                            [0 => new CompoundMatcher([new StaticMatcher('abc'), new StaticMatcher('def')]),],
                            new ChildrenNodeCollection([])
                        )
                    ])
                ]),
            ],
        ];
    }

    /**
     * @dataProvider optimizationCases
     */
    public function testRouteTreeOptimizer(RouteTree $original, RouteTree $expected)
    {
        $optimized = $this->optimizer->optimize($original);

        $this->assertEquals($expected, $optimized);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockRouteSegment()
    {
        return $this->getMockBuilder(SegmentMatcher::getType())
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }
}