<?php

class Utils
{

    const SPECIAL_SYMBOLS = array(
        '!', '"', '$', '%',
        '\'', '(', ')', '*',
        '+', ',', '-', '.',
        '/', ':', '<', '=',
        '>', '?', '@', '[',
        '\\', ']', '^', '_',
        '`', '{', '|', '}',
        '~'
    );

    const SPECIAL_SYMBOL_REPLACEMENT = array(
        '&#33;', '&#34;', '&#36;', '&#37;',
        '&#39;', '&#40;', '&#41;', '&#42;',
        '&#43;', '&#44;', '&#45;', '&#46;',
        '&#47;', '&#58;', '&#60;', '&#61;',
        '&#62;', '&#63;', '&#64;', '&#91;',
        '&#92;', '&#93;', '&#94;', '&#95;',
        '&#96;', '&#123;', '&#124;', '&#125;',
        '&#126;'
    );

    public static function secureGetAndPost()
    {
        $_GET = self::encodeHtml($_GET);
        $_POST = self::encodeHtml($_POST);
    }

    public static function encodeHtml($html)
    {
        return str_replace(self::SPECIAL_SYMBOLS, self::SPECIAL_SYMBOL_REPLACEMENT, $html);
    }

    public static function decodeHTML($html)
    {
        return str_replace(self::SPECIAL_SYMBOL_REPLACEMENT, self::SPECIAL_SYMBOLS, $html);
    }

    public static function getExceptionTraceAsString($exception)
    {
        $rtn = "";
        $count = 0;
        foreach ($exception->getTrace() as $frame) {
            $args = "";
            if (isset($frame['args'])) {
                $args = array();
                foreach ($frame['args'] as $arg) {
                    if (is_string($arg)) {
                        $args[] = "'" . $arg . "'";
                    } elseif (is_array($arg)) {
                        $args[] = "Array";
                    } elseif (is_null($arg)) {
                        $args[] = 'NULL';
                    } elseif (is_bool($arg)) {
                        $args[] = ($arg) ? "true" : "false";
                    } elseif (is_object($arg)) {
                        $args[] = get_class($arg);
                    } elseif (is_resource($arg)) {
                        $args[] = get_resource_type($arg);
                    } else {
                        $args[] = $arg;
                    }
                }
                $args = join(", ", $args);
            }
            $rtn .= sprintf("#%s %s(%s): %s(%s)\n",
                $count,
                $frame['file'],
                $frame['line'],
                $frame['function'],
                $args);
            $count++;
        }
        return $rtn;
    }

    public static function generatePassword()
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array();
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass);
    }
}