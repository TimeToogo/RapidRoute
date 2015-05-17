<?php

namespace RapidRoute\Tests\Integration;

use RapidRoute\CompiledRouter;
use RapidRoute\RouteCollection;
use RapidRoute\RouteParser;
use RapidRoute\Tests\Helpers\CustomRouteCollection;
use RapidRoute\Tests\Helpers\DummyRouteCompiler;
use RapidRoute\Tests\RapidRouteTest;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CompiledRouterTest extends RapidRouteTest
{
    /**
     * @var string
     */
    protected $compiledFilePath;

    public function setUp()
    {
        $this->compiledFilePath = __DIR__ . '/compiled/compiled-router-test.php';
        @unlink($this->compiledFilePath);
    }

    public function testCompiledRouterWillGenerateRouterFile()
    {
        $called = 0;

        $definitions = function (RouteCollection $routes) use (&$called) {
            $routes->get('/', ['name' => 'home']);
            $called++;
        };

        $router = CompiledRouter::generate($this->compiledFilePath, $definitions);

        $this->assertSame(1, $called, 'Should have called route definitions once');
        $this->assertInternalType('callable', $router);
        $this->assertFileExists($this->compiledFilePath);

        $routerFromFile = self::staticRequire($this->compiledFilePath);

        $this->assertEquals(
            \ReflectionFunction::export($routerFromFile, true),
            \ReflectionFunction::export($router, true)
        );
    }

    public function testCustomRouteCollection()
    {
        $definitions = function (RouteCollection $routes) use (&$called) {
            $this->assertInstanceOf(CustomRouteCollection::getType(), $routes);
        };

        CompiledRouter::generate(
            $this->compiledFilePath,
            $definitions,
            function () { return new CustomRouteCollection(new RouteParser()); }
        );
    }

    public function testCustomRouteParser()
    {
        $router = CompiledRouter::generate(
            $this->compiledFilePath,
            function (RouteCollection $routes) use (&$called) {
                $routes->get('/', ['home']);
            },
            null,
            function () { return new DummyRouteCompiler('<?php return function () { return [\'SUCCESS\']; }; ?>'); }
        );

        $this->assertSame(['SUCCESS'], $router());
    }


    private static function staticRequire($file)
    {
        return require $file;
    }
}