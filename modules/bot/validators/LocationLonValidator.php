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
        if (!(is_numeric($model->$attribute)
            && (doubleval($model->$attribute) >= -180) && (doubleval($model->$attribute) <= 180))) {
            $this->addError($model, $attribute, 'Input value must be between {min} and {max}', [
                'min' => -180,
                'max' => 180,
            ]);
        }
    }
}
