<?php

namespace app\modules\bot\components\rules;

/**
 * Class ExplodeStringFieldComponent
 *
 * @package app\modules\bot\components\rules
 */
class ExplodeStringFieldComponent extends BaseFieldComponent implements FieldInterface
{
    const MAIN_DELIMITER = '|';

    /** @var string[] */
    public $delimiters = [',', '.', "\n"];
    /** @var bool */
    public $shouldTrim = true;

    /** @inheritDoc */
    public function prepare($text)
    {
        if (!$text) {
            return [];
        }
        $text = str_replace($this->delimiters, self::MAIN_DELIMITER, $text);
        $text = str_replace(self::MAIN_DELIMITER . self::MAIN_DELIMITER, self::MAIN_DELIMITER, $text);

        $array = explode(self::MAIN_DELIMITER, $text);

        if ($this->shouldTrim) {
            $array = array_map(
                function ($val) {
                    return mb_strtolower(trim($val));
                },
                $array
            );
        }

        return $array;
    }

    /** @inheritDoc */
    public function getFields()
    {
        return [];
    }
}
