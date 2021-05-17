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

    public const ROLE_OWNER = 1;
    public const ROLE_HR = 2;

    public static function getRoles(): array
    {
        return [
            self::ROLE_OWNER => Yii::t('app', 'Owner'),
            self::ROLE_HR => Yii::t('app', 'Hr')
        ];
    }

    public static function tableName(): string
    {
        return '{{%company_user}}';
    }

    public function rules(): array
    {
        return [
            [['user_id', 'company_id'], 'integer'],
            [['user_id', 'company_id'], 'required'],
            ['user_role', 'integer'],
            ['user_role', 'in', 'range' => array_keys(static::getRoles())],
            ['user_role', 'default', 'value' => static::ROLE_HR],
        ];
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasMany(User::class, ['id' => 'user_id']);
    }

    public function getCompany(): ActiveQuery
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }
}
