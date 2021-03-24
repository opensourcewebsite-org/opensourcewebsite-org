<?php

namespace app\modules\bot\validators;

use yii\validators\Validator;

/**
 * Class LocationLonValidator
 *
 * @package app\modules\bot\validators
 */
class LocationLonValidator extends Validator
{
    /** @inheritDoc */
    public function validateAttribute($model, $attribute)
    {
        if (!$this->validateLon($model->$attribute)) {
            $this->addError($model, $attribute, 'Input value must be between {min} and {max}', [
                'min' => -180,
                'max' => 180,
            ]);
        }
    }

    public function validateLon($lon): bool
    {
        return (is_numeric($lon)
            && (doubleval($lon) >= -180) && (doubleval($lon) <= 180));
    }
}
