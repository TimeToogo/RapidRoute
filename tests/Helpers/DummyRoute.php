<?php

namespace RapidRoute\Tests\Helpers;

use RapidRoute\Route;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DummyRoute extends Route
{
    public function __construct(array $httpMethods = ['GET'], array $segments = [], $data = [])
    {
        parent::__construct($httpMethods, $segments, $data);
    }

}