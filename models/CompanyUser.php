<?php

namespace app\models;

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

    public static function tableName(): string
    {
        return '{{%company_user}}';
    }

    public function rules(): array
    {
        return [
            [['user_id', 'company_id'], 'integer'],
            [['user_id', 'company_id'], 'required'],
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
