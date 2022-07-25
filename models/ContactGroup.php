<?php

namespace app\models;

use yii\db\ActiveRecord;
use Yii;

class ContactGroup extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%contact_group}}';
    }

    public function rules()
    {
        return [
            [['name'], 'trim'],
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique', 'filter' => ['user_id' => Yii::$app->user->identity->id]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'name' => Yii::t('app', 'Name'),
        ];
    }

    public function getContactsWithGroup()
    {
        return $this->hasMany(ContactHasGroup::class, ['contact_group_id' => 'id']);
    }
}
