<?php

namespace RapidRoute\Tests\Helpers;

use RapidRoute\Compilation\RouterCompiler;
use RapidRoute\RouteCollection;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DummyRouteCompiler implements RouterCompiler
{
    public $compiled;

    public function __construct($compiled)
    {
        $this->compiled = $compiled;
    }

    public function compileRouter(RouteCollection $routes)
    {
        return $this->compiled;
    }
}