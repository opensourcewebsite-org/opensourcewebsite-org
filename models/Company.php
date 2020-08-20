<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * Class Company
 *
 * @package app\models
 */
class Company extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%company}}';
    }

    /** @inheritDoc */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /** @inheritDoc */
    public function rules()
    {
        return [
            [
                [
                    'name',
                    'address',
                ],
                'string',
                'max' => 255,
            ],
            [
                ['url'],
                'filter',
                'skipOnEmpty' => true,
                'filter' => function ($value) {
                    $parsedUrl = parse_url($value);
                    if ($parsedUrl['host'] ?? false) {
                        $url = trim($parsedUrl['host'], " \t\n\r\0\x0B.");
                        if ($path = ($parsedUrl['path'] ?? '')) {
                            $url .= $path;
                        }
                        if ($query = ($parsedUrl['query'] ?? '')) {
                            $url .= '?' . $query;
                        }
                        $value = $url;
                    }

                    return $value;
                },
            ],
            [
                ['url'], 'url',
                'pattern' => '/^(?:(?:https?|ftp):\/\/|www\.)?[-a-z0-9+&@#\/%?=~_|!:,.;]+[.][a-zA-Z]{2,4}/i',
            ],
            [['description'], 'string'],
            [['name'], 'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'url' => Yii::t('app', 'Website'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getMembers()
    {
        return $this->hasMany(User::className(), ['id', 'user_id'])
            ->viaTable(CompanyUser::tableName(), ['company_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVacancies()
    {
        return $this->hasMany(Vacancy::className(), ['company_id' => 'id']);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        $url = $this->url;
        if ($url && !preg_match('|^https?:\/\/|', $url)) {
            $url = Yii::$app->params['defaultScheme'] . '://' . $url;
        }

        return $url;
    }
}
