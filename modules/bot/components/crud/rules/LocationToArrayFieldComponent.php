<?php

namespace app\modules\bot\components\crud\rules;

use app\modules\bot\components\helpers\LocationParser;
use Yii;

/**
 * Class LocationToArrayFieldComponent
 *
 * @package app\modules\bot\components\rules
 */
class LocationToArrayFieldComponent extends BaseFieldComponent implements FieldInterface
{
    /** @var string[] */
    public $delimiters = [' ', ', ', ',', ';', "\n"];

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
            [$latitude, $longitude] = (new LocationParser($text, $this->delimiters))->parse();
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
