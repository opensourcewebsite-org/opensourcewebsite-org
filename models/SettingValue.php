<?php

namespace app\models;

use app\components\Converter;
use Yii;
use app\models\queries\SettingValueQuery;

/**
 * This is the model class for table "setting_value".
 *
 * @property int $id
 * @property int $setting_id
 * @property string $value
 *
 * @property Setting $setting
 * @property User $user
 * @property SettingValueVote[] $settingValueVotes
 */
class SettingValue extends \yii\db\ActiveRecord
{
    public $settingValueUserVote;

    protected $rating = null;

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
            [['setting_id'], 'integer'],
            ['value', 'trim'],
            ['value', 'validateValue'],
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
            'value' => Yii::t('app', 'Value'),
        ];
    }


    /**
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateValue($attribute, $params)
    {
        $rules = $this->getValidationRules();

        if ($rules) {
            if (isset($rules['type'])) {
                switch ($rules['type']) {
                    case 'integer':
                        $this->value = intval($this->value);
                        if (!is_int($this->value)) {
                            $this->addError('value', 'Value must be an integer.');
                        }
                    break;
                    case 'float':
                        $this->value = floatval($this->value);
                        if (!is_float($this->value)) {
                            $this->addError('value', 'Value must be a number.');
                        }
                    break;
                }
            }

            if (isset($rules['min'])) {
                if ($this->value < $rules['min']) {
                    $this->addError('value', 'Value must be no less than ' . $rules['min'] . '.');
                }
            }

            if (isset($rules['max'])) {
                if ($this->value > $rules['max']) {
                    $this->addError('value', 'Value must be no greater than ' . $rules['max'] . '.');
                }
            }

            if (isset($rules['less'])) {
                if ($this->value >= $rules['less']) {
                    $this->addError('value', 'Value must be less than ' . $rules['less'] . '.');
                }
            }

            if (isset($rules['more'])) {
                if ($this->value <= $rules['more']) {
                    $this->addError('value', 'Value must be greater than ' . $rules['more'] . '.');
                }
            }
        }
    }

    public function getValidationRules()
    {
        return $this->setting->getValidationRules();
    }

    public static function find()
    {
        return new SettingValueQuery(get_called_class());
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
     * @param int|null $userId
     *
     * @return SettingValueVote
     */
    public function getSettingValueVoteByUserId($userId = null)
    {
        if (!$userId) {
            $userId = Yii::$app->user->id;
        }

        return SettingValueVote::find()
            ->where([
                'setting_value_id' => $this->id,
                'user_id' => $userId,
            ])
            ->one();
    }

    /**
     * @return mixed Votes percentage of a setting values
     */
    public function getVotesPercent()
    {
        $totalRating = User::getTotalRating();

        return Converter::percentage($this->getRating(), $totalRating, false);
    }

    public function isCurrent()
    {
        return $this->value == $this->setting->value;
    }

    /**
     * @return integer value rating
     */
    public function getRating()
    {
        if (is_null($this->rating)) {
            $this->rating = 0;

            foreach ($this->settingValueVotes as $settingValueVote) {
                $this->rating += $settingValueVote->getRating();
            }
        }

        return $this->rating;
    }

    /**
     * @param integer $userId
     */
    public function setVoteByUserId($userId)
    {
        $vote = SettingValueVote::find()
            ->where([
                'setting_id' => $this->setting_id,
                'user_id' => $userId,
            ])
            ->one();
        // delete another user vote
        if ($vote && ($vote->getSettingValueId() != $this->id)) {
            $valueId = $vote->getSettingValueId();
            $vote->delete();

            $existVotes = SettingValueVote::find()
                ->where([
                    'setting_id' => $this->setting_id,
                    'setting_value_id' => $valueId,
                ])
                ->exists();
            // delete the value without votes
            if (!$existVotes) {
                SettingValue::deleteAll([
                    'id' => $valueId,
                ]);
            }
        }

        $vote = new SettingValueVote([
            'setting_id' => $this->setting_id,
            'setting_value_id' => $this->id,
            'user_id' => $userId,
        ]);

        if ($vote->save() && array_key_exists($this->setting->key, Setting::$settings)) {
            //Make the voted setting value as current setting value, if it reach a threshhold of setting value 'website_setting_min_vote_percent_to_apply_change'
            try {
                $threshHold = Yii::$app->settings->website_setting_min_vote_percent_to_apply_change;

                if ($threshHold < $this->getVotesPercent()) {
                    $this->setting->value = $this->value;
                    $this->setting->updated_at = time();
                    $this->setting->save();
                }
            } catch (\Exception $e) {
                Yii::warning($e);
            }
        }

        return $vote;
    }

    public function getSettingId()
    {
        return $this->setting_id;
    }
}
