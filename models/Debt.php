<?php

namespace app\models;

use app\helpers\Number;
use app\interfaces\UserRelation\ByDebtInterface;
use app\interfaces\UserRelation\ByDebtTrait;
use app\models\queries\CurrencyQuery;
use app\models\queries\DebtBalanceQuery;
use app\models\queries\DebtQuery;
use app\models\traits\FloatAttributeTrait;
use Yii;
use yii\base\InvalidCallException;
use yii\db\ActiveRecord;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "debt".
 *
 * @property int $id
 * @property int $from_user_id  this user should return money to $to_user_id
 * @property int $to_user_id    this user will receive money from $from_user_id
 * @property int $currency_id
 * @property float $amount
 * @property int $status
 * @property int $created_at
 * @property int $created_by    {@see self::isCreatedByUser()}
 * @property int $updated_at
 * @property int $updated_by
 * @property float $group       NULL - if created by User. TIMESTAMP - if created by script {@see \app\components\debt\Reduction}
 *
 * @property User        $toUser
 * @property User        $fromUser
 * @property Currency    $currency
 * @property DebtBalance $debtBalance
 * @property DebtBalance $counterDebtBalance
 */
class Debt extends ActiveRecord implements ByDebtInterface
{
    use ByDebtTrait;
    use FloatAttributeTrait;

    public const STATUS_PENDING = 0;
    public const STATUS_CONFIRM = 1;

    public const DIRECTION_DEPOSIT = 1;
    public const DIRECTION_CREDIT = 2;

    public $depositPending;
    public $creditPending;
    public $depositConfirmed;
    public $creditConfirmed;
    public $totalAmount;

    public const DIRECTION_LABELS = [
        self::DIRECTION_DEPOSIT => 'My deposit',
        self::DIRECTION_CREDIT => 'My credit',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%debt}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['from_user_id', 'to_user_id', 'currency_id', 'amount'], 'required'],
            [['from_user_id', 'to_user_id', 'currency_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            ['status', 'in', 'range' => [self::STATUS_PENDING, self::STATUS_CONFIRM]],
            [
                'amount',
                'double',
                'min' => 0.01,
                'max' => 9999999999999.99,
            ],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'from_user_id' => 'From User',
            'to_user_id' => Yii::t('app', 'User'),
            'currency_id' => Yii::t('app', 'Currency'),
            'amount' => Yii::t('app', 'Amount'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
            ],
            'blameable' => [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
        ];
    }

    /**
     * @return DebtQuery
     */
    public static function find()
    {
        return new DebtQuery(get_called_class());
    }

    /**
     * @return \yii\db\ActiveQuery|CurrencyQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::class, ['id' => 'currency_id']);
    }

    public function getCounterUserDisplayName()
    {
        if ($this->from_user_id == Yii::$app->user->id) {
            return $this->toUser->getDisplayName();
        } elseif ($this->to_user_id == Yii::$app->user->id) {
            return $this->fromUser->getDisplayName();
        }

        return '';
    }

    public function getCounterUserId()
    {
        if ($this->from_user_id == Yii::$app->user->id) {
            return $this->to_user_id;
        } elseif ($this->to_user_id == Yii::$app->user->id) {
            return $this->from_user_id;
        }

        return '';
    }

    public function canConfirm()
    {
        return ($this->isStatusPending()
            && (((int)$this->from_user_id == Yii::$app->user->id) && ($this->from_user_id != $this->created_by)));
    }

    public function confirm()
    {
        $this->status = self::STATUS_CONFIRM;
        $this->save();
    }

    public function canCancel()
    {
        return ($this->isStatusPending()
            && (((int)$this->from_user_id == Yii::$app->user->id) || ((int)$this->to_user_id == Yii::$app->user->id)));
    }

    public function getDirection()
    {
        return (int)$this->from_user_id == Yii::$app->user->id ? self::DIRECTION_CREDIT
            : ((int)$this->to_user_id == Yii::$app->user->id ? self::DIRECTION_DEPOSIT : false);
    }

    public function isStatusPending()
    {
        return $this->status == self::STATUS_PENDING;
    }

    public function isStatusConfirm()
    {
        return $this->status == self::STATUS_CONFIRM;
    }

    /**
     * @param DebtBalance|DebtRedistribution|Debt $source
     * @param string $amount '-' (negative) will decrease Balance amount.
     *                       '+' (positive) will increase.
     * @param float $group
     *
     * @return Debt
     */
    public static function factoryBySource($source, string $amount, float $group): self
    {
        if (Number::isFloatEqual($amount, 0, 2)) {
            throw new InvalidCallException('Argument $amount cannot be empty. $amount = ' . var_export($amount, true));
        }

        $debt = new Debt();
        $isAmountPositive = Number::isFloatGreater($amount, 0, 2);

        if ($source instanceof DebtRedistribution) {
            $debtorUID = ($isAmountPositive) ? $source->linkedUID() : $source->ownerUID();
            $debtReceiverUID = ($isAmountPositive) ? $source->ownerUID() : $source->linkedUID();
        } else {
            $debtorUID = ($isAmountPositive) ? $source->debtorUID() : $source->debtReceiverUID();
            $debtReceiverUID = ($isAmountPositive) ? $source->debtReceiverUID() : $source->debtorUID();
        }

        $debt->currency_id = $source->currency_id;
        $debt->debtorUID($debtorUID);
        $debt->debtReceiverUID($debtReceiverUID);
        $debt->amount = abs($amount);
        $debt->status = Debt::STATUS_CONFIRM;
        $debt->group = $group;

        if ($source instanceof DebtBalance) {
            $debt->populateDebtBalance($source);
        }

        return $debt;
    }
    // TODO remove old code
    public function populateDebtBalance(DebtBalance $balance): void
    {
        if ($this->isDebtBalanceHasSameDirection($balance)) {
            $this->populateRelation('debtBalance', $balance);
        } else {
            $this->populateRelation('counterDebtBalance', $balance);
        }
    }

    /**
     * Is Debt's direction (Credit|Deposit) == DebtBalance's direction
     *
     * @param DebtBalance $balance
     *
     * @return bool
     */
    public function isDebtBalanceHasSameDirection(DebtBalance $balance): bool
    {
        foreach ($this->getDebtBalance()->link as $attributeBalance => $attributeDebt) {
            if ((string)$balance->getAttribute($attributeBalance) !== (string)$this->getAttribute($attributeDebt)) {
                return false;
            }
        }

        return true;
    }

    public static function mapStatus()
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_CONFIRM,
        ];
    }

    public static function mapDirection()
    {
        return [
            self::DIRECTION_DEPOSIT,
            self::DIRECTION_CREDIT,
        ];
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        $transaction = null;

        if ($this->isStatusConfirm()
            && ($this->isNewRecord
                || ($this->isAttributeChanged('status') && ($this->getOldAttribute('status') != (int)$this->status))
                || ($this->isAttributeChanged('from_user_id') && ($this->getOldAttribute('from_user_id') != (int)$this->from_user_id))
                || ($this->isAttributeChanged('to_user_id') && ($this->getOldAttribute('to_user_id') != (int)$this->to_user_id)))) {
            $transaction = Yii::$app->db->beginTransaction();

            if (!$this->isNewRecord) {
                $oldFromUserId = $this->getOldAttribute('from_user_id');
                $oldToUserId = $this->getOldAttribute('to_user_id');
            }
        }

        if (!$result = parent::save($runValidation, $attributeNames)) {
            return $result;
        }

        if ($transaction) {
            if ($this->debtBalance) {
                if ($this->debtBalance->isFoundForUpdate()) {
                    $debtBalance = $this->debtBalance;
                } else {
                    $debtBalance = DebtBalance::findOneForUpdate($this->debtBalance);
                }

                $debtBalance->amount = Number::floatAdd($debtBalance->amount, $this->amount);
            } else {
                $debtBalance = new DebtBalance();

                $debtBalance->setAttributes([
                    'from_user_id' => $this->from_user_id,
                    'to_user_id' => $this->to_user_id,
                    'currency_id' => $this->currency_id,
                    'amount' => $this->amount,
                ]);
            }

            if ($this->counterDebtBalance) {
                if ($this->counterDebtBalance->isFoundForUpdate()) {
                    $counterDebtBalance = $this->counterDebtBalance;
                } else {
                    $counterDebtBalance = DebtBalance::findOneForUpdate($this->counterDebtBalance);
                }

                $counterDebtBalance->amount = Number::floatSub($counterDebtBalance->amount, $this->amount);
            } else {
                $counterDebtBalance = new DebtBalance();

                $counterDebtBalance->setAttributes([
                    'from_user_id' => $this->to_user_id,
                    'to_user_id' => $this->from_user_id,
                    'currency_id' => $this->currency_id,
                    'amount' => -$this->amount,
                ]);
            }

            if (isset($oldFromUserId) && isset($oldFromUserId) && (($oldFromUserId != $this->from_user_id) || ($oldToUserId != $this->to_user_id))) {
                $oldDebtBalance = DebtBalance::find()
                    ->andWhere([
                        'from_user_id' => $oldFromUserId,
                        'to_user_id' => $oldToUserId,
                        'currency_id' => $this->currency_id,
                    ])
                    ->one();

                if ($oldDebtBalance) {
                    $oldDebtBalance = DebtBalance::findOneForUpdate($oldDebtBalance);

                    $oldDebtBalance->amount = Number::floatSub($oldDebtBalance->amount, $this->amount);
                } else {
                    $oldDebtBalance = new DebtBalance();

                    $oldDebtBalance->setAttributes([
                        'from_user_id' => $oldFromUserId,
                        'to_user_id' => $oldToUserId,
                        'currency_id' => $this->currency_id,
                        'amount' => -$this->amount,
                    ]);
                }

                $oldCounterDebtBalance = DebtBalance::find()
                    ->andWhere([
                        'from_user_id' => $oldToUserId,
                        'to_user_id' => $oldFromUserId,
                        'currency_id' => $this->currency_id,
                    ])
                    ->one();

                if ($oldCounterDebtBalance) {
                    $oldCounterDebtBalance = DebtBalance::findOneForUpdate($oldCounterDebtBalance);

                    $oldCounterDebtBalance->amount = Number::floatAdd($oldCounterDebtBalance->amount, $this->amount);
                } else {
                    $oldCounterDebtBalance = new DebtBalance();

                    $oldCounterDebtBalance->setAttributes([
                        'from_user_id' => $oldToUserId,
                        'to_user_id' => $oldFromUserId,
                        'currency_id' => $this->currency_id,
                        'amount' => $this->amount,
                    ]);
                }
            }

            if ($debtBalance->save()
                && $counterDebtBalance->save()
                && (!isset($oldDebtBalance) || $oldDebtBalance->save())
                && (!isset($oldCounterDebtBalance) || $oldCounterDebtBalance->save())) {
                $transaction->commit();
            } else {
                $transaction->rollBack();

                return false;
            }
        }

        return true;
    }

    public function getDebtBalance()
    {
        return $this->hasOne(DebtBalance::className(), [
            'currency_id' => 'currency_id',
            'from_user_id' => 'from_user_id',
            'to_user_id' => 'to_user_id',
        ]);
    }

    public function getCounterDebtBalance()
    {
        return $this->hasOne(DebtBalance::className(), [
            'currency_id' => 'currency_id',
            'from_user_id' => 'to_user_id',
            'to_user_id' => 'from_user_id',
        ]);
    }

    /**
     * Debt::$created_by possible values:
     *  <li>`user.id` - if created by User.
     *  <li>NULL - if created by script (e.g. {@see \app\components\debt\Reduction})
     *
     * @return bool TRUE - by User. FALSE - by script
     */
    public function isCreatedByUser(): bool
    {
        if ($this->isNewRecord) {
            throw new InvalidCallException('Field Debt::$created_by is always NULL while isNewRecord');
        }

        return (bool)$this->created_by;
    }

    public static function generateGroup(): float
    {
        return microtime(true);
    }
}
