<?php

namespace app\models;

use app\models\queries\ResumeQuery;
use app\models\User as GlobalUser;
use app\modules\bot\validators\RadiusValidator;
use Yii;
use app\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\conditions\AndCondition;

/**
 * Class Resume
 *
 * @package app\models
 */
class Resume extends ActiveRecord
{
    public const STATUS_OFF = 0;
    public const STATUS_ON = 1;

    public const LIVE_DAYS = 14;

    const REMOTE_OFF = 0;
    const REMOTE_ON = 1;

    /** @inheritDoc */
    public static function tableName()
    {
        return '{{%resume}}';
    }

    /** @inheritDoc */
    public function rules()
    {
        return [
            [
                [
                    'user_id',
                    'currency_id',
                    'status',
                    'created_at',
                    'renewed_at',
                    'processed_at',
                ],
                'integer',
            ],
            ['search_radius', RadiusValidator::class],
            [
                [
                    'min_hourly_rate',
                    'location_lat',
                    'location_lon',
                ],
                'double',
            ],
            [
                [
                    'name',
                ],
                'string',
                'max' => 256,
            ],
            [
                [
                    'experiences',
                    'expectations',
                    'skills',
                ],
                'string',
            ],
            [
                [
                    'user_id',
                    'currency_id',
                    'name',
                ],
                'required',
            ],
        ];
    }

    /**
     * @return ResumeQuery
     */
    public static function find()
    {
        return new ResumeQuery(get_called_class());
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'min_hourly_rate' => Yii::t('app', 'Min. hourly rate'),
            'remote_on' => Yii::t('bot', 'Remote work'),
            'search_radius' => Yii::t('bot', 'Search radius'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::class, ['id' => 'currency_id']);
    }

    /** @inheritDoc */
    public function behaviors()
    {
        return [
            'TimestampBehavior' => [
                'class' => TimestampBehavior::class,
            ],
        ];
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->status == self::STATUS_ON && (time() - $this->renewed_at) <= self::LIVE_DAYS * 24 * 60 * 60;
    }

    /**
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getMatches()
    {
        $query = Vacancy::find()->active()->languages();
        if ($this->min_hourly_rate) {
            $conditions = [];
            $conditions[] = ['>=', Vacancy::tableName() . '.max_hourly_rate', $this->min_hourly_rate];
            $conditions[] = [Vacancy::tableName() . '.currency_id' => $this->currency_id];
            $query->andWhere(new AndCondition($conditions));
        }

        return $query;
    }

    /**
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getAllMatches()
    {
        return $this->hasMany(Vacancy::className(), ['id' => 'vacancy_id'])
            ->viaTable('{{%job_match}}', ['resume_id' => 'id']);
    }

    public function updateMatches()
    {
        $this->unlinkAll('allMatches', true);
    }

    public function markToUpdateMatches()
    {
        if ($this->processed_at !== null) {
            $this->unlinkAll('matches', true);

            $this->setAttributes([
                'processed_at' => null,
            ]);
            $this->save();
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrencyRelation()
    {
        return $this->hasOne(Currency::class, ['id' => 'currency_id']);
    }

    /**
     * @return string
     */
    public function getCurrencyCode()
    {
        $currency = $this->currencyRelation;
        if ($currency) {
            $currencyCode = $currency->code;
        } else {
            $currencyCode = '';
        }

        return $currencyCode;
    }

    /**
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getGlobalUser()
    {
        return $this->hasOne(GlobalUser::className(), ['id' => 'user_id'])
            ->viaTable('{{%bot_user}}', ['id' => 'user_id']);
    }
}
