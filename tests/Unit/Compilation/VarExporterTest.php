<?php

namespace RapidRoute\Tests\Unit\Compilation;

use RapidRoute\Compilation\VarExporter;
use RapidRoute\Tests\Helpers\CustomClass;
use RapidRoute\Tests\RapidRouteTest;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class VarExporterTest extends RapidRouteTest
{
    public function exportCases()
    {
       return [
           [1, '1'],
           [-1, '-1'],
           [34243, '34243'],
           [1.0, '1'],
           [-1.954, '-1.954'],
           [true, 'true'],
           [false, 'false'],
           [null, 'null'],
           ['abcdef', '\'abcdef\''],
           ['', '\'\''],
           [[], '[]'],
           [[1], '[0 => 1]'],
           [[1, 2, 3], '[
    0 => 1,
    1 => 2,
    2 => 3,
]'],
           [[1, '2', 3], '[
    0 => 1,
    1 => \'2\',
    2 => 3,
]'],
           [['foo' => 1, [2, 3]], '[
    \'foo\' => 1,
    0 => [
        0 => 2,
        1 => 3,
    ],
]'],
           [new \stdClass(), '(object)[]'],
           [(object)['foo' => 'bar'], '(object)[\'foo\' => \'bar\']'],
           [new CustomClass(), 'unserialize(\'O:36:"RapidRoute\\\\Tests\\\\Helpers\\\\CustomClass":0:{}\')'],
       ];
    }

    /**
     * @dataProvider exportCases
     */
    public function testConvertsValueToValidPhp($value, $code)
    {
        $exported = VarExporter::export($value);

        $evaluated = eval('return ' . $exported . ';');

        $this->assertSame($code, $exported, '');
        $this->assertEquals($value, $evaluated);
    }
}