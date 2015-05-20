<?php

namespace RapidRoute;

use RapidRoute\RouteSegments\ParameterSegment;
use RapidRoute\RouteSegments\StaticSegment;

/**
 * The route parser class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RouteParser
{
    const DEFAULT_PARAMETER_PATTERN = Pattern::ANY;
    const STATIC_PART = 0;
    const PARAMETER_PART = 1;

    /**
     * Parses the supplied route pattern into an array of route segments.
     *
     * Example:
     *  $pattern = 'user/{id}/delete'
     *  $conditions = ['id' => '[0-9]+']
     * Should return:
     *  [
     *      StaticSegment{ $value => 'user' },
     *      ParameterSegment{ $name => 'id', $match => '[0-9]+' },
     *      StaticSegment{ $value => 'delete' },
     *  ]
     *
     * @param string   $pattern
     * @param string[] $conditions
     *
     * @return RouteSegments\RouteSegment[]
     * @throws InvalidRoutePatternException
     */
    public function parse($pattern, array $conditions)
    {
        if (strlen($pattern) > 1 && $pattern[0] !== '/') {
            throw new InvalidRoutePatternException(
                sprintf('Invalid route pattern: non-root route must be prefixed with \'/\', \'%s\' given', $pattern)
            );
        }

        $segments = [];

        $patternSegments = explode('/', $pattern);
        array_shift($patternSegments);

        foreach ($patternSegments as $key => $patternSegment) {
            if ($this->matchRouteParameters($pattern, $patternSegment, $matches, $names)) {
                $regex = $this->compileSegmentRegex($matches, $conditions);

                $segments[] = new ParameterSegment($names, $regex);
            } else {
                $segments[] = new StaticSegment($patternSegment);
            }
        }

        return $segments;
    }

    protected function matchRouteParameters($pattern, $patternSegment, &$matches, &$names)
    {
        $matchedParameter = false;
        $names   = [];
        $matches = [];

        $current = '';
        $inParameter = false;

        foreach(str_split($patternSegment) as $character) {
            if($inParameter) {
                if($character === '}') {
                    $matches[] = [self::PARAMETER_PART, $current];
                    $names[] = $current;
                    $current = '';
                    $inParameter = false;
                    $matchedParameter = true;
                    continue;
                } elseif ($character === '{') {
                    throw new InvalidRoutePatternException(sprintf(
                        'Invalid route pattern: cannot contain nested \'{\', \'%s\' given',
                        $pattern
                    ));
                }
            } else {
                if($character === '{') {
                    $matches[] = [self::STATIC_PART, $current];
                    $current = '';
                    $inParameter = true;
                    continue;
                } elseif ($character === '}') {
                    throw new InvalidRoutePatternException(sprintf(
                        'Invalid route pattern: cannot contain \'}\' before opening \'{\', \'%s\' given',
                        $pattern
                    ));
                }
            }

            $current .= $character;
        }

        if($inParameter) {
            throw new InvalidRoutePatternException(sprintf(
                'Invalid route pattern: cannot contain \'{\' without closing \'}\', \'%s\' given',
                $pattern
            ));
        } elseif ($current !== '') {
            $matches[] = [self::STATIC_PART, $current];
        }

        return $matchedParameter;
    }

    protected function compileSegmentRegex(array $matches, array $parameterPatterns)
    {
        $regex = '/^';

        foreach($matches as $match) {
            list($type, $part) = $match;

            if($type === self::STATIC_PART) {
                $regex .= preg_quote($part, '/');
            } else {
                // Parameter, $part is the parameter name
                $pattern = isset($parameterPatterns[$part]) ? $parameterPatterns[$part] : self::DEFAULT_PARAMETER_PATTERN;
                $regex .= '(' . $pattern . ')';
            }
        }

        $regex .= '$/';

        return $regex;
    }
}
