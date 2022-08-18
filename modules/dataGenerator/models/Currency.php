<?php

namespace app\modules\dataGenerator\models;

class Currency extends \app\models\Currency
{
    /** @var string|array list codes, that should be used by dataGenerator. Empty - will allow all */
    private const CODE_RANGE = 'USD';

    public static function find()
    {
        $query = parent::find();

        if (!empty(self::CODE_RANGE)) {
            $query->byCode(self::CODE_RANGE);
        }

        return $query;
    }
}
