<?php

namespace RapidRoute\Tests\Unit\Compilation;

use RapidRoute\Compilation\PhpBuilder;
use RapidRoute\Tests\RapidRouteTest;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PhpBuilderTest extends RapidRouteTest
{
    /**
     * @var PhpBuilder
     */
    protected $builder;

    public function setUp()
    {
        $this->builder = new PhpBuilder();
    }

    public function testInitialState()
    {
        $this->assertSame('', $this->builder->getCode());
        $this->assertSame(0, $this->builder->indent);
    }

    public function testAppend()
    {
        $this->builder->append('<?= strlen(""); ?>');

        $this->assertSame('<?= strlen(""); ?>', $this->builder->getCode());
    }

    public function testAppendLine()
    {
        $this->builder->appendLine('<?php');
        $this->builder->appendLine('return 1;');
        $this->builder->append('?>');

        $this->assertSame('<?php' . PHP_EOL . 'return 1;' . PHP_EOL . '?>', $this->builder->getCode());
    }

    public function testAppendWithIndent()
    {
        $this->builder->indent++;
        $this->builder->append(<<<'PHP'
some_code();
$more = $code;
PHP
        );

        $this->assertSame(<<<'PHP'
    some_code();
    $more = $code;
PHP
            , $this->builder->getCode());
    }

    public function testBuilderUsage()
    {
        $this->builder->appendLine('<?php');
        $this->builder->appendLine();
        $this->builder->appendLine('while(true) {');

        $this->builder->indent++;
        $this->builder->appendLine('echo \'hi\';');
        $this->builder->indent--;

        $this->builder->appendLine('}');
        $this->builder->appendLine();
        $this->builder->append('?>');

        $this->assertSame(<<<'PHP'
<?php

while(true) {
    echo 'hi';
}

?>
PHP
            , $this->builder->getCode());
    }
}