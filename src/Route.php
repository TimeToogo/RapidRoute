<?php

namespace RapidRoute;

use RapidRoute\RouteSegments\RouteSegment;

/**
 * The route class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Route
{
    const ALLOW_ANY_METHOD = null;

    /**
     * @var string[]
     */
    protected $httpMethods;

    /**
     * @var RouteSegment[]
     */
    protected $segments;

    /**
     * @var array|object
     */
    protected $data;

    public function __construct(array $httpMethods = null, array $segments, $data)
    {
        if(is_array($httpMethods) && empty($httpMethods)) {
            throw new RapidRouteException(sprintf('Cannot construct %s: http methods not be empty', __CLASS__));
        }

        if (!is_array($data) && !is_object($data)) {
            throw new InvalidRouteDataException($data);
        }

        $this->httpMethods = $httpMethods === self::ALLOW_ANY_METHOD
            ? self::ALLOW_ANY_METHOD
            : array_map('strtoupper', $httpMethods);
        $this->segments    = $segments;
        $this->data        = $data;
    }

    /**
     * @return string
     */
    public static function getType()
    {
        return get_called_class();
    }

    /**
     * @return bool
     */
    public function allowsAnyHttpMethod()
    {
        return $this->httpMethods === self::ALLOW_ANY_METHOD;
    }

    /**
     * @return string[]|null
     */
    public function getHttpMethods()
    {
        return $this->httpMethods;
    }

    /**
     * @return RouteSegment[]
     */
    public function getSegments()
    {
        return $this->segments;
    }

    /**
     * @return array|object
     */
    public function getData()
    {
        return $this->data;
    }
}