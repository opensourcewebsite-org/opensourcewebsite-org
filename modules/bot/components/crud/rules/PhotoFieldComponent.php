<?php

namespace app\modules\bot\components\crud\rules;

/**
 * Class PhotoFieldComponent
 *
 * @package app\modules\bot\components\rules
 */
class PhotoFieldComponent extends BaseFieldComponent implements FieldInterface
{
    public function prepare($text)
    {
        $text = '';

        if (($message = $this->getUpdate()->getMessage()) && $message->getPhoto()) {
            $photoFileId = $message->getPhoto()[0]->getFileId();
            $text = $photoFileId;
        }

        return $text;
    }

    public function getFields()
    {
        return ['file_id'];
    }
}
