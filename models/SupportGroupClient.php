<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "support_group_client".
 *
 * @property int $id
 * @property int $support_group_id
 * @property string $language_code
 *
 * @property Language $languageCode
 * @property SupportGroup $supportGroup
 * @property SupportGroupClientBot[] $supportGroupClientBots
 * @property SupportGroupInsideMessage[] $supportGroupInsideMessages
 * @property SupportGroupOutsideMessage[] $supportGroupOutsideMessages
 */
class SupportGroupClient extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'support_group_client';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['support_group_id', 'language_code'], 'required'],
            [['support_group_id'], 'integer'],
            [['language_code'], 'string', 'max' => 255],
            [['language_code'], 'exist', 'skipOnError' => true, 'targetClass' => Language::className(), 'targetAttribute' => ['language_code' => 'code']],
            [['support_group_id'], 'exist', 'skipOnError' => true, 'targetClass' => SupportGroup::className(), 'targetAttribute' => ['support_group_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'support_group_id' => 'Support Group ID',
            'language_code' => 'Language Code',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLanguageCode()
    {
        return $this->hasOne(Language::className(), ['code' => 'language_code']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSupportGroup()
    {
        return $this->hasOne(SupportGroup::className(), ['id' => 'support_group_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSupportGroupClientBots()
    {
        return $this->hasMany(SupportGroupClientBot::className(), ['support_group_client_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSupportGroupInsideMessages()
    {
        return $this->hasMany(SupportGroupInsideMessage::className(), ['support_group_client_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSupportGroupOutsideMessages()
    {
        return $this->hasMany(SupportGroupOutsideMessage::className(), ['support_group_client_id' => 'id']);
    }
}
