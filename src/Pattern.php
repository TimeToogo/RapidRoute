<?php

namespace RapidRoute;

/**
 * The parameter pattern enum class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
final class Pattern
{
    private function __construct() {}

    const ANY = '.+';

    const DIGITS = '\d+';

    const ALPHA = '[a-zA-Z]+';

    const ALPHA_NUM = '[a-zA-Z\d]+';

    const ALPHA_NUM_DASH = '[a-zA-Z\d\-]+';

    const ALPHA_UPPER = '[A-Z]+';

    const ALPHA_LOWER = '[a-z]+';

    /**
     * @param string $pattern
     *
     * @return string
     */
    public static function asRegex($pattern)
    {
        return '/^(' . $pattern . ')$/';
    }
}