<?php

namespace app\models;

use app\components\helpers\TimeHelper;
use app\helpers\Number;
use app\models\traits\FloatAttributeTrait;
use app\modules\bot\models\ChatTipWalletTransaction;
use DateTime;
use DateTimeZone;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "wallet_transaction".
 *
 * @property int $id
 * @property int $currency_id
 * @property int $from_user_id
 * @property int $to_user_id
 * @property float $amount
 * @property float $fee
 * @property int $type
 * @property int $anonymity
 * @property int $created_at
 *
 * @property Currency $currency
 * @property User $fromUser
 * @property User $toUser
 */
class WalletTransaction extends ActiveRecord
{
    use FloatAttributeTrait;

    // fee for internal transactions in source currency
    public const FEE = 0.01;
    public const MIN_AMOUNT = 0.01;
    public const MAX_AMOUNT = 9999999999999.99;

    public const WALLET_TYPE = 0;
    public const SEND_TIP_TYPE = 1;
    public const SEND_MONEY_TYPE = 2;
    public const SEND_ANONYMOUS_ADMIN_TIP_TYPE = 3;

    public const FROM_USER_CHECK_FLAG = 1;
    public const TO_USER_CHECK_FLAG = 2;
    public const CURRENCY_CHECK_FLAG = 4;
    public const AMOUNT_CHECK_FLAG = 8;

    public function __construct()
    {
        $this->fee = self::FEE;

        parent::__construct(...func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%wallet_transaction}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['currency_id', 'from_user_id', 'to_user_id', 'amount', 'type', 'anonymity'], 'required'],
            [['currency_id', 'from_user_id', 'to_user_id', 'type', 'anonymity', 'created_at'], 'integer'],
            ['amount', 'double', 'min' => self::MIN_AMOUNT, 'max' => self::MAX_AMOUNT],
            ['fee', 'double', 'min' => 0, 'max' => self::MAX_AMOUNT],
            ['fee', 'default', 'value' => self::MIN_AMOUNT],
            [['currency_id'], 'exist', 'skipOnError' => true, 'targetClass' => Currency::class, 'targetAttribute' => ['currency_id' => 'id']],
            [['from_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['from_user_id' => 'id']],
            [['to_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['to_user_id' => 'id']],
            [['type'], 'default', 'value'=> self::WALLET_TYPE],
            [['anonymity'], 'default', 'value'=> 0],
        ];
    }

    public function init()
    {
        parent::init();
        if ($this->isNewRecord) {
            $this->type = self::WALLET_TYPE;
            $this->anonymity = 0;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'currency_id' => 'Currency ID',
            'from_user_id' => 'From User ID',
            'to_user_id' => 'To User ID',
            'amount' => 'Amount',
            'fee' => 'Fee',
            'type' => 'Type',
            'anonymity' => 'Anonymity',
            'created_at' => 'Created At',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false,
            ],
        ];
    }

    public function getCurrency(): ActiveQuery
    {
        return $this->hasOne(Currency::class, ['id' => 'currency_id']);
    }

    public function getFromUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'from_user_id']);
    }

    public function getToUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'to_user_id']);
    }

    public function getChatTipWalletTransaction(): ActiveQuery
    {
        return $this->hasOne(ChatTipWalletTransaction::class, ['transaction_id' => 'id']);
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function getFee()
    {
        return $this->fee;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getToUserId()
    {
        return $this->to_user_id;
    }

    public function getFromUserId()
    {
        return $this->from_user_id;
    }

    public function getCurrencyId()
    {
        return $this->currency_id;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function getType()
    {
        return $this->type ?? WalletTransaction::WALLET_TYPE;
    }

    public function getAnonymity()
    {
        return $this->anonymity ?? 0;
    }

    public function getAmountPlusFee(): float
    {
        return $this->fee ? Number::floatAdd($this->amount, $this->fee) : $this->amount;
    }

    public function getCreatedAtByUser($user = null)
    {
        if (!isset($user)) {
            $user = $this->fromUser;
        }

        $date = new DateTime();
        $dateTimeZone = new DateTimeZone(TimeHelper::getTimezoneByOffset($user->timezone));

        $date->setTimezone($dateTimeZone);
        $date->setTimestamp($this->getCreatedAt());

        $timeOffset = $dateTimeZone->getOffset($date);

        return $date->getTimestamp() + $timeOffset;
    }

    public function check($flag = self::FROM_USER_CHECK_FLAG | self::CURRENCY_CHECK_FLAG)
    {
        if ($flag & self::FROM_USER_CHECK_FLAG) {
            $chekItem = $this->getFromUser()->one();
            if (empty($chekItem->id)) {
                return false;
            }
        }

        if ($flag & self::TO_USER_CHECK_FLAG) {
            $chekItem = $this->getToUser()->one();
            if (empty($chekItem->id)) {
                return false;
            }
        }

        if ($flag & self::CURRENCY_CHECK_FLAG) {
            $chekItem = $this->getCurrency()->one();
            if (empty($chekItem->id)) {
                return false;
            }
        }

        if ($flag & self::AMOUNT_CHECK_FLAG) {
            $chekItem = $this->getAmountPlusFee();
            if ($chekItem < (self::MIN_AMOUNT + $this->getFee())) {
                return false;
            }
        }

        return true;
    }
}
