<?php

namespace RapidRoute;

/**
 * The route collection class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RouteCollection
{
    /**
     * @var string[]
     */
    protected $globalParameterConditions = [];

    /**
     * @var RouteParser
     */
    protected $parser = [];

    /**
     * @var Route[]
     */
    protected $routes = [];

    public function __construct(RouteParser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @return string
     */
    public static function getType()
    {
        return get_called_class();
    }

    /**
     * Adds a route for a GET http request matching the supplied pattern to the collection.
     *
     * @param array|string $pattern
     * @param mixed $data
     *
     * @return void
     */
    public function get($pattern, $data)
    {
        $this->add('GET', $pattern, $data);
    }

    /**
     * Adds a route for a POST http request matching the supplied pattern to the collection.
     *
     * @param array|string $pattern
     * @param mixed $data
     *
     * @return void
     */
    public function post($pattern, $data)
    {
        $this->add('POST', $pattern, $data);
    }

    /**
     * Adds a route for a PUT http request matching the supplied pattern to the collection.
     *
     * @param array|string $pattern
     * @param mixed $data
     *
     * @return void
     */
    public function put($pattern, $data)
    {
        $this->add('PUT', $pattern, $data);
    }

    /**
     * Adds a route for a PATCH http request matching the supplied pattern to the collection.
     *
     * @param array|string $pattern
     * @param mixed $data
     *
     * @return void
     */
    public function patch($pattern, $data)
    {
        $this->add('PATCH', $pattern, $data);
    }

    /**
     * Adds a route for a DELETE http request matching the supplied pattern to the collection.
     *
     * @param array|string $pattern
     * @param mixed $data
     *
     * @return void
     */
    public function delete($pattern, $data)
    {
        $this->add('DELETE', $pattern, $data);
    }

    /**
     * Adds a route for an http request matching the supplied pattern to the collection.
     *
     * @param array|string $pattern
     * @param mixed $data
     *
     * @return void
     */
    public function any($pattern, $data)
    {
        $this->add(Route::ALLOW_ANY_METHOD, $pattern, $data);
    }

    /**
     * Adds a route for the supplied http method(s) and pattern to the collection.
     *
     * @param string|string[]|null $httpMethods The http method(s) for the route, null if any method is allowed
     * @param array|string         $pattern     The routing pattern
     * @param array|object         $data        The associated route / handler data
     *
     * @throws InvalidRoutePatternException
     * @return void
     */
    public function add($httpMethods, $pattern, $data)
    {
        list($patternString, $conditions) = $this->parseRoutingPattern($pattern);

        $this->addRoute(new Route(
            $httpMethods === Route::ALLOW_ANY_METHOD ? Route::ALLOW_ANY_METHOD : (array)$httpMethods,
            $this->parser->parse($patternString, $conditions + $this->globalParameterConditions),
            $data
        ));
    }

    protected function parseRoutingPattern($pattern)
    {
        if(is_string($pattern)) {
            return [$pattern, []];
        }

        if(is_array($pattern)) {
            if(!isset($pattern[0]) || !is_string($pattern[0])) {
                throw new InvalidRoutePatternException(sprintf(
                    'Cannot add route: route pattern array must have the first element containing the pattern string, %s given',
                    isset($pattern[0]) ? gettype($pattern[0]) : 'none'
                ));
            }

            $patternString = $pattern[0];
            $parameterConditions = $pattern;
            unset($parameterConditions[0]);

            return [$patternString, $parameterConditions];
        }

        throw new InvalidRoutePatternException(sprintf(
            'Cannot add route: route pattern must be a pattern string or array, %s given',
            gettype($pattern)
        ));
    }

    /**
     * Adds the supplied route to the collection
     *
     * @param Route $route
     *
     * @return void
     */
    public function addRoute(Route $route)
    {
        $this->routes[] = $route;
    }

    /**
     * Defines the supplied parameter name to be globally associated with the pattern
     *
     * @param string $parameterName
     * @param string $pattern
     *
     * @return void
     */
    public function param($parameterName, $pattern)
    {
        $this->globalParameterConditions[$parameterName] = $pattern;
    }

    /**
     * Defines the supplied parameter name to be globally associated with the pattern
     *
     * @param string[] $parameterPatternMap
     *
     * @return void
     */
    public function params(array $parameterPatternMap)
    {
        $this->globalParameterConditions += $parameterPatternMap;
    }

    /**
     * Removes the global pattern associated with the supplied parameter name
     *
     * @param string $parameterName
     *
     * @return void
     */
    public function removeParam($parameterName)
    {
        unset($this->globalParameterConditions[$parameterName]);
    }

    /**
     * @return Route[]
     */
    public function asArray()
    {
        return $this->routes;
    }
}