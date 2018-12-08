<?php

namespace app\models;

use app\components\Converter;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "setting".
 *
 * @property int $id
 * @property string $key
 * @property string $value
 * @property int $updated_at
 */
class Setting extends ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'setting';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['key', 'value'], 'required'],
            [['value'], 'string'],
            [['updated_at'], 'integer'],
            [['key'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'key' => 'Key',
            'value' => 'Value',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSettingValues()
    {
        return $this->hasMany(SettingValue::className(), ['setting_id' => 'id']);
    }

    /**
     * @return SettingValue
     */
    public function getDefaultSettingValue()
    {
        return SettingValue::find()->where(['setting_id' => $this->id, 'is_current' => 1])->one();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSettingValueVotes()
    {
        return $this->hasMany(SettingValueVote::className(), ['setting_id' => 'id']);
    }

    /**
     * @return SettingValueVote vote record of login user
     */
    public function getSettingValueUserVote()
    {
        $user_id = Yii::$app->user->id;

        return SettingValueVote::find()->where(['setting_id' => $this->id, 'user_id' => $user_id])->one();
    }

    /**
     * @return SettingValue all settings value except current value
     */
    public function getSettingValuesByDefault()
    {
        return SettingValue::find()->where(['setting_id' => $this->id, 'is_current' => 0])->all();
    }

    /**
     * Percentage of votes for all values of setiing except current value
     * @param bool $format whether to return formatted percent value or not
     * @return mixed Total votes percentage of a setting
     */
    public function getTotalVotesPercent($format = true)
    {
        $values = $this->getSettingValuesByDefault();
        $votes = 0;
        foreach ($values as $value) {
            $votes += $value->getUserVotesPercent();
        }
        if ($format) {
            $votes = Converter::formatNumber($votes);
        }
        return $votes;
    }

    /**
     * Percentage of vote for current value of setting
     * @param bool $format whether to return formatted percent value or not
     * @return mixed Percentage of vote for current value of setting
     */
    public function getCurrentValueVotes($format = true)
    {
        $remainingVotes = 100 - $this->getTotalVotesPercent(false);
        if ($format) {
            $remainingVotes = Converter::formatNumber($remainingVotes);
        }
        return $remainingVotes;
    }
}
