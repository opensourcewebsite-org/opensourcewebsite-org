<?php

namespace app\modules\bot\components\crud\rules;

/**
 * Interface FieldInterface
 *
 * @package app\modules\bot\components\rules
 */
interface FieldInterface
{
    /**
     * @param string|null $text
     *
     * @return string
     */
    public function prepare($text);

    /**
     * @return string[]
     */
    public function getFields();
}
