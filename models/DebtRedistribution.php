<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "debt_redistribution".
 *
 * @property int      $id
 * @property int      $from_user_id
 * @property int      $to_user_id
 * @property int      $currency_id
 * @property int|null $max_amount   "NULL" - no limit - allow any amount. "0" - limit is 0, so deny to redistribute.
 * @property int      $priority     "1" - the highest. "0" - no priority. //todo [#294][priority]
 *
 * @property User $fromUser
 * @property User $toUser
 * @property Currency $currency
 */
class DebtRedistribution extends ActiveRecord
{
    public const MAX_AMOUNT_ANY  = null;
    public const MAX_AMOUNT_DENY = 0;

    public const PRIORITY_NO = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'debt_redistribution';
    }

    public function init()
    {
        $this->loadDefaultValues();
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['currency_id', 'required'],
            //todo [#294][priority]
//            ['priority', 'integer', 'min' => 0, 'max' => 255],
//            ['priority', 'default', 'value' => self::PRIORITY_NO],

            //max_amount:
            ['max_amount', 'number', 'min' => 0],
            ['max_amount', $this->fnFormatMaxAmount(), 'skipOnEmpty' => false],
            ['priority'  , 'default', 'value' => self::PRIORITY_NO],

            //db:
            [['from_user_id', 'to_user_id', 'currency_id'], 'unique', 'targetAttribute' => ['from_user_id', 'to_user_id', 'currency_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'           => 'ID',
            'from_user_id' => 'From User ID',
            'to_user_id'   => 'To User ID',
            'currency_id'  => 'Currency',
            'max_amount'   => 'Max Amount',
        ];
    }

    public function attributeHints()
    {
        return [
            'max_amount' => '<ul><li>"" (empty field) - no limit - allow any amount.</li><li>"0" - limit is 0, so deny to redistribute.</li></ul>',
            'priority'   => '<ul><li>"1" - the highest.</li><li>"0" - no priority.</li></ul>',
        ];
    }

    public function getFromUser()
    {
        return $this->hasOne(User::className(), ['id' => 'from_user_id']);
    }

    public function getToUser()
    {
        return $this->hasOne(User::className(), ['id' => 'to_user_id']);
    }

    public function getCurrency()
    {
        return $this->hasOne(Currency::className(), ['id' => 'currency_id']);
    }

    //todo [#294][priority]
//    public function isPriorityEmpty(): bool
//    {
//        return $this->priority == self::PRIORITY_NO;
//    }

    public function isMaxAmountAny(): bool
    {
        return $this->max_amount === self::MAX_AMOUNT_ANY;
    }

    public function isMaxAmountDeny(): bool
    {
        return !$this->isMaxAmountAny() && ($this->max_amount == self::MAX_AMOUNT_DENY);
    }

    private function fnFormatMaxAmount(): callable
    {
        return function () {
            $this->max_amount = $this->max_amount === '' ? null : $this->max_amount;
        };
    }
}
