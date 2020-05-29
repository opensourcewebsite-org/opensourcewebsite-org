<?php

namespace app\models;

use yii\db\ActiveRecord;
use Yii;
use yii2tech\ar\position\PositionBehavior;

class ContactGroup extends ActiveRecord
{
    public static function tableName()
    {
        return 'contact_group';
    }


    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'unique', 'filter' => ['user_id' => Yii::$app->user->identity->id]],
            [['name'], 'string', 'max' => 255],
            [['name'], 'validateHasEmptyGroup', 'when' => function () {
                return $this->isNewRecord;
            },],
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => PositionBehavior::class,
            ]
        ];
    }

    /*
     * validate count empty groups
     */
    public function validateHasEmptyGroup()
    {
        if (Yii::$app->user->identity->hasEmptyContactGroup()) {
            $this->addError('name', 'You already have an empty group!');
        }
    }

    public function getContactsWithGroup()
    {
        return $this->hasMany(ContactHasGroup::class, ['contact_group_id' => 'id']);
    }

    public function beforeSave($insert)
    {
        parent::beforeSave($insert);

        if (!parent::beforeSave($insert)) {
            return false;
        }

        $this->user_id = Yii::$app->user->identity->id;

        return true;
    }
}
