<?php

namespace RapidRoute\Compilation;

/**
 * The variable exporter class. Converts PHP values into a source code
 * representation.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class VarExporter
{
    /**
     * Converts the supplied value into a valid PHP representation.
     *
     * @param mixed $value
     *
     * @return string
     */
    public static function export($value)
    {
        if (self::shouldBeSerialized($value)) {
            return 'unserialize(' . var_export(serialize($value), true) . ')';
        }

        if (is_array($value)) {
            $code = '[';

            foreach ($value as $key => $element) {
                $code .= self::export($key);
                $code .= ' => ';
                $code .= self::export($element);
                $code .= ', ';
            }

            if (strlen($code) > 2) {
                $code = substr($code, 0, -2);
            }

            $code .= ']';

            return $code;
        }

        if($value === null) {
            return 'null';
        }

        return var_export($value, true);
    }

    protected static function shouldBeSerialized($value)
    {
        if (is_scalar($value) || $value === null) {
            return false;
        }

        if (is_array($value)) {
            $shouldBeSerialized = false;

            array_walk_recursive($value, function ($value) use (&$shouldBeSerialized) {
                if (!$shouldBeSerialized) {
                    $shouldBeSerialized = self::shouldBeSerialized($value);
                }
            });

            return $shouldBeSerialized;
        }

        return true;
    }
}