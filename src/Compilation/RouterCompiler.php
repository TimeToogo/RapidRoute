<?php

namespace RapidRoute\Compilation;

use RapidRoute\RouteCollection;

/**
 * The router compiler interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface RouterCompiler
{
    /**
     * Compiles a the supplied route collection to native PHP.
     * The returned string should be a valid PHP file that will
     * return a callable with the signature:
     *
     * function ($httpMethod, $uri) : array
     *
     * Which will return an array containing the match status in
     * the format that is accepted by MatchResult::fromArray()
     *
     * @param RouteCollection $routes
     *
     * @return string
     */
    public function compileRouter(RouteCollection $routes);
}