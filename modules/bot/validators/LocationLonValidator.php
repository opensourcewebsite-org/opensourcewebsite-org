<?php

namespace app\modules\bot\validators;

use Yii;
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
            $this->addError($model, $attribute, Yii::t('bot', 'Input value for {attribute} must be between {min} and {max}'), [
                'attribute' => $model->getAttributeLabel($attribute),
                'min' => -180,
                'max' => 180,
            ]);
        }
    }

    public function validateLon($lon): bool
    {
        return (is_numeric($lon) && (doubleval($lon) >= -180) && (doubleval($lon) <= 180));
    }
}
