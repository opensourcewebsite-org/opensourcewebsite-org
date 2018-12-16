<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "support_group_command_text".
 *
 * @property int $id
 * @property int $support_group_command_id
 * @property string $language_code
 * @property string $text
 *
 * @property Language $languageCode
 * @property SupportGroupCommand $supportGroupCommand
 */
class SupportGroupCommandText extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'support_group_command_text';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['support_group_command_id', 'language_code', 'text'], 'required'],
            [['support_group_command_id'], 'integer'],
            [['text'], 'string'],
            [['language_code'], 'string', 'max' => 255],
            [['language_code'], 'exist', 'skipOnError' => true, 'targetClass' => Language::className(), 'targetAttribute' => ['language_code' => 'code']],
            [['support_group_command_id'], 'exist', 'skipOnError' => true, 'targetClass' => SupportGroupCommand::className(), 'targetAttribute' => ['support_group_command_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'support_group_command_id' => 'Support Group Command ID',
            'language_code' => 'Language Code',
            'text' => 'Text',
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
    public function getSupportGroupCommand()
    {
        return $this->hasOne(SupportGroupCommand::className(), ['id' => 'support_group_command_id']);
    }
}
