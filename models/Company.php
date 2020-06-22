<?php
namespace app\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * Class Company
 *
 * @package app\models
 */
class Company extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%company}}';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    public function rules()
    {
        return [
            [['name', 'address'], 'string', 'max' => 256],
            [['url'], 'url'],
            [['description'], 'string'],
            [['name'], 'required'],
        ];
    }

    public function getMembers()
    {
        return $this->hasMany(User::className(), ['id', 'user_id'])
            ->viaTable(CompanyUser::tableName(), ['company_id' => 'id']);
    }

    public function getVacancies()
    {
        return $this->hasMany(Vacancy::className(), ['company_id' => 'id']);
    }
}
