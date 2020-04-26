<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "support_group_language".
 *
 * @property int $id
 * @property int $support_group_id
 * @property string $language_code
 *
 * @property Language $language
 */
class SupportGroupLanguage extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'support_group_language';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['support_group_id'], 'required'],
            [['support_group_id'], 'integer'],
            [['language_code'], 'string', 'max' => 255],
            [
                ['language_code'],
                'exist',
                'targetClass'     => Language::class,
                'targetAttribute' => ['language_code' => 'code'],
            ],
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
    public function getLanguage()
    {
        return $this->hasOne(Language::className(), ['code' => 'language_code']);
    }
}
