<?php

namespace app\modules\bot\components\crud\rules;

/**
 * Class LocationToArrayFieldComponent
 *
 * @package app\modules\bot\components\rules
 */
class LocationToArrayFieldComponent extends BaseFieldComponent implements FieldInterface
{
    const MAIN_DELIMITER = '|';

    /** @var string[] */
    public $delimiters = [' ', ';', "\n"];

    /** @inheritDoc */
    public function prepare($text)
    {
        $latitude = null;
        $longitude = null;

        if (!$text) {
            $message = $this->getUpdate()->getMessage();
            if ($message && ($location = $message->getLocation())) {
                $latitude = $location->getLatitude();
                $longitude = $location->getLongitude();
            }
        } else {
            // removeExtraChars
            // $text = preg_replace('/[^\d\.\- ]/', '', $str);

            $text = str_replace($this->delimiters, self::MAIN_DELIMITER, $text);
            $text = str_replace(self::MAIN_DELIMITER . self::MAIN_DELIMITER, self::MAIN_DELIMITER, $text);

            $coords = explode(self::MAIN_DELIMITER, $text);

            if (count($coords) == 2) {
                $coords[0] = preg_replace("|[^0-9\.]|", "$2", $coords[0]);
                $coords[1] = preg_replace("|[^0-9\.]|", "$2", $coords[1]);
                if (is_numeric($coords[0]) && is_numeric($coords[1])) {
                    $latitude = $coords[0];
                    $longitude = $coords[1];
                }
            }
        }

        return [
            'location_lat' => $latitude,
            'location_lon' => $longitude,
        ];
    }

    /** @inheritDoc */
    public function getFields()
    {
        return [
            'location_lat',
            'location_lon',
        ];
    }
}
