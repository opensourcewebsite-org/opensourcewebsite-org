<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "support_group_command".
 *
 * @property int $id
 * @property int $support_group_id
 * @property string $command
 * @property int $is_default
 * @property int $updated_at
 * @property int $updated_by
 * @property SupportGroupCommandText[] $reIndexTexts
 * @property SupportGroupLanguage[] $languages
 * @property string $languageCode
 *
 * @property SupportGroup $supportGroup
 * @property SupportGroupCommandText[] $supportGroupCommandTexts
 */
class SupportGroupCommand extends \yii\db\ActiveRecord
{
    public $reIndexTexts;

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
                'class'              => TimestampBehavior::className(),
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
            [
                ['support_group_id'],
                'exist',
                'targetClass' => SupportGroup::class,
                'targetAttribute' => ['support_group_id' => 'id'],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'               => 'ID',
            'support_group_id' => 'Support Group ID',
            'command'          => 'Command',
            'is_default'       => 'Is Default',
            'updated_at'       => 'Updated At',
            'updated_by'       => 'Updated By',
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
     * @return void
     */
    public function setLanguagesIndexes()
    {
        $this->reIndexTexts = ArrayHelper::index($this->supportGroupCommandTexts, 'language_code');
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

    /**
     * @return SupportGroupLanguage
     */
    public function getLanguages()
    {
        return $this->hasMany(SupportGroupLanguage::className(), ['support_group_id' => 'support_group_id']);
    }

    /**
     * @return array
     */
    public function getNavItems()
    {
        $navItems = [];

        foreach ($this->languages as $lang) {
            $navItems[] = [
                'label'       => $lang->language->name_ascii,
                'url'         => '#tab_' . $lang->id,
                'linkOptions' => [
                    'data-toggle' => 'tab',
                    'onclick'     => !ArrayHelper::keyExists($lang->language_code, $this->reIndexTexts)
                        ?
                        '$(\'#modalLanguage' . $lang->id . '\').modal();'
                        :
                        '',
                ],
            ];
        }

        return $navItems;
    }

    /**
     * @param bool $insert
     *
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
