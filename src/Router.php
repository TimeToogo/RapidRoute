<?php

namespace RapidRoute;

use RapidRoute\Compilation\RouterCompiler;

/**
 * The router class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Router
{
    /**
     * @var bool
     */
    protected $developmentMode = false;

    /**
     * @var string
     */
    protected $compiledRouterPath;

    /**
     * @var callable
     */
    protected $routeDefinitionsCallback;

    /**
     * @var callable
     */
    protected $compiledRouter;

    /**
     * @var RouteParser
     */
    protected $routeParser;

    public function __construct(
        $compiledRouterPath,
        callable $routeDefinitionsCallback,
        RouteParser $routeParser = null
    ) {
        $this->compiledRouterPath       = $compiledRouterPath;
        $this->routeDefinitionsCallback = $routeDefinitionsCallback;
        $this->routeParser              = $routeParser ?: new RouteParser();
    }

    /**
     * Gets whether the router is in development modes
     *
     * @return bool
     */
    public function isDevelopmentMode()
    {
        return $this->developmentMode;
    }

    /**
     * If set to true the routes will be recompiled every request.
     *
     * @param bool $developmentMode
     */
    public function setDevelopmentMode($developmentMode)
    {
        $this->developmentMode = $developmentMode;
    }

    /**
     * @param string $httpMethod
     * @param string $uri
     *
     * @return RouterResult
     * @throws InvalidRouteDataException
     */
    public function match($httpMethod, $uri)
    {
        if ($this->compiledRouter === null) {
            $this->compiledRouter = $this->loadCompiledRouter();
        }

        $compiledRouter = $this->compiledRouter;

        return $compiledRouter($httpMethod, $uri);
    }

    /**
     * Clears the compiled router, it will be recompiled when next
     * requested.
     *
     * @return void
     */
    public function clearCompiled()
    {
        @unlink($this->compiledRouterPath);
        $this->compiledRouter = null;
    }

    /**
     * @return callable
     */
    protected function loadCompiledRouter()
    {
        if ($this->developmentMode || !file_exists($this->compiledRouterPath)) {
            $this->saveCompiledRouter();
        }

        return require $this->compiledRouterPath;
    }

    /**
     * @return void
     */
    protected function saveCompiledRouter()
    {
        file_put_contents($this->compiledRouterPath, $this->compileRouterFile());
    }

    /**
     * @return string
     */
    protected function compileRouterFile()
    {
        $definitionsCallback = $this->routeDefinitionsCallback;
        $routes              = new RouteCollection($this->routeParser);
        $definitionsCallback($routes);

        $compiler = new RouterCompiler();

        return $compiler->compileRoutesToPhpClosure($routes);
    }
}