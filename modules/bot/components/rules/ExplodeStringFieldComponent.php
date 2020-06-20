<?php

namespace app\modules\bot\components\rules;

use app\modules\bot\components\CrudController;

/**
 * Class ExplodeStringFieldComponent
 *
 * @package app\modules\bot\components\rules
 */
class ExplodeStringFieldComponent implements FieldInterface
{
    const MAIN_DELIMITER = '|';

    /** @var CrudController */
    public $controller;
    /** @var string[] */
    public $delimiters = [',', '.', "\n"];
    /** @var bool */
    public $shouldTrim = true;

    /**
     * LocationToArrayField constructor.
     *
     * @param $controller
     */
    public function __construct($controller)
    {
        $this->controller = $controller;
    }

    /** @inheritDoc */
    public function prepare($text)
    {
        $text = str_replace($this->delimiters, self::MAIN_DELIMITER, $text);
        $text = str_replace(self::MAIN_DELIMITER . self::MAIN_DELIMITER, self::MAIN_DELIMITER, $text);

        $array = explode(self::MAIN_DELIMITER, $text);

        if ($this->shouldTrim) {
            $array = array_map(
                function ($val) {
                    return trim($val);
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
