<?php

namespace app\modules\bot\components\crud\rules;

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
        return $this->controller->getUser()->getId();
    }

    /** @inheritDoc */
    public function getFields()
    {
        return ['user_id'];
    }
}
