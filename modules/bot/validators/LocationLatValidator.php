<?php

namespace app\modules\bot\validators;

use yii\validators\Validator;

/**
 * Class LocationLatValidator
 *
 * @package app\modules\bot\validators
 */
class LocationLatValidator extends Validator
{
    /** @inheritDoc */
    public function validateAttribute($model, $attribute)
    {
        if (!$this->validateLat($model->$attribute)){
            $this->addError($model, $attribute, 'Input value must be between {min} and {max}', [
                'min' => -90,
                'max' => 90,
            ]);
        }
    }

    public function validateLat($lat): bool
    {
        return  (is_numeric($lat)
            && (doubleval($lat) >= -90) && (doubleval($lat) <= 90));
    }
}
