<?php

namespace RapidRoute;

/**
 * The router result class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RouterResult
{
    const NOT_FOUND = 0;
    const HTTP_METHOD_NOT_ALLOWED = 1;
    const FOUND = 2;

    /**
     * @var int
     */
    protected $status;

    /**
     * @var array|object|null
     */
    protected $routeData;

    /**
     * @var array|null
     */
    protected $parameters;

    /**
     * @var array
     */
    protected $allowedHttpMethods;

    /**
     * Returns a NOT_FOUND router response
     *
     * @return RouterResult
     */
    public static function notFound()
    {
        $result = new RouterResult();
        $result->status = RouterResult::NOT_FOUND;

        return $result;
    }

    /**
     * Returns a HTTP_METHOD_NOT_ALLOWED router response
     *
     * @param string[] $allowedHttpMethods
     *
     * @return RouterResult
     */
    public static function httpMethodNotAllowed(array $allowedHttpMethods)
    {
        $result = new RouterResult();
        $result->status = RouterResult::HTTP_METHOD_NOT_ALLOWED;
        $result->allowedHttpMethods = $allowedHttpMethods;

        return $result;
    }

    /**
     * Returns a FOUND router response
     *
     * @param object|array $data
     * @param array        $parameters
     *
     * @return RouterResult
     * @throws InvalidRouteDataException
     */
    public static function found($data, array $parameters)
    {
        $result = new RouterResult();
        $result->status = RouterResult::FOUND;
        $result->routeData = $data;
        $result->parameters = $parameters;

        return $result;
    }

    /**
     * Returns the response from the router as defined by the class constants:
     * [NOT_FOUND => 0, HTTP_METHOD_NOT_ALLOWED => 1, FOUND => 1]
     *
     * @return int
     */
    public function status()
    {
        return $this->status;
    }

    /**
     * Returns whether the request matched a route and was a valid HTTP method.
     *
     * @return bool
     */
    public function isFound()
    {
        return $this->status === self::FOUND;
    }

    /**
     * Returns whether the request matched did not match any route.
     *
     * @return bool
     */
    public function isNotFound()
    {
        return $this->status === self::NOT_FOUND;
    }

    /**
     * Returns whether the request matched a route but was an invalid HTTP method.
     *
     * @return bool
     */
    public function isDisallowedHttpMethod()
    {
        return $this->status === self::HTTP_METHOD_NOT_ALLOWED;
    }

    /**
     * Returns the matched route data or null if no route was matched.
     *
     * @return array|object|null
     */
    public function getRouteData()
    {
        return $this->routeData;
    }

    /**
     * Returns the array of matched parameters or null if no route was matched.
     *
     * @return array|null
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Returns the array of allowed HTTP method verbs for the matched route
     * if the request submitted an invalid method or null otherwise.
     *
     * @return string[]|null
     */
    public function getAllowedHttpMethods()
    {
        return $this->allowedHttpMethods;
    }
}