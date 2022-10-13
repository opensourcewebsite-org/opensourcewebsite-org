<?php

namespace app\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

class UserCitizenship extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%user_citizenship}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['user_id', 'country_id'], 'integer'],
            [['user_id', 'country_id'], 'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'country_id' => Yii::t('app', 'Citizenship'),
        ];
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, [ 'id' => 'user_id' ]);
    }

    public function getCountry(): ActiveQuery
    {
        return $this->hasOne(Country::class, [ 'id' => 'country_id' ]);
    }
}
