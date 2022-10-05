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
        if ($model->$attribute < 0) {
            $this->addError($model, $attribute, 'The radius must be either more then 0 and less then {max}.', [
                'max' => self::MAX_RADIUS,
            ]);
        } elseif ($model->$attribute > self::MAX_RADIUS) {
            $model->$attribute = self::MAX_RADIUS;
        }
    }
}
