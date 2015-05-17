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

    const APLHA = '[a-zA-Z]+';

    const APLHA_NUM = '[a-zA-Z\d]+';

    const APLHA_NUM_DASH = '[a-zA-Z\d\-]+';

    const APLHA_UPPER = '[A-Z]+';

    const APLHA_LOWER = '[a-z]+';

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