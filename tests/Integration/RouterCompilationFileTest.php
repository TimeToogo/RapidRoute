<?php

namespace RapidRoute\Tests\Integration;

use RapidRoute\RouteCollection;
use RapidRoute\Router;
use RapidRoute\Tests\RapidRouteTest;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RouterCompilationFileTest extends RapidRouteTest
{
    /**
     * @var string
     */
    protected $compiledFilePath;

    public function setUp()
    {
        $this->compiledFilePath = __DIR__ . '/compiled/test-compilation.php';
        @unlink($this->compiledFilePath);
    }

    public function testRouterWillCompileFileWhenRequiredAndReuseItSubsequently()
    {
        $called = 0;

        $definitions = function (RouteCollection $routes) use (&$called) {
            $routes->get('/', ['name' => 'home']);
            $called++;
        };

        $router = new Router($this->compiledFilePath, $definitions);

        $this->assertSame(0, $called, 'Should not call route definitions on instantiation, should be lazy');
        $this->assertFileNotExists($this->compiledFilePath);

        $resultOne = $router->match('GET', '/');
        // Should only compile router the first time
        $router->match('GET', '/');

        $this->assertSame(1, $called, 'Should have called route definitions once');
        $this->assertInternalType('callable', $router->getCompiledRouter());
        $this->assertFileExists($this->compiledFilePath);

        $compiledRouter = require $this->compiledFilePath;
        $this->assertInternalType('callable', $compiledRouter);

        // Pretend this is a new request, so should it should check if
        // the router has already been compiled and not recompile
        $newRouter = new Router($this->compiledFilePath, $definitions);

        $resultTwo = $newRouter->match('GET', '/');

        $this->assertSame(
            1,
            $called,
            'Should not have called route definitions and instead used previously compiled router'
        );

        $this->assertEquals($resultOne, $resultTwo);
    }

    public function testClearCompiledWillRemoveTheCompiledRouterFile()
    {
        $router = new Router(
            $this->compiledFilePath,
            function (RouteCollection $routes) use (&$called) {
                $routes->get('/abc', ['name' => 'home']);
            });

        $router->match('GET', '/abc');

        $this->assertFileExists($this->compiledFilePath);

        $router->clearCompiled();

        $this->assertFileNotExists($this->compiledFilePath);

        $router->match('POST', '/abc');

        $this->assertFileExists($this->compiledFilePath);
    }

    public function testRouterWillNotUsedCompiledFileIfInDevelopmentMode()
    {
        $called = 0;

        $definitions = function (RouteCollection $routes) use (&$called) {
            $routes->get('/', ['name' => 'home']);
            $called++;
        };

        // Compile routes
        $router = new Router($this->compiledFilePath, $definitions);
        $router->match('GET', '/');

        // New router in dev mode should not reuse compiled routes
        $newRouter = new Router($this->compiledFilePath, $definitions);
        $newRouter->setDevelopmentMode(true);
        $newRouter->match('HEAD', '/abc');
        // Should only reload once
        $newRouter->match('HEAD', '/abc');

        $this->assertTrue($newRouter->isDevelopmentMode());
        $this->assertSame(
            2,
            $called,
            'The router in development mode should have regenerated the routes from the definitions'
        );
    }
}