<?php

namespace RapidRoute;

/**
 * The match result class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MatchResult
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
     * Constructs a match results object from the supplied array.
     * The expected format is one of:
     *
     * [0 => MatchResult::FOUND, 1 => <route data>, 2 => <parameter array>]
     *
     * [0 => MatchResult::HTTP_METHOD_NOT_ALLOWED, 1 => <allowed http methods array>]
     *
     * [0 => MatchResult::NOT_FOUND]
     *
     * @param array $resultArray
     *
     * @return MatchResult
     * @throws RapidRouteException
     */
    public static function fromArray(array $resultArray)
    {
        $result = new MatchResult();

        switch($resultArray[0]) {
            case MatchResult::FOUND:
                list($result->status, $result->routeData, $result->parameters) = $resultArray;
                break;

            case MatchResult::HTTP_METHOD_NOT_ALLOWED:
                list($result->status, $result->allowedHttpMethods) = $resultArray;
                break;

            case MatchResult::NOT_FOUND:
                $result->status = MatchResult::NOT_FOUND;
                break;

            default:
                throw new RapidRouteException(sprintf('Cannot construct %s: invalid results status %s given', __CLASS__, $resultArray[0]));
        }

        return $result;
    }

    /**
     * Returns the match result status in the equivalent array format.
     * One of:
     *
     * [0 => MatchResult::FOUND, 1 => <route data>, 2 => <parameter array>]
     *
     * [0 => MatchResult::HTTP_METHOD_NOT_ALLOWED, 1 => <allowed http methods array>]
     *
     * [0 => MatchResult::NOT_FOUND]
     *
     * @return array
     */
    public function toArray()
    {
        switch($this->status) {
            case MatchResult::FOUND:
                return [MatchResult::FOUND, $this->routeData, $this->parameters];

            case MatchResult::HTTP_METHOD_NOT_ALLOWED:
                return [MatchResult::HTTP_METHOD_NOT_ALLOWED, $this->allowedHttpMethods];

            default:
                return [MatchResult::NOT_FOUND];
        }
    }

    /**
     * Returns a NOT_FOUND router response
     *
     * @return MatchResult
     */
    public static function notFound()
    {
        return MatchResult::fromArray([MatchResult::NOT_FOUND]);
    }

    /**
     * Returns a HTTP_METHOD_NOT_ALLOWED router response
     *
     * @param string[] $allowedHttpMethods
     *
     * @return MatchResult
     */
    public static function httpMethodNotAllowed(array $allowedHttpMethods)
    {
        return MatchResult::fromArray([MatchResult::HTTP_METHOD_NOT_ALLOWED, $allowedHttpMethods]);
    }

    /**
     * Returns a FOUND router response
     *
     * @param object|array $data
     * @param array        $parameters
     *
     * @return MatchResult
     * @throws InvalidRouteDataException
     */
    public static function found($data, array $parameters)
    {
        return MatchResult::fromArray([MatchResult::FOUND, $data, $parameters]);
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