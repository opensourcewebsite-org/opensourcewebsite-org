<?php

namespace app\models;

use app\models\queries\ContactQuery;
use app\models\queries\DebtRedistributionQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "debt_redistribution".
 *
 * @property int      $id
 * @property int      $from_user_id
 * @property int      $to_user_id
 * @property int      $currency_id
 * @property int|null $max_amount   "NULL" - no limit - allow any amount. "0" - limit is 0, so deny to redistribute.
 *
 * @property User $fromUser
 * @property User $toUser
 * @property Currency $currency
 * @property Contact $contact
 */
class DebtRedistribution extends ActiveRecord
{
    /** @var null no limit - allow any amount. */
    public const MAX_AMOUNT_ANY  = null;
    /** @var int limit is 0, so deny to redistribute. Default value. */
    public const MAX_AMOUNT_DENY = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'debt_redistribution';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['currency_id', 'required'],

            //max_amount:
            ['max_amount', 'number', 'min' => 0],
            ['max_amount', $this->fnFormatMaxAmount(), 'skipOnEmpty' => false],

            //db:
            [['from_user_id', 'to_user_id', 'currency_id'], 'unique', 'targetAttribute' => ['from_user_id', 'to_user_id', 'currency_id']],
            ['currency_id', 'exist', 'targetRelation' => 'currency'],
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

    /**
     * @return DebtRedistributionQuery
     */
    public static function find()
    {
        return new DebtRedistributionQuery(get_called_class());
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

    public function getContact(): ContactQuery
    {
        return $this->hasOne(Contact::className(), [
            'user_id'      => 'from_user_id',
            'link_user_id' => 'to_user_id',
        ]);
    }

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
