<?php

namespace app\models;

use app\models\queries\CurrencyQuery;
use app\models\queries\DebtBalanceQuery;
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
 * @property int $amount
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
 */
class Debt extends ActiveRecord
{
    public const STATUS_PENDING = 0;
    public const STATUS_CONFIRM = 1;

    public const DIRECTION_DEPOSIT = 1;
    public const DIRECTION_CREDIT  = 2;

    public const SCENARIO_FORM = 'form';

    public const EVENT_AFTER_CONFIRMATION = 'after_confirmation';

    public $user;
    public $direction;
    public $depositPending;
    public $creditPending;
    public $depositConfirmed;
    public $creditConfirmed;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'debt';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['currency_id', 'amount'], 'required'],
            //TODO [ref] These fields ('user', 'direction', and other public fields in this class)
            //       we need only on frontend form.
            //       For this purpose you should create DebtForm model with all these fields and their rules.
            //       Why: in all other places, except page /debt/create, we DON'T need these rules and fields
            //            (e.g. in console app)
            [['user', 'direction'], 'required', 'on' => self::SCENARIO_FORM],
            [['from_user_id', 'to_user_id', 'currency_id', 'status'], 'integer'],
            ['amount', 'number', 'min' => 0],
            [['created_at', 'created_by', 'updated_at', 'updated_by'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'from_user_id' => 'From User',
            'to_user_id' => 'User',
            'currency_id' => 'Currency',
            'amount' => 'Amount',
            'status' => 'Status',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
            ],
            'blameable' => [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
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

    /**
     * @return \yii\db\ActiveQuery|CurrencyQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::className(), ['id' => 'currency_id']);
    }

    /**
     * @return \yii\db\ActiveQuery|DebtBalanceQuery
     */
    public function getDebtBalance()
    {
        return $this->hasOne(DebtBalance::className(), [
            'currency_id'  => 'currency_id',
            'from_user_id' => 'from_user_id',
            'to_user_id'   => 'to_user_id',
        ]);
    }

    public function getUserDisplayName($direction)
    {
        $name = $this->fromUser->name;
        if (!empty($this->fromUser->contact)) {
            $name = $this->fromUser->getDisplayName();
        }
        if ((int) $direction === self::DIRECTION_CREDIT) {
            $name = $this->toUser->name;
            if (!empty($this->toUser->contact)) {
                $name = $this->toUser->getDisplayName();
            }
        }

        return $name;
    }

    public function canConfirmDebt($direction)
    {
        $canConfirmDebt = $this->isStatusPending() && ((int) $this->created_by !== (int) $this->from_user_id);
        if ((int) $direction === self::DIRECTION_DEPOSIT) {
            $canConfirmDebt = $this->isStatusPending() && ((int) $this->created_by !== (int) $this->to_user_id);
        }

        return $canConfirmDebt;
    }

    public function canCancelDebt()
    {
        return ($this->isStatusPending() && (((int) $this->from_user_id === Yii::$app->user->id) || ((int) $this->to_user_id === Yii::$app->user->id)));
    }

    public function isStatusPending()
    {
        return (int)$this->status === Debt::STATUS_PENDING;
    }

    public function isStatusConfirm()
    {
        return !$this->isStatusPending();
    }

    public function setUsersFromContact($contactUserId, $contactLinkedUserId)
    {
        if ($this->isStatusPending()) {
            $this->from_user_id = $contactLinkedUserId;
            $this->to_user_id   = $contactUserId;
        } else {
            $this->from_user_id = $contactUserId;
            $this->to_user_id   = $contactLinkedUserId;
        }
    }

    public function beforeSave($insert)
    {
        if ($this->scenario === self::SCENARIO_FORM) {
            $this->setUsersFromContact(Yii::$app->user->id, $this->user);
        }

        return parent::beforeSave($insert);
    }

    /**
     * @param DebtBalance $balance
     * @param int         $amount '-' (negative) will decrease Balance amount.
     *                            '+' (positive) will increase.
     */
    public static function factoryChangeBalance(DebtBalance $balance, $amount): ?self
    {
        if (!$amount) {
            return null;
        }

        $model = new Debt();

        $model->currency_id  = $balance->currency_id;
        $model->from_user_id = ($amount > 0) ? $balance->from_user_id : $balance->to_user_id;
        $model->to_user_id   = ($amount > 0) ? $balance->to_user_id : $balance->from_user_id;
        $model->amount       = abs($amount);
        $model->populateRelation('debtBalance', $balance);

        return $model;
    }


    public static function mapStatus()
    {
        return [self::STATUS_PENDING, self::STATUS_CONFIRM];
    }

    public function transactions()
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_ALL,
            self::SCENARIO_FORM    => self::OP_ALL,
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Throwable
     */
    public function afterSave($insert, $changedAttributes)
    {
        $this->triggerAfterAffectBalance($insert, $changedAttributes);
        parent::afterSave($insert, $changedAttributes);
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

    /**
     * @throws \Throwable
     */
    private function triggerAfterAffectBalance(bool $insert, array $changedAttributes): void
    {
        if ($insert) {
            $isStatusChanged = true;
        } elseif (!isset($changedAttributes['status'])) {
            $isStatusChanged = false;
        } else {
            $isStatusChanged = (int)$changedAttributes['status'] !== (int)$this->status;
        }

        if ($isStatusChanged && $this->isStatusConfirm()) {
            DebtBalance::onDebtConfirmation($this);
            $this->trigger(self::EVENT_AFTER_CONFIRMATION);
        }
    }
}
