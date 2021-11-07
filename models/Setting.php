<?php

namespace app\models;

use app\components\Converter;
use Yii;
use yii\db\ActiveRecord;
use app\models\queries\SettingQuery;

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
    public static array $settings = [
        'issue_quantity_value_per_one_rating' => [
            'type' => 'float',
            'default' => 3,
            'more' => 0,
        ],
        'days_count_to_calculate_active_rating' => [
            'type' => 'integer',
            'default' => 30,
            'more' => 0,
        ],
        'moqup_quantity_value_per_one_rating' => [
            'type' => 'float',
            'default' => 3,
            'more' => 0,
        ],
        'moqup_html_field_max_value' => [
            'type' => 'integer',
            'default' => 100000,
            'more' => 0,
        ],
        'moqup_css_field_max_value' => [
            'type' => 'integer',
            'default' => 100000,
            'more' => 0,
        ],
        'issue_text_field_max_value' => [
            'type' => 'integer',
            'default' => 10000,
            'more' => 0,
        ],
        'website_setting_min_vote_percent_to_apply_change' => [
            'type' => 'float',
            'default' => 70,
            'more' => 0,
            'max' => 100,
        ],
        'support_group_quantity_value_per_one_rating' => [
            'type' => 'float',
            'default' => 1,
            'more' => 0,
        ],
        'support_group_bot_quantity_value_per_one_rating' => [
            'type' => 'float',
            'default' => 1,
            'more' => 0,
        ],
        'support_group_member_quantity_value_per_one_rating' => [
            'type' => 'float',
            'default' => 1,
            'more' => 0,
        ],
        'bot_group_join_hider_quantity_value_per_one_rating' => [
            'type' => 'float',
            'default' => 1,
            'more' => 0,
        ],
        'bot_group_join_captcha_quantity_value_per_one_rating' => [
            'type' => 'float',
            'default' => 1,
            'more' => 0,
        ],
        'bot_group_greeting_quantity_value_per_one_rating' => [
            'type' => 'float',
            'default' => 1,
            'more' => 0,
        ],
        'bot_group_filter_quantity_value_per_one_rating' => [
            'type' => 'float',
            'default' => 1,
            'more' => 0,
        ],
        'bot_group_faq_quantity_value_per_one_rating' => [
            'type' => 'float',
            'default' => 1,
            'more' => 0,
        ],
        'bot_group_stellar_quantity_value_per_one_rating' => [
            'type' => 'float',
            'default' => 1,
            'more' => 0,
        ],
        'company_quantity_value_per_one_rating' => [
            'type' => 'float',
            'default' => 1,
            'more' => 0,
        ],
        'active_vacancy_quantity_value_per_one_rating' => [
            'type' => 'float',
            'default' => 1,
            'more' => 0,
        ],
        'active_vacancy_min_quantity_value_per_one_user' => [
            'type' => 'integer',
            'default' => 2,
            'more' => 0,
        ],
        'active_resume_quantity_value_per_one_rating' => [
            'type' => 'float',
            'default' => 1,
            'more' => 0,
        ],
        'active_resume_min_quantity_value_per_one_user' => [
            'type' => 'integer',
            'default' => 2,
            'more' => 0,
        ],
        'active_currency_exchange_order_quantity_value_per_one_rating' => [
            'type' => 'float',
            'default' => 1,
            'more' => 0,
        ],
        'active_currency_exchange_order_min_quantity_value_per_one_user' => [
            'type' => 'integer',
            'default' => 2,
            'more' => 0,
        ],
        'active_ad_offer_quantity_value_per_one_rating' => [
            'type' => 'float',
            'default' => 1,
            'more' => 0,
        ],
        'active_ad_offer_min_quantity_value_per_one_user' => [
            'type' => 'integer',
            'default' => 2,
            'more' => 0,
        ],
        'active_ad_search_quantity_value_per_one_rating' => [
            'type' => 'float',
            'default' => 1,
            'more' => 0,
        ],
        'active_ad_search_min_quantity_value_per_one_user' => [
            'type' => 'integer',
            'default' => 2,
            'more' => 0,
        ],
        'basic_income_min_rating_value_to_activate' => [
            'type' => 'integer',
            'default' => 1000,
            'min' => 1,
        ],
    ];

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
            [['key'], 'required'],
            [['updated_at'], 'integer'],
            [['key'], 'string', 'length' => [2, 255]],
            ['key', 'filter', 'filter' => 'strtolower'],
            [
                'key',
                'match',
                'pattern' => '/(?:^(?:[A-Za-z][_]{0,1})*[A-Za-z]$)/i',
                'message' => 'Key can contain only letters and _ symbols.',
            ],
//            ['key', 'validateKey'],
            [['value'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'key' => Yii::t('app', 'Key'),
            'value' => Yii::t('app', 'Value'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    public static function find()
    {
        return new SettingQuery(get_called_class());
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSettingValues()
    {
        return $this->hasMany(SettingValue::className(), ['setting_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSettingValueVotes()
    {
        return $this->hasMany(SettingValueVote::className(), ['setting_id' => 'id']);
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
                'setting_id' => $this->id,
                'user_id' => $userId,
            ])
            ->one();
    }

    /**
     * @return SettingValue all settings value except current value
     */
    public function getSettingValuesByDefault()
    {
        return SettingValue::find()
            ->where([
                'setting_id' => $this->id,
            ])
            ->andWhere([
                '!=', 'value', $this->value,
            ])
            ->all();
    }

    /**
     * Percentage of votes for all values of setiing except current value
     * @param bool $format whether to return formatted percent value or not
     *
     * @return mixed Total votes percentage of a setting
     */
    public function getTotalVotesPercent($format = true)
    {
        $values = $this->getSettingValuesByDefault();
        $votes = 0;

        foreach ($values as $value) {
            $votes += $value->getVotesPercent();
        }

        if ($format) {
            $votes = Converter::formatNumber($votes);
        }

        return $votes;
    }

    /**
     * Percentage of vote for current value of setting
     * @param bool $format whether to return formatted percent value or not
     *
     * @return mixed Percentage of vote for current value of setting
     */
    public function getVotesCount($format = true)
    {
        $remainingVotes = 100 - $this->getTotalVotesPercent(false);

        if ($format) {
            $remainingVotes = Converter::formatNumber($remainingVotes);
        }

        return $remainingVotes;
    }

    // TODO remove old code
    public static function getValue($key)
    {
        $setting = static::findOne(['key' => $key]);

        if (!$setting && isset(self::$settings[$key]['default'])) {
            $setting = new self();

            $setting->setAttributes([
                'key' => $key,
                'value' => self::$settings[$key]['default'],
                'updated_at' => time(),
            ]);

            $setting->save();
        }

        return $setting->value;
    }

    public function getValidationRules()
    {
        return self::$settings[$this->key] ?? null;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getDefault($name)
    {
        return self::$settings[$name]['default'] ?? null;
    }

    public function getMin($name)
    {
        return self::$settings[$name]['min'] ?? null;
    }
}
