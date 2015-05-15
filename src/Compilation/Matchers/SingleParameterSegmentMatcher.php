<?php

namespace RapidRoute\Compilation\Matchers;

/**
 * The base route segment matcher class that only maps to single parameter
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class SingleParameterSegmentMatcher extends SegmentMatcher
{
    public function __construct($parameterKey)
    {
        parent::__construct([$parameterKey]);
    }
}