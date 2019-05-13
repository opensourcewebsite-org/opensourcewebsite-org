<?php

namespace app\models;

use app\components\Converter;
use Yii;

/**
 * This is the model class for table "setting_value".
 *
 * @property int $id
 * @property int $setting_id
 * @property string $value
 * @property int $updated_at
 *
 * @property Setting $setting
 * @property User $user
 * @property SettingValueVote[] $settingValueVotes
 */
class SettingValue extends \yii\db\ActiveRecord
{
    public $settingValueUserVote;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'setting_value';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['setting_id', 'value'], 'required'],
            [['setting_id', 'updated_at'], 'integer'],
            [['value'], 'string'],
            [['setting_id'], 'exist', 'skipOnError' => true, 'targetClass' => Setting::className(), 'targetAttribute' => ['setting_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'setting_id' => 'Setting ID',
            'value' => 'Value',
            'updated_at' => 'Last updated',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSetting()
    {
        return $this->hasOne(Setting::className(), ['id' => 'setting_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSettingValueVotes()
    {
        return $this->hasMany(SettingValueVote::className(), ['setting_value_id' => 'id']);
    }

    /**
     * @return SettingValueVote vote record of login user
     */
    public function getSettingValueUserVote()
    {
        $user_id = Yii::$app->user->id;

        return SettingValueVote::find()->where(['setting_value_id' => $this->id, 'user_id' => $user_id])->one();
    }

    /**
     * @param bool $format whether to return formatted percent value or not
     * @return mixed Votes percentage of a setting values
     */
    public function getUserVotesPercent($format = true)
    {
        $valueVotes = $this->settingValueVotes;
        $votes = 0;
        foreach ($valueVotes as $valueVote) {
            $votes += $valueVote->getVotesPercent();
        }
        if ($format) {
            $votes = Converter::formatNumber($votes);
        }
        return $votes;
    }
}
