<?php

namespace app\models;

use app\helpers\Number;
use app\interfaces\UserRelation\ByDebtInterface;
use app\interfaces\UserRelation\ByDebtTrait;
use app\models\queries\DebtBalanceQuery;
use app\models\traits\FloatAttributeTrait;
use app\models\traits\SelectForUpdateTrait;
use app\components\debt\Reduction;
use app\components\debt\Redistribution;
use yii\base\Exception;
use yii\base\InvalidCallException;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;

/**
 * You can validate DB for data collisions - {@see \app\commands\DebtController::actionCheckBalance()}
 *
 * @property int $currency_id
 * @property int $from_user_id          {@see Debt::$from_user_id}
 * @property int $to_user_id            {@see Debt::$to_user_id}
 * @property float $amount              $amount = sumOfAllCredits - sumOfAllDeposits. Always ($amount > 0) is true
 * @property int|null $reduction_try_at NULL      - this row is waiting for cron {@see \app\components\debt\Reduction}.
 *                                                  Because amount has been changed.
 *                                      TIMESTAMP - {@see Reduction} will not try to reduce it.
 *                                                  Because it has already tried to do that. It will try again, when
 *                                                  `amount` will be changed and become `reduction_try_at IS NULL`.
 * @property int $redistribute_try_at   TIMESTAMP - when {@see Redistribution} tried to resolve it.
 *                                      0         - default. I.e. this is new row, and `Redistribution` have never tried it.
 *
 * @property Currency      $currency
 * @property User          $fromUser
 * @property User          $toUser
 * @property DebtBalance[] $chainMembers
 * @property DebtBalance   $chainMemberParent   you should not use this relation for SQL query. It's only purpose -
 *                                              to be used as `inverseOf`
 *                                              for relation {@see DebtBalance::getChainMembers()}
 *                                              in {@see Reduction::reduceCircledChainAmount()}
 */
class DebtBalance extends ActiveRecord implements ByDebtInterface
{
    use ByDebtTrait;
    use SelectForUpdateTrait;
    use FloatAttributeTrait;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%debt_balance}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['from_user_id', 'to_user_id', 'currency_id', 'amount'], 'required'],
            [['from_user_id', 'to_user_id', 'currency_id'], 'integer'],
            [
                'amount',
                'double',
                'min' => -9999999999999.99,
                'max' => 9999999999999.99,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'currency_id'  => 'Currency ID',
            'from_user_id' => 'From User ID',
            'to_user_id' => 'To User ID',
            'amount' => 'Amount',
            'reduction_try_at' => 'Reduction Try At',
        ];
    }

    /**
     * {@inheritdoc}
     * @return DebtBalanceQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new DebtBalanceQuery(get_called_class());
    }

    /**
     * Gets query for [[Currency]].
     *
     * @return \yii\db\ActiveQuery|\app\models\queries\CurrencyQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::className(), ['id' => 'currency_id']);
    }

    /**
     * @return \yii\db\ActiveQuery|DebtBalanceQuery
     */
    public function getChainMembers()
    {
        return $this->hasMany(self::className(), [
            'currency_id'  => 'currency_id',
            'from_user_id' => 'to_user_id',
        ])->inverseOf('chainMemberParent');
    }

    /**
     * @return \yii\db\ActiveQuery|DebtBalanceQuery
     */
    public function getChainMemberParent()
    {
        /** @var [] $link empty array is not bug. {@see DebtBalance::$chainMemberParent} */
        $link = [];
        return $this->hasOne(self::className(), $link);
    }

    /**
     * {@inheritdoc}
     * @throws NotSupportedException
     */
    public static function updateAll($attributes, $condition = '', $params = [])
    {
        self::requireTransaction();

        return parent::updateAll($attributes, $condition, $params);
    }

    /**
     * {@inheritdoc}
     * @throws NotSupportedException
     */
    public static function deleteAll($condition = null, $params = [])
    {
        self::requireTransaction();

        return parent::deleteAll($condition, $params);
    }

    public function update($runValidation = true, $attributeNames = null)
    {
        if (Number::isFloatEqual(0, $this->amount)) {
            return (bool)$this->delete();
        }

        self::requireTransaction();

        return parent::update($runValidation, $attributeNames);
    }

    public function insert($runValidation = true, $attributes = null)
    {
        if (Number::isFloatEqual(0, $this->amount)) {
            return true;
        }

        return parent::insert($runValidation, $attributes);
    }

    public function beforeSave($insert)
    {
        if (!$insert && ($this->getOldAttribute('amount') > 0) && ($this->amount <= 0)) {
            $this->reduction_try_at = null;
            $this->redistribute_try_at = null;
        }

        return parent::beforeSave($insert);
    }

    /**
     * @throws Exception
     */
    public function afterRedistribution(): void
    {
        //SELECT FOR UPDATE and transaction is not necessary for this particular field.
        //So we can simply use raw SQL to avoid transaction validation
        $this->redistribute_try_at = time();
        //row $this may no longer exist in DB on this step. It's ok.
        static::getDb()
            ->createCommand()
            ->update(static::tableName(), ['redistribute_try_at' => $this->redistribute_try_at], $this->primaryKey)
            ->execute();
    }

    /**
     * @throws Exception
     */
    public static function setReductionTryAt(self $balance): ?self
    {
        $balance = self::findOneForUpdate($balance);

        if (!$balance) {
            return null; //it became zero or changed direction. This Balance can't be updated anymore.
        }

        $balance->reduction_try_at = time();
        $balance->save();

        return $balance;
    }

    public function hasRedistributionConfig(): bool
    {
        return (
            $this->toContact &&
            !$this->toContact->isDebtRedistributionPriorityDeny() &&
            $this->toDebtRedistribution &&
            !$this->toDebtRedistribution->isMaxAmountDeny()
        );
    }

    public function getCounterDebtBalance()
    {
        return $this->hasOne(self::className(), [
            'currency_id' => 'currency_id',
            'from_user_id' => 'to_user_id',
            'to_user_id' => 'from_user_id',
        ]);
    }
}
