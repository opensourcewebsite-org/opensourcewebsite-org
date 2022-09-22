<?php

namespace app\modules\bot\validators;

use yii\validators\Validator;

/**
 * Class RadiusValidator
 *
 * @package app\modules\bot\validators
 */
class RadiusValidator extends Validator
{
    public const MAX_RADIUS = 1000;

    /** @inheritDoc */
    public function validateAttribute($model, $attribute)
    {
        if ($model->$attribute < 0 || $model->$attribute > self::MAX_RADIUS) {
            $this->addError($model, $attribute, 'Input value must be between {min} and {max}', [
                'min' => 0,
                'max' => self::MAX_RADIUS,
            ]);
        }
    }
    
}
