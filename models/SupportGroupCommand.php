<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "support_group_command".
 *
 * @property int $id
 * @property int $support_group_id
 * @property string $command
 * @property int $is_default
 * @property int $updated_at
 * @property int $updated_by
 *
 * @property SupportGroup $supportGroup
 * @property SupportGroupCommandText[] $supportGroupCommandTexts
 */
class SupportGroupCommand extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'support_group_command';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => false,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['support_group_id', 'command', 'is_default'], 'required'],
            [['support_group_id', 'is_default'], 'integer'],
            [['command'], 'unique', 'targetAttribute' => ['support_group_id', 'command']],
            [['command'], 'string', 'max' => 255],
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
            'command' => 'Command',
            'is_default' => 'Is Default',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
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
    public function getSupportGroupBot()
    {
        return $this->hasOne(SupportGroupBot::className(), ['support_group_id' => 'support_group_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSupportGroupCommandTexts()
    {
        return $this->hasMany(SupportGroupCommandText::className(), ['support_group_command_id' => 'id']);
    }


    public function getLanguage()
    {
        return SupportGroupLanguage::findAll(['support_group_id' => $this->support_group_id]);
    }

    /**
     * @param $text
     * @return array
     */
    public function getNavItems($text)
    {
        $navItems = [];

        foreach ($this->getLanguage() as $lang) {
            $navItems[] = [
                'label' => $lang->languageCode->name_ascii,
                'url' => '#tab_' . $lang->id,
                'linkOptions' => [
                    'data-toggle' => 'tab',
                    'onclick' => !isset($text[$lang->language_code]) ? 'document.getElementById(\'bottonModal' . $lang->id . '\').click();' : ''
                ]
            ];
        }

        return $navItems;
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->updated_by = Yii::$app->user->id;

            return true;
        }
        return false;
    }
}
