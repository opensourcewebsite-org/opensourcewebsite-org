<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_stellar_income".
 *
 * @property int $id
 * @property string $account_id
 * @property string $asset_code
 * @property float $income
 * @property int $created_at
 * @property int|null $processed_at
 * @property string|null $result_code
 */
class UserStellarIncome extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_stellar_income';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['account_id', 'asset_code', 'income', 'created_at'], 'required'],
            [['income'], 'number'],
            [['created_at', 'processed_at'], 'integer'],
            [['account_id', 'asset_code', 'result_code'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'account_id' => 'Account ID',
            'asset_code' => 'Asset Code',
            'income' => 'Income',
            'created_at' => 'Created At',
            'processed_at' => 'Processed At',
            'result_code' => 'Result Code',
        ];
    }
}
