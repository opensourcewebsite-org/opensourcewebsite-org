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

        if (isset($this->config['fieldNames'])) {
            return [
                $this->config['fieldNames'][0] => $latitude,
                $this->config['fieldNames'][1] => $longitude,
            ];
        } else {
            return [
                'location_lat' => $latitude,
                'location_lon' => $longitude,
            ];
        }
    }

    public function getFields()
    {
        if (isset($this->config['fieldNames'])) {
            return $this->config['fieldNames'];
        } else {
            return [
                'location_lat',
                'location_lon',
            ];
        }
    }
}
