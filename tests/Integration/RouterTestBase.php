<?php

namespace RapidRoute\Tests\Integration;

use RapidRoute\RouteCollection;
use RapidRoute\Router;
use RapidRoute\MatchResult;
use RapidRoute\Tests\RapidRouteTest;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class RouterTestBase extends RapidRouteTest
{
    protected static $isFirstTest = [];

    /**
     * @var Router
     */
    protected $router;

    public function setUp()
    {
        if(!isset(self::$isFirstTest[get_called_class()])) {
            @unlink($this->getCompilationFilePath());
            self::$isFirstTest[get_called_class()] = true;
        }

        $this->router = new Router(
            $this->getCompilationFilePath(),
            function (RouteCollection $collection) {
                $this->definitions($collection);
            }
        );
    }

    /**
     * @return string
     */
    protected function getCompilationFilePath()
    {
        return __DIR__ . '/compiled/' . $this->compiledFileName() . '.php';
    }

    abstract protected function compiledFileName();

    abstract protected function definitions(RouteCollection $routes);

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
    abstract public function routerMatchingExamples();

    /**
     * @dataProvider routerMatchingExamples
     */
    public function testRouter($httpMethod, $uri, MatchResult $expectedResult)
    {
        $actualResult = $this->router->match($httpMethod, $uri);

        $this->assertEquals($expectedResult, $actualResult);
    }
}