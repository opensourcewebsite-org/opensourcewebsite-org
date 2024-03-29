<?php

declare(strict_types=1);

namespace app\models;

use app\helpers\UrlTrimmer;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

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
 * @property User[] $users
 * @property Vacancy[] $vacancies
 *
 */
class Company extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%company}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['name'], 'required'],
            [['name', 'address', 'url'], 'string', 'max' => 255],
            [
                [
                    'url',
                ],
                'filter',
                'skipOnEmpty' => true,
                'filter' => [
                    new UrlTrimmer(),
                    'trim',
                ],
            ],
            [
                [
                    'url',
                ],
                'url',
                'defaultScheme' => Yii::$app->params['defaultScheme'] ?? 'https',
            ],
            [['description'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'name' => Yii::t('app', 'Name'),
            'url' => Yii::t('app', 'Website'),
            'address' => Yii::t('app', 'Address'),
            'description' => Yii::t('app', 'Description'),
        ];
    }

    public function getUsers(): ActiveQuery
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->viaTable(CompanyUser::tableName(), ['company_id' => 'id']);
    }

    public function getVacancies(): ActiveQuery
    {
        return $this->hasMany(Vacancy::class, ['company_id' => 'id']);
    }

    public function getCompanyUser(): ActiveQuery
    {
        return $this->hasMany(CompanyUser::class, ['company_id' => 'id']);
    }
}
