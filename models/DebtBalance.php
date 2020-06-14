<?php

namespace app\models;

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
 * This is the model class for table "debt_balance".
 * on {@see Debt::EVENT_AFTER_CONFIRMATION} - script automatically recalculate balance amount between these two users
 *
 * You can validate DB for data collisions - {@see \app\commands\DebtController::actionCheckBalance()}
 *
 * @property int $currency_id
 * @property int $from_user_id          {@see Debt::$from_user_id}
 * @property int $to_user_id            {@see Debt::$to_user_id}
 * @property float $amount              $amount = sumOfAllCredits - sumOfAllDeposits. Always ($amount > 0) is true
 * @property int|null $processed_at     TIMESTAMP - this row is waiting for cron {@see \app\components\debt\Reduction}.
 *                                                  Because amount has been changed.
 *                                      NULL      - {@see Reduction} will not try to reduce it.
 *                                                  Because it has already tried to do that. It will try again, when
 *                                                  `amount` will be changed and `processed_at` will be set.
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
     * Should script store zero amount in DB.
     *      FALSE - system will expect, that there are no row, where `debt_balance.amount = 0`.
     *           No sense to store zero. Even more - if we will not store zero - system will be more optimized:
     *           SELECT queries, when searching for DebtDeduction chain, will work faster {@see Reduction}
     *      TRUE  - system will not delete rows where `amount = 0`. Be careful - this option was not tested deeply.
     *           May require some fixes.
     * WARNING: be careful to switch it's value. Highly recommended to clear tables `debt_balance` and `debt` before
     * switching. Or use migrations to fix `debt_balance`.
     */
    public const STORE_EMPTY_AMOUNT = false;

    /** @var bool {@see DebtBalance::requireAllowExecute()} */
    private static $allowExecute = false;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'debt_balance';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'currency_id'  => 'Currency ID',
            'from_user_id' => 'From User ID',
            'to_user_id'   => 'To User ID',
            'amount'       => 'Amount',
            'processed_at' => 'Processed At',
        ];
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
     * @return DebtBalanceQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new DebtBalanceQuery(get_called_class());
    }

    /**
     * {@inheritdoc}
     * @throws NotSupportedException
     */
    public static function updateAll($attributes, $condition = '', $params = [])
    {
        self::requireAllowExecute();

        return parent::updateAll($attributes, $condition, $params);
    }

    /**
     * {@inheritdoc}
     * @throws NotSupportedException
     */
    public static function deleteAll($condition = null, $params = [])
    {
        self::requireAllowExecute();

        return parent::deleteAll($condition, $params);
    }

    public function update($runValidation = true, $attributeNames = null)
    {
        if ($this->amount == 0 && !self::STORE_EMPTY_AMOUNT) {
            return (bool)$this->delete();
        }

        return parent::update($runValidation, $attributeNames);
    }

    public function insert($runValidation = true, $attributes = null)
    {
        if ($this->amount == 0 && !self::STORE_EMPTY_AMOUNT) {
            return true;
        }
        self::requireAllowExecute();

        return parent::insert($runValidation, $attributes);
    }

    public function beforeSave($insert)
    {
        $this->updateProcessedAt();

        return parent::beforeSave($insert);
    }

    /**
     * @throws Exception
     */
    public function afterRedistribution(int $timestamp): void
    {
        //SELECT FOR UPDATE and transaction is not necessary for this particular field.
        //So we can simply use raw SQL to avoid transaction validation
        $this->redistribute_try_at = $timestamp;
        static::getDb()
            ->createCommand()
            ->update(static::tableName(), ['redistribute_try_at' => $timestamp], $this->primaryKey)
            ->execute();
    }

    /**
     * @throws \Throwable
     */
    public static function onDebtConfirmation(Debt $debt): self
    {
        if (!$debt->isStatusConfirm()) {
            throw new InvalidCallException('Method require `Debt::isStatusConfirm() === TRUE`');
        }

        if ($debt->isDebtBalancePopulated() && $debt->getDebtBalance()->isFoundForUpdate()) {
            $debtBalance = $debt->getDebtBalance();
        } else {
            //`FOR UPDATE` - necessary to avoid conflict. Because we can't just set `amount = amount + :debtAmount`.
            // We need possibility to switch values of `from_user_id` & `to_user_id`. And possibility to delete row.
            $query = DebtBalance::find()->debt($debt);
            $debtBalance = DebtBalance::findOneForUpdate($query);
        }

        if ($debtBalance) {
            $debtBalance->changeAmount($debt);
        } else {
            $debtBalance = self::factory($debt);
        }

        $debtBalance->saveCore();

        return $debtBalance;
    }

    /**
     * @throws Exception
     */
    public static function unsetProcessedAt(self $balance): ?self
    {
        $balance = self::findOneForUpdate($balance);

        if (!$balance) {
            return null; //it became zero or changed direction. This Balance can't be updated anymore.
        }

        $balance->processed_at = null;
        $balance->saveCore();

        return $balance;
    }

    public function isDirectionChanged(): bool
    {
        // we can check `to_user_id` as well here. Or both attributes. Result will be the same.
        return $this->isAttributeChanged('from_user_id', false);
    }

    public function isAttributeChanged($name, $identical = true)
    {
        if (!parent::isAttributeChanged($name, $identical)) {
            return false;
        }

        if (!$identical && self::isAttributeFloat($name)) {
            return $this->isAttributeFloatChanged($name);
        }

        return true;
    }

    /**
     * @throws Exception
     */
    private function saveCore(): void
    {
        self::$allowExecute = true;
        $res = $this->save();
        self::$allowExecute = false;

        if (!$res) {
            $message = "Unexpected error occurred: Fail to save DebtBalance.\n";
            $message .= 'DebtBalance::$errors = ' . print_r($this->errors, true);
            throw new Exception($message);
        }
    }

    private function updateProcessedAt(): void
    {
        if (!$this->amount) {
            $this->processed_at = null; // no sense to run \app\components\debt\Reduction if amount is "0"
            return;
        }

        $isAmountBecomeNotZero = $this->isAttributeChanged('amount', false) && !$this->getOldAttribute('amount');

        if ($this->isNewRecord || $isAmountBecomeNotZero || $this->isDirectionChanged()) {
            $this->processed_at = time();
        }

        // Else: leave `processed_at` as is.
    }

    /**
     * Is Direction of Debt (Credit|Deposit) is equals the Direction of DebtBalance
     *
     * @param Debt $debt
     *
     * @return bool
     */
    private function isSameDirection(Debt $debt): bool
    {
        return ($this->from_user_id == $debt->from_user_id) && ($this->to_user_id == $debt->to_user_id);
    }

    private static function factory(Debt $debt): self
    {
        $model = new self;

        $model->currency_id  = $debt->currency_id;
        $model->from_user_id = $debt->from_user_id;
        $model->to_user_id   = $debt->to_user_id;
        $model->amount       = $debt->amount;

        return $model;
    }

    private function changeAmount(Debt $debt): void
    {
        $direction = $this->isSameDirection($debt) ? +1 : -1;
        $newAmount = $this->amount + ($direction * $debt->amount);

        if ($newAmount < 0) {
            //debt_balance.amount is always > 0. Switch users to change direction.
            $fromUID = $this->from_user_id;
            $toUID   = $this->to_user_id;

            $this->from_user_id = $toUID;
            $this->to_user_id   = $fromUID;

            $newAmount = abs($newAmount);
        }

        $this->amount = $newAmount;
    }

    /**
     * @throws NotSupportedException
     */
    private static function requireAllowExecute(): void
    {
        if (self::$allowExecute) {
            self::requireTransaction();
            return;
        }

        $message = "Any change of this table requires `SELECT FOR UPDATE` to be done before it.\n";
        $message .= " To ensure it, was restricted access to all execute methods (save(), delete(), updateAll(), etc.).\n";
        $message .= " The app design expect only 2 reasons to save|delete balance:\n";
        $message .= "     `Debt::EVENT_AFTER_CONFIRMATION`  &  `\app\components\debt\Reduction::cantReduceBalance()`.\n";
        $message .= "---\n";
        $message .= "If you REALLY need new way to do it - create new method in `DebtBalance`\n";
        $message .= " and before any change call `SELECT FOR UPDATE` for rows, you want to insert|update|delete.\n";
        $message .= " You should never allow to call any execute method as public - to avoid bugs in future development.\n";

        throw new NotSupportedException($message);
    }
}
