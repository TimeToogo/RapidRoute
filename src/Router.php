<?php

namespace RapidRoute;

use RapidRoute\Compilation\RouterCompiler;
use RapidRoute\Compilation\TreeBasedRouterCompiler;

/**
 * The router class is a higher level entry point to the RapidRoute API.
 *
 * This class will wrap the array from the compiled router in an instance
 * of the MatchResult class as well as providing some other helper
 * methods for dealing with the compiled router.
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

    public function __construct(
        $compiledRouterPath,
        callable $routeDefinitionsCallback
    ) {
        $this->compiledRouterPath       = $compiledRouterPath;
        $this->routeDefinitionsCallback = $routeDefinitionsCallback;
    }

    /**
     * @return RouteCollection
     */
    protected function buildRouteCollection()
    {
        return new RouteCollection(new RouteParser());
    }

    /**
     * @return RouterCompiler
     */
    protected function buildRouterCompiler()
    {
        return new TreeBasedRouterCompiler();
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
     * @return MatchResult
     * @throws InvalidRouteDataException
     */
    public function match($httpMethod, $uri)
    {
        $compiledRouter = $this->getCompiledRouter();

        return MatchResult::fromArray($compiledRouter($httpMethod, $uri));
    }

    /**
     * Gets the compiled router callable.
     * The callable signature is:
     *
     * function ($httpMethod, $uri) : array
     *
     * @return callable
     */
    public function getCompiledRouter()
    {
        if ($this->compiledRouter === null) {
            $this->compiledRouter = CompiledRouter::generate(
                $this->compiledRouterPath,
                $this->routeDefinitionsCallback,
                function () { return $this->buildRouteCollection(); },
                function () { return $this->buildRouterCompiler(); },
                $this->developmentMode
            );
        }

        return $this->compiledRouter;
    }

    /**
     * Clears the compiled router, it will be recompiled when next requested.
     *
     * @return void
     */
    public function clearCompiled()
    {
        if(file_exists($this->compiledRouterPath)) {
            @unlink($this->compiledRouterPath);
        }

        $this->compiledRouter = null;
    }
}