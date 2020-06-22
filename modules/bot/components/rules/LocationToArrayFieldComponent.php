<?php

namespace app\modules\bot\components\rules;

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
            $latitude = $this->telegramUser->location_lat;
            $longitude = $this->telegramUser->location_lon;
            if (!$latitude || !$longitude) {
                $location = $this->update->getMessage()->getLocation();
                $latitude = $location->getLatitude();
                $longitude = $location->getLongitude();
            }
        } else {
            $text = str_replace([';'], ' ', $text);
            $coords = explode(' ', $text);
            $latitude = $coords[0];
            $longitude = $coords[1] ?? null;
            if (!$longitude) {
                return [];
            }
        }

        return ['location_lat' => $latitude, 'location_lon' => $longitude];
    }

    /** @inheritDoc */
    public function getFields()
    {
        return ['location_lat', 'location_lon'];
    }
}
