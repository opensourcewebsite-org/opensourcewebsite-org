<?php
namespace app\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * Class Company
 * @package app\models
 * @property-read $vacancies
 * @property string $name
 * @property string $description
 * @property string $url
 * @property string $address
 * @property-read int $id
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
            TimestampBehavior::class,
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

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'url' => 'Website link',
        ]);
    }

    public function getMembers()
    {
        return $this->hasMany(User::class, ['id', 'user_id'])
            ->viaTable('company_user', ['company_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVacancies()
    {
        return $this->hasMany(Vacancy::class, ['company_id' => 'id']);
    }
}
