<?php

namespace app\modules\bot\components\crud\rules;

/**
 * Class CurrentUserFieldComponent
 *
 * @package app\modules\bot\components\rules
 */
class CurrentUserFieldComponent extends BaseFieldComponent implements FieldInterface
{
    public function prepare($text)
    {
        return $this->controller->getUser()->getId();
    }

    public function getFields()
    {
        return ['user_id'];
    }
}
