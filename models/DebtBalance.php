<?php

namespace app\models;

use app\models\queries\DebtBalanceQuery;
use yii\base\Exception;
use yii\base\InvalidCallException;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "debt_balance".
 * on {@see Debt::EVENT_AFTER_CONFIRMATION} - script automatically recalculate balance amount between these two users
 *
 * @property int $currency_id
 * @property int $from_user_id          {@see Debt::$from_user_id}
 * @property int $to_user_id            {@see Debt::$to_user_id}
 * @property float $amount              $amount = sumOfAllCredits - sumOfAllDeposits. Always ($amount > 0 == true)
 * @property int|null $processed_at     TIMESTAMP - if last Debt created by User. NULL - by cron todo [#294] {@see DebtDeduction}
 *
 * @property Currency $currency
 * @property User     $fromUser
 * @property User     $toUser
 */
class DebtBalance extends ActiveRecord
{
    /**
     * Should script store zero amount in DB.
     *      FALSE - system will expect, that there are no row, where `debt_balance.amount = 0`.
     *           No sense to store zero. Even more - if we will not store zero - system will be more optimized:
     *           SELECT queries, when searching for DebtDeduction chain, will work faster todo [#294] {@see DebtDeduction}
     *      TRUE  - system will not delete rows where `amount = 0`.
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
     * Gets query for [[FromUser]].
     *
     * @return \yii\db\ActiveQuery|\app\models\queries\UserQuery
     */
    public function getFromUser()
    {
        return $this->hasOne(User::className(), ['id' => 'from_user_id']);
    }

    /**
     * Gets query for [[ToUser]].
     *
     * @return \yii\db\ActiveQuery|\app\models\queries\UserQuery
     */
    public function getToUser()
    {
        return $this->hasOne(User::className(), ['id' => 'to_user_id']);
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

    /**
     * @param Debt $debt
     *
     * @return DebtBalance
     * @throws \Throwable
     */
    public static function onDebtConfirmation(Debt $debt): self
    {
        if (!$debt->isStatusConfirm()) {
            throw new InvalidCallException('Method require `Debt::isStatusConfirm() === TRUE`');
        }

        //tme [#294] Этот заппрос будет делаться из крона внутри родительской транзакции. По идее проблем не должно быть. Проверь
        //`FOR UPDATE` - necessary to avoid conflict. Because we can't just set `amount = amount + :debtAmount`.
        // We need possibility to switch values of `from_user_id` & `to_user_id`. And possibility to delete row.
        $debtBalance = DebtBalance::findOneForUpdate($debt);

        if ($debtBalance) {
            $debtBalance->changeAmount($debt);
        } else {
            $debtBalance = self::factory($debt);
        }

        $debtBalance->processed_at = $debt->isCreatedByUser() ? time() : null;

        self::$allowExecute = true;
        $res = $debtBalance->save();
        self::$allowExecute = false;

        if (!$res) {
            $msg = "Unexpected error occurred: Fail to save DebtBalance.\n";
            $msg .= 'DebtBalance::$errors = ' . print_r($debtBalance->errors, true);
            throw new Exception($msg);
        }

        return $debtBalance;
    }

    private static function findOneForUpdate(Debt $debt): ?self
    {
        self::requireTransaction();

        $sql = self::find()
            ->debt($debt)
            ->createCommand()
            ->getRawSql();

        return self::findBySql($sql . ' FOR UPDATE')->one();
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

        $msg = "Any change of this table requires `SELECT FOR UPDATE` to be done before it.\n";
        $msg .= " To ensure it, was restricted access to all execute methods (save(), delete(), updateAll(), etc.).\n";
        $msg .= " The app design expect the single reason to save|delete balance - `Debt::EVENT_AFTER_CONFIRMATION`.\n";
        $msg .= " And the single way to do it - `DebtBalance::onDebtConfirmation()`.\n";
        $msg .= "---\n";
        $msg .= "If you REALLY need new way to do it - create new method in `DebtBalance`\n";
        $msg .= " and before any change call `SELECT FOR UPDATE` for rows, you want to insert|update|delete.\n";
        $msg .= " You should never allow to call any execute method as public - to avoid bugs in future development.\n";

        throw new NotSupportedException($msg);
    }

    private static function requireTransaction(): void
    {
        if (!self::getDb()->getTransaction()) {
            throw new InvalidCallException('The method must be called in DB transaction');
        }
    }
}
