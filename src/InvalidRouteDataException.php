<?php

namespace RapidRoute;

/**
 * Exception class for invalid route data.
 * 
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InvalidRouteDataException extends RapidRouteException
{
    public function __construct($data)
    {
        parent::__construct(
            sprintf('The supplied route data is invalid: expecting object or array, %s given', gettype($data))
        );
    }

}