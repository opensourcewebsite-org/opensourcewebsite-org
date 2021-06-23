<?php

namespace app\modules\bot\validators;

use yii\validators\Validator;

/**
 * Class StellarPublicKeyValidator
 *
 * @package app\modules\bot\validators
 */
class StellarPublicKeyValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        $result = $this->validateValue($value);

        if (!empty($result)) {
            $this->addError($model, $attribute, 'Public key has invalid format.');
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function validateValue($value): ?array
    {
        if (preg_match("/^G([a-zA-Z2-7]){55}$/", $value, $matches)) {
            return null;
        }

        return ['Public key has invalid format.', []];
    }
}
