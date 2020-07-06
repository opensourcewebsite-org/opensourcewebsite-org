<?php


namespace app\modules\bot\models;

use yii\db\ActiveRecord;

/**
 * Class JobMatch
 *
 * @package app\modules\bot\models
 */
class JobMatch extends ActiveRecord
{
    const TYPE_SELF = 0;
    const TYPE_THEY = 1;
    const TYPE_BOTH = 2;

    /** @inheritDoc */
    public static function tableName()
    {
        return '{{%job_match}}';
    }
}
