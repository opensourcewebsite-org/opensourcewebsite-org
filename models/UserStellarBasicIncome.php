<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "user_stellar_basic_income".
 *
 * @property int $id
 * @property string $account_id
 * @property float $income
 * @property int $created_at
 * @property int|null $processed_at
 * @property string|null $result_code
 */
class UserStellarBasicIncome extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_stellar_basic_income}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['account_id', 'income'], 'required'],
            [['income'], 'number'],
            [['created_at', 'processed_at'], 'integer'],
            [['created_at'], 'default', 'value' => time()],
            [['account_id', 'result_code'], 'string', 'max' => 255],
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
            'income' => 'Income',
            'created_at' => 'Created At',
            'processed_at' => 'Processed At',
            'result_code' => 'Result Code',
        ];
    }
}
