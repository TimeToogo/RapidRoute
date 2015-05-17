<?php

namespace RapidRoute;

use RapidRoute\RouteSegments\ParameterSegment;
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
     * @var mixed
     */
    protected $data;

    public function __construct(array $httpMethods = null, array $segments, $data)
    {
        if(is_array($httpMethods) && empty($httpMethods)) {
            throw new RapidRouteException(sprintf('Cannot construct %s: http methods not be empty', __CLASS__));
        }

        $this->httpMethods = $httpMethods === self::ALLOW_ANY_METHOD
            ? self::ALLOW_ANY_METHOD
            : array_map('strtoupper', $httpMethods);
        $this->segments    = $segments;
        $this->data        = $data;

        if($this->httpMethods !== self::ALLOW_ANY_METHOD) {
            // HEAD request should behave identically to GET
            if(in_array('GET', $this->httpMethods) && !in_array('HEAD', $this->httpMethods)) {
                $this->httpMethods[] = 'HEAD';
            }
        }
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

    /**
     * @return bool
     */
    public function isStatic()
    {
        foreach($this->segments as $segment) {
            if($segment instanceof ParameterSegment) {
                return false;
            }
        }

        return true;
    }
}