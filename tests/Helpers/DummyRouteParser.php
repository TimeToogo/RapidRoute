<?php

namespace RapidRoute\Tests\Helpers;

use RapidRoute\RouteParser;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DummyRouteParser extends RouteParser
{
    /**
     * @var array[]
     */
    public $patternSegmentsMap = [];

    /**
     * @var array|null
     */
    public $lastParsed;

    public function parse($pattern, array $conditions)
    {
        $this->lastParsed = func_get_args();
        return $this->patternSegmentsMap[$pattern];
    }
}