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
        if (is_array($value)) {
            if(empty($value)) {
                return '[]';
            } elseif(count($value) === 1) {
                reset($value);
                return '[' . self::export(key($value)) . ' => ' . self::export(current($value)) . ']';
            }

            $code = '[' . PHP_EOL;
            $indent = '    ';

            foreach ($value as $key => $element) {
                $code .= $indent;
                $code .= self::export($key);
                $code .= ' => ';
                $code .= str_replace(PHP_EOL, PHP_EOL . $indent, self::export($element));
                $code .= ',' . PHP_EOL;
            }

            $code .= ']';

            return $code;
        } elseif (is_object($value) && get_class($value) === 'stdClass') {
            return '(object)' . self::export((array)$value);
        }

        if($value === null) {
            return 'null';
        }

        if (is_scalar($value)) {
            return var_export($value, true);
        }

        return 'unserialize(' . var_export(serialize($value), true) . ')';
    }
}