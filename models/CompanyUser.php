<?php

namespace app\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class CompanyUser
 *
 * @package app\models
 *
 * @property int $id
 * @property int $user_id
 * @property int $company_id
 * @property int $user_role
 *
 * @property User $user
 * @property Company $company
 */
class CompanyUser extends ActiveRecord
{
    public const ROLE_OWNER = 0;
    public const ROLE_HR = 1;

    public static function getRoles(): array
    {
        return [
            self::ROLE_OWNER => Yii::t('app', 'Owner'),
            self::ROLE_HR => Yii::t('app', 'Hr')
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%company_user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['user_id', 'company_id'], 'integer'],
            [['user_id', 'company_id'], 'required'],
            ['user_role', 'integer'],
            ['user_role', 'in', 'range' => array_keys(static::getRoles())],
            ['user_role', 'default', 'value' => static::ROLE_OWNER],
        ];
    }

    public function getRoleName(): string
    {
        return static::getRoles()[$this->user_role];
    }

    public function isOwner(): bool
    {
        return $this->user_role === static::ROLE_OWNER;
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getCompany(): ActiveQuery
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }
}
