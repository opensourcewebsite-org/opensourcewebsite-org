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
 * You can validate DB for data collisions - {@see \app\commands\DebtController::actionCheckBalance()}
 *
 * @property int $currency_id
 * @property int $from_user_id          {@see Debt::$from_user_id}
 * @property int $to_user_id            {@see Debt::$to_user_id}
 * @property float $amount              $amount = sumOfAllCredits - sumOfAllDeposits. Always ($amount > 0) is true
 * @property int|null $processed_at     TIMESTAMP - if last Debt created by User.
 *                                      NULL      - by cron {@see \app\components\debt\Reduction}
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
class DebtBalance extends ActiveRecord
{
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

    private $foundForUpdate = false;

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
        /** @var [] $link empty array is not bug. {@see DebtBalance::chainMemberParent} */
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

    /**
     * @throws \Throwable
     */
    public static function onDebtConfirmation(Debt $debt): self
    {
        if (!$debt->isStatusConfirm()) {
            throw new InvalidCallException('Method require `Debt::isStatusConfirm() === TRUE`');
        }

        if ($debt->isRelationPopulated('debtBalance') && $debt->debtBalance->foundForUpdate) {
            $debtBalance = $debt->debtBalance;
        } else {
            //`FOR UPDATE` - necessary to avoid conflict. Because we can't just set `amount = amount + :debtAmount`.
            // We need possibility to switch values of `from_user_id` & `to_user_id`. And possibility to delete row.
            $debtBalance = DebtBalance::findOneForUpdate($debt);
        }

        if ($debtBalance) {
            $debtBalance->changeAmount($debt);
            $debtBalance->changeProcessed($debt);
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

    /**
     * @param self[] $models
     *
     * @return DebtBalance[]
     */
    public static function findAllForUpdate($models): array
    {
        self::requireTransaction();

        $sql = self::find()
            ->balances($models)
            ->createCommand()
            ->getRawSql();

        $modelsRefreshed = self::findBySql($sql . ' FOR UPDATE')->all();
        foreach ($modelsRefreshed as $model) {
            $model->foundForUpdate = true;
        }

        return $modelsRefreshed;
    }

    /**
     * @param Debt|DebtBalance $model
     *
     * @return DebtBalance|null
     */
    private static function findOneForUpdate($model): ?self
    {
        self::requireTransaction();

        $query = self::find();
        if ($model instanceof DebtBalance) {
            $query->where($model->getPrimaryKey(true));
        } else {
            $query->debt($model);
        }
        $sql = $query->createCommand()->getRawSql();

        $balance = self::findBySql($sql . ' FOR UPDATE')->one();
        if ($balance) {
            $balance->foundForUpdate = true;
        }

        return $balance;
    }

    private function changeProcessed(Debt $debt): void
    {
        if (!$this->amount) {
            $this->processed_at = null; // no sense to run \app\components\debt\Deduction if amount is "0"
        } elseif ($debt->isCreatedByUser()) {
            $this->processed_at = time();
        }
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

        $model->changeProcessed($debt);

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

    private static function requireTransaction(): void
    {
        if (!self::getDb()->getTransaction()) {
            throw new InvalidCallException('The method must be called in DB transaction');
        }
    }
}
