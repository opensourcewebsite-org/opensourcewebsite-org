<?php

namespace app\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * Class Company
 *
 * @package app\models
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $url
 * @property string|null $address
 * @property string|null $description
 * @property int $updated_at
 * @property int $created_at
 *
 * @property User[] $members
 * @property Vacancy[] $vacancies
 *
 */
class Company extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%company}}';
    }

    public function behaviors(): array
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function rules(): array
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
                    if (!isset($parsedUrl['scheme'])) {
                        $parsedUrl['scheme'] = Yii::$app->params['defaultScheme'];
                    }
                    if ($parsedUrl['host'] ?? false) {
                        $parsedUrl['host'] = trim($parsedUrl['host'], " \t\n\r\0\x0B.");
                    }

                    return $parsedUrl['scheme'].'://'.$parsedUrl['host'].$parsedUrl['path'].'?'.$parsedUrl['query'];
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

    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'url' => Yii::t('app', 'Website'),
            'address' => Yii::t('app', 'Address'),
            'description' => Yii::t('app', 'Description'),
        ];
    }

    public function getMembers(): ActiveQuery
    {
        return $this->hasMany(User::class, ['id', 'user_id'])
            ->viaTable(CompanyUser::tableName(), ['company_id' => 'id']);
    }

    public function getVacancies(): ActiveQuery
    {
        return $this->hasMany(Vacancy::class, ['company_id' => 'id']);
    }
}
