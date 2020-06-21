<?php

namespace app\modules\bot\components\rules;

use app\modules\bot\components\CrudController;

/**
 * Class LocationToArrayFieldComponent
 *
 * @package app\modules\bot\components\rules
 */
class LocationToArrayFieldComponent implements FieldInterface
{
    /** @var CrudController */
    public $controller;

    /**
     * LocationToArrayField constructor.
     *
     * @param $controller
     */
    public function __construct($controller)
    {
        $this->controller = $controller;
    }

    /** @inheritDoc */
    public function prepare($text)
    {
        if (!$text) {
            $latitude = $this->controller->getTelegramUser()->location_lat;
            $longitude = $this->controller->getTelegramUser()->location_lon;
        } else {
            $text = str_replace([';'], ' ', $text);
            $coords = explode(' ', $text);
            $latitude = $coords[0];
            $longitude = $coords[1] ?? null;
        }

        return ['location_lat' => $latitude, 'location_lon' => $longitude];
    }

    /** @inheritDoc */
    public function getFields()
    {
        return ['location_lat', 'location_lon'];
    }
}
