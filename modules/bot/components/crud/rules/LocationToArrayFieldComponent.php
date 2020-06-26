<?php

namespace app\modules\bot\components\crud\rules;

/**
 * Class LocationToArrayFieldComponent
 *
 * @package app\modules\bot\components\rules
 */
class LocationToArrayFieldComponent extends BaseFieldComponent implements FieldInterface
{
    /** @inheritDoc */
    public function prepare($text)
    {
        if (!$text) {
            $message = $this->update->getMessage();
            if ($message) {
                $location = $message->getLocation();
                $latitude = $location->getLatitude();
                $longitude = $location->getLongitude();
            } else {
                $latitude = null;
                $longitude = null;
            }
            if (!$latitude || !$longitude) {
                $latitude = $this->telegramUser->location_lat;
                $longitude = $this->telegramUser->location_lon;
            }
        } else {
            $text = str_replace([';'], ' ', $text);
            $coords = explode(' ', $text);
            $latitude = $coords[0];
            $longitude = $coords[1] ?? null;
        }

        return ['location_lat' => (string)$latitude, 'location_lon' => (string)$longitude];
    }

    /** @inheritDoc */
    public function getFields()
    {
        return ['location_lat', 'location_lon'];
    }
}
