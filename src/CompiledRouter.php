<?php

namespace RapidRoute;

use RapidRoute\Compilation\RouterCompiler;
use RapidRoute\Compilation\TreeBasedRouterCompiler;

/**
 * The compiled router class is a helper class that will
 * generate a compiled router callable.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CompiledRouter
{
    /**
     * @param string   $compiledRouterPath
     * @param callable $routeDefinitionsCallback
     * @param callable $routeCollectionFactory
     * @param callable $routerCompilerFactory
     * @param bool     $forceCompile
     *
     * @return callable
     */
    public static function generate(
        $compiledRouterPath,
        callable $routeDefinitionsCallback,
        callable $routeCollectionFactory = null,
        callable $routerCompilerFactory = null,
        $forceCompile = false
    ) {
        if (file_exists($compiledRouterPath) && !$forceCompile) {
            return require $compiledRouterPath;
        }

        /** @var RouteCollection $routes */
        $routes = $routeCollectionFactory ? $routeCollectionFactory() : new RouteCollection(new RouteParser());

        /** @var RouterCompiler $routeParser */
        $routerCompiler = $routerCompilerFactory ? $routerCompilerFactory() : new TreeBasedRouterCompiler();

        $routeDefinitionsCallback($routes);

        file_put_contents($compiledRouterPath, $routerCompiler->compileRouter($routes));

        return require $compiledRouterPath;
    }
}