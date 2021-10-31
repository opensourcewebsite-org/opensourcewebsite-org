<?php

namespace app\modules\bot\components\helpers;

/**
 * Class LocationParser
 *
 * Trying to parse location lat and lng from string.
 * call [[parse]] method to parse location from string.
 *
 * ToDo: need to throw exception if not possible to parse
 * and add 'throwOnError' option.
 *
 * @package app\modules\bot\components\helpers
 */
class LocationParser
{
    private const MAIN_DELIMITER = '|';
    private array $delimiters;
    private string $locationStr;

    public function __construct(string $locationStr, array $delimiters = [',', ' ', ';', "\n"])
    {
        $this->locationStr = $locationStr;
        $this->delimiters = $delimiters;
    }

    /**
     * @return int[]|null[]|string[]
     */
    public function parse(): array
    {
        $latitude = null;
        $longitude = null;

        $text = str_replace($this->delimiters, self::MAIN_DELIMITER, $this->locationStr);
        $text = str_replace(self::MAIN_DELIMITER . self::MAIN_DELIMITER, self::MAIN_DELIMITER, $text);

        $coords = explode(self::MAIN_DELIMITER, $text);

        $coords = array_map('trim', $coords);

        if (count($coords) === 2) {
            $coords = array_map(
                function ($item) {
                    return preg_replace("|[^0-9\.]|", "$2", $item);
                },
                $coords
            );

            if (is_numeric($coords[0]) && is_numeric($coords[1])) {
                $latitude = $coords[0];
                $longitude = $coords[1];
            }
        }

        return [$latitude, $longitude];
    }

    public function withLocationStr(string $str): self
    {
        $clone = clone $this;
        $this->locationStr = $str;

        return $clone;
    }
}
