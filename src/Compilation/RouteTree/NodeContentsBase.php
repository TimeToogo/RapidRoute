<?php

namespace RapidRoute\Compilation\RouteTree;

/**
 * The node content base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class NodeContentsBase
{
    /**
     * @return string
     */
    public static function getType()
    {
        return get_called_class();
    }
}