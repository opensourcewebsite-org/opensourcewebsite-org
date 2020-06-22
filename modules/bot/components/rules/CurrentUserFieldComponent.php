<?php

namespace app\modules\bot\components\rules;

/**
 * Class CurrentUserFieldComponent
 *
 * @package app\modules\bot\components\rules
 */
class CurrentUserFieldComponent extends BaseFieldComponent implements FieldInterface
{
    /** @inheritDoc */
    public function prepare($text)
    {
        return $this->user->id;
    }

    /** @inheritDoc */
    public function getFields()
    {
        return ['user_id'];
    }
}
