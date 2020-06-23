<?php


namespace app\modules\bot\components\rules;

/**
 * Class PhotoFieldComponent
 *
 * @package app\modules\bot\components\rules
 */
class PhotoFieldComponent extends BaseFieldComponent implements FieldInterface
{
    /** @inheritDoc */
    public function prepare($text)
    {
        $text = '';
        if (($message = $this->update->getMessage()) && $message->getPhoto()) {
            $photoFileId = $message->getPhoto()[0]->getFileId();
            $text = $photoFileId;
        }

        return $text;
    }

    /** @inheritDoc */
    public function getFields()
    {
        return ['file_id'];
    }
}
