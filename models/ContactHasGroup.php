<?php

namespace app\models;

use yii\db\ActiveRecord;

class ContactHasGroup extends ActiveRecord
{
    public static function tableName()
    {
        return 'contact_has_group';
    }

    public function getContacts()
    {
        return $this->hasMany(Contact::class, ['id' => 'contact_id']);
    }

    public function getContact()
    {
        return $this->hasOne(Contact::className(), ['id' => 'contact_id']);
    }
}
