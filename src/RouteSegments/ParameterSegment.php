<?php

namespace RapidRoute\RouteSegments;

use RapidRoute\Compilation\Matchers\RegexMatcher;
use RapidRoute\Pattern;

/**
 * Route segment for a parameter segment of the uri.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ParameterSegment extends RouteSegment
{
    /**
     * @var string[]
     */
    protected $names;

    /**
     * @var string
     */
    protected $regex;

    public function __construct(array $names, $regex)
    {
        $this->names  = $names;
        $this->regex = $regex;
    }

    /**
     * @param string $name
     * @param string $pattern
     *
     * @return ParameterSegment
     */
    public static function from($name, $pattern)
    {
        return new self([$name], Pattern::asRegex($pattern));
    }

    /**
     * @return string[]
     */
    public function getNames()
    {
        return $this->names;
    }

    /**
     * @return string
     */
    public function getRegex()
    {
        return $this->regex;
    }

    public function getMatcher(array &$parameterIndexNameMap)
    {
        $parameterKey = empty($parameterIndexNameMap) ? 0 : max(array_keys($parameterIndexNameMap)) + 1;
        $parameterKeyGroupMap = [];
        $group = 0;

        foreach($this->names as $name) {
            $parameterIndexNameMap[$parameterKey] = $name;
            $parameterKeyGroupMap[$parameterKey] = $group++;
            $parameterKey++;
        }

        return new RegexMatcher($this->regex, $parameterKeyGroupMap);
    }
}