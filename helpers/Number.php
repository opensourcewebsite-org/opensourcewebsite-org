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
 *
 * Notes and thoughts for contributors:
 * - Nevertheless arg `$scale` is not required in all BC Math Functions - I made it required after few days working.
 *   Otherwise - easy to forget.
 *      - But, maybe, you may want to use {@see bcscale()} as default for whole project.
 * - Methods like {@see Number::floatSub()} are just aliases. And maybe redundant - in terms of beautiful code.
 *   But in terms of readability by developers of any level (beginners especially) - they may be helpful. I guess.
 */
class Number
{
    public static function isFloatEqual(?string $leftFloat, ?string $rightFloat, int $scale): bool
    {
        return 0 === bccomp($leftFloat, $rightFloat, $scale);
    }

    /**
     * @return bool is leftFloat greater
     */
    public static function isFloatGreater(?string $leftFloat, ?string $rightFloat, int $scale): bool
    {
        return 1 === bccomp($leftFloat, $rightFloat, $scale);
    }

    /**
     * @return bool is leftFloat lower
     */
    public static function isFloatLower(?string $leftFloat, ?string $rightFloat, int $scale): bool
    {
        return -1 === bccomp($leftFloat, $rightFloat, $scale);
    }

    /**
     * @see isFloatGreater
     */
    public static function isFloatGreaterE(?string $leftFloat, ?string $rightFloat, int $scale): bool
    {
        return self::isFloatGreater($leftFloat, $rightFloat, $scale)
            || self::isFloatEqual($leftFloat, $rightFloat, $scale);
    }

    /**
     * @see isFloatLower
     */
    public static function isFloatLowerE(?string $leftFloat, ?string $rightFloat, int $scale): bool
    {
        return self::isFloatLower($leftFloat, $rightFloat, $scale)
            || self::isFloatEqual($leftFloat, $rightFloat, $scale);
    }

    public static function floatSub(?string $leftFloat, ?string $rightFloat, int $scale): string
    {
        return bcsub($leftFloat, $rightFloat, $scale);
    }

    public static function floatAdd(?string $leftFloat, ?string $rightFloat, int $scale): string
    {
        return bcadd($leftFloat, $rightFloat, $scale);
    }
}
