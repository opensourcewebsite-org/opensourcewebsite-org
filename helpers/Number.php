<?php

namespace app\helpers;

/**
 * Class Number
 *
 * WARNING: don't compare float numbers in simple way: `0.17 === (1 - 0.83)`.
 * In some cases this way will return unexpected result!
 *
 * SOLUTION: use BC Math Functions https://www.php.net/manual/en/ref.bc.php
 *
 * @see https://stackoverflow.com/questions/3148937/compare-floats-in-php
 * @package app\helpers
 */
class Number
{
    public static function isFloatEqual(string $leftFloat, string $rightFloat, int $scale = 0): bool
    {
        return 0 === bccomp($leftFloat, $rightFloat, $scale);
    }

    public static function isFloatGreater(string $leftFloat, string $rightFloat, int $scale = 0): bool
    {
        return 1 === bccomp($leftFloat, $rightFloat, $scale);
    }

    public static function isFloatLower(string $leftFloat, string $rightFloat, int $scale = 0): bool
    {
        return -1 === bccomp($leftFloat, $rightFloat, $scale);
    }
}
