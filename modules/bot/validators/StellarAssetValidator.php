<?php

namespace app\modules\bot\validators;

use yii\validators\Validator;

/**
 * Class StellarAssetValidator
 *
 * @package app\modules\bot\validators
 */
class StellarAssetValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        $result = $this->validateValue($value);

        if (!empty($result)) {
            $this->addError($model, $attribute, 'Asset has invalid format.');
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function validateValue($value)
    {
        if (preg_match("/^([a-zA-Z2-7]){1,12}$/", $value, $matches)) {
            return;
        }

        return ['Asset has invalid format.', []];
    }
}
