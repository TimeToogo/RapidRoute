<?php

namespace RapidRoute;

use Exception;

/**
 * The base exception class for the RapidRoute library.
 * 
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RapidRouteException extends \Exception
{
    /**
     * @return string
     */
    public static function getType()
    {
        return get_called_class();
    }
}