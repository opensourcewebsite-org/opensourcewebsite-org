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
        $latitude = null;
        $longitude = null;
        if (!$text) {
            $message = $this->update->getMessage();
            if ($message) {
                $location = $message->getLocation();
                $latitude = $location->getLatitude();
                $longitude = $location->getLongitude();
            }
            if (!$latitude || !$longitude) {
                $latitude = $this->telegramUser->location_lat;
                $longitude = $this->telegramUser->location_lon;
            }
        } else {
            $text = str_replace([';'], ' ', $text);
            $coords = explode(' ', $text);
            if (is_numeric($coords[0]) && is_numeric($coords[1] ?? null)) {
                $latitude = $coords[0];
                $longitude = $coords[1] ?? null;
            }
        }

        return ['location_lat' => (string)$latitude, 'location_lon' => (string)$longitude];
    }

    /** @inheritDoc */
    public function getFields()
    {
        return ['location_lat', 'location_lon'];
    }
}
