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
        if (!(is_numeric($model->$attribute)
            && (doubleval($model->$attribute) >= -90) && (doubleval($model->$attribute) <= 90))) {
            $this->addError($model, $attribute, 'Input value must be between {min} and {max}', [
                'min' => -90,
                'max' => 90,
            ]);
        }
    }
}
