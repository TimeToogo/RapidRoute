<?php

namespace RapidRoute;

/**
 * Exception class for invalid route data
 * 
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InvalidRoutePatternException extends RapidRouteException
{
    public function __construct($message)
    {
        parent::__construct($message);
    }

}