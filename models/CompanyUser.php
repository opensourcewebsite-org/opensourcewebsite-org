<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Class CompanyUser
 *
 * @package app\models
 */
class CompanyUser extends ActiveRecord
{
    /** @inheritDoc */
    public static function tableName()
    {
        return '{{%company_user}}';
    }

    /** @inheritDoc */
    public function rules()
    {
        return [
            [['user_id', 'company_id'], 'integer'],
            [['user_id', 'company_id'], 'required'],
        ];
    }
}
