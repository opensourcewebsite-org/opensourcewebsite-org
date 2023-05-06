<?php

namespace app\models;

use app\behaviors\JsonBehavior;
use app\components\helpers\TimeHelper;
use app\helpers\Number;
use app\models\traits\FloatAttributeTrait;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\ChatTip;
use app\modules\bot\models\ChatTipQueueUser;
use DateTime;
use DateTimeZone;
use Yii;
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
    public const TIP_WITHOUT_REPLY_TYPE = 4;
    public const MEMBERSHIP_PAYMENT_TYPE = 5;

    public const FROM_USER_CHECK_FLAG = 1;
    public const TO_USER_CHECK_FLAG = 2;
    public const CURRENCY_CHECK_FLAG = 4;
    public const AMOUNT_CHECK_FLAG = 8;

    public const CHAT_TIP_ID_DATA_KEY = 'chatTipId';
    public const CHAT_TIP_QUEUE_USER_ID_DATA_KEY = 'chatTipQueueUserId';
    public const CHAT_MEMBER_ID_DATA_KEY = 'chatMemberId';

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
            $this->type = $this->type ?? self::WALLET_TYPE;
            $this->anonymity = $this->anonymity ?? 0;
        }
    }

    public function getTypeLabel(): string
    {
        return static::getTypeLabels()[(int)$this->type];
    }

    public function hasTypeLabel(): string
    {
        return !empty(static::getTypeLabels()[(int)$this->type]);
    }

    public static function getTypeLabels(): array
    {
        return [
            self::WALLET_TYPE => '',
            self::SEND_TIP_TYPE => Yii::t('app', 'Group Thanks'),
            self::SEND_MONEY_TYPE => '',
            self::SEND_ANONYMOUS_ADMIN_TIP_TYPE => Yii::t('app', 'Tip sending to anonymous admin transaction'),
            self::TIP_WITHOUT_REPLY_TYPE => Yii::t('app', 'Tip without reply transaction'),
            self::MEMBERSHIP_PAYMENT_TYPE => Yii::t('app', 'Membership payment'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'currency_id' => Yii::t('app', 'Currency'),
            'from_user_id' => Yii::t('app', 'From User'),
            'to_user_id' => Yii::t('app', 'To User'),
            'amount' => Yii::t('app', 'Amount'),
            'data' => Yii::t('app', 'Data'),
            'fee' => Yii::t('app', 'Fee'),
            'type' => Yii::t('app', 'Type'),
            'anonymity' => Yii::t('app', 'Anonymity'),
            'created_at' => Yii::t('app', 'Created At'),
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
            [
                'class' => JsonBehavior::class,
                'attributes' => [
                      'data',
                ]
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

    public function getData($name)
    {
        return (isset($this->data[$name]) ? $this->data[$name] : null);
    }

    public function setData($name, $value)
    {
        $data = $this->data;
        $data[$name] = $value;
        $this->data = $data;
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
            $checkItem = $this->getFromUser()->one();
            if (empty($checkItem->id)) {
                return false;
            }
        }

        if ($flag & self::TO_USER_CHECK_FLAG) {
            $checkItem = $this->getToUser()->one();
            if (empty($checkItem->id)) {
                return false;
            }
        }

        if ($flag & self::CURRENCY_CHECK_FLAG) {
            $checkItem = $this->getCurrency()->one();
            if (empty($checkItem->id)) {
                return false;
            }
        }

        if ($flag & self::AMOUNT_CHECK_FLAG) {
            $checkItem = $this->getAmountPlusFee();
            if ($checkItem < (self::MIN_AMOUNT + $this->getFee())) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param WalletTransaction  $walletTransaction
     *
     * @return bool
     */
    public function createTransaction()
    {
        $transaction = ActiveRecord::getDb()->beginTransaction();

        try {
            if ($this->validate()) {

                if ($this->fromUser->id == $this->toUser->id) {
                    throw new \Exception('Sender and reciever are the same');
                }

                $fromUserWallet = $this->fromUser->getWalletByCurrencyId($this->currency_id);
                $toUserWallet = $this->toUser->getWalletByCurrencyId($this->currency_id);

                if (Number::floatSub($fromUserWallet->amount, Number::floatAdd($this->amount, $this->fee)) < 0) {
                    throw new \Exception('Not enough money');
                }

                $toUserWallet->amount += $this->amount;
                $toUserWallet->save();

                $fromUserWallet->amount -= $this->amount + $this->fee;
                $fromUserWallet->save();

                $this->save();
            } else {
                throw new \Exception('Transaction validation is failed: ' . print_r($this->getErrors(), true));
            }

            switch ($this->type) {
                case self::TIP_WITHOUT_REPLY_TYPE:
                    $chatTipQueueUserId = $this->getData(self::CHAT_TIP_QUEUE_USER_ID_DATA_KEY);
                    if (!isset($chatTipQueueUserId)) {
                        throw new \Exception('Chat tip user id is not found in transaction');
                    }
                    $chatTipQueueUser = ChatTipQueueUser::findOne($chatTipQueueUserId);

                    if (!isset($chatTipQueueUser->id)) {
                        throw new \Exception("Chat tip user with id: {$chatTipQueueUserId} is not found");
                    }

                    $chatTipQueueUser->transaction_id = $this->id;
                    $chatTipQueueUser->save();
                    break;
                case self::MEMBERSHIP_PAYMENT_TYPE:
                    $chatMemberId = $this->getData(self::CHAT_MEMBER_ID_DATA_KEY);
                    if (!isset($chatMemberId)) {
                        throw new \Exception('Chat member id is not found in transaction');
                    }
                    $chatMember = ChatMember::findOne($chatMemberId);

                    if (!isset($chatMember->id)) {
                        throw new \Exception("Chat member with id: {$chatMemberId} is not found");
                    }

                    $days = $chatMember->getMembershipTariffDaysBalance();
                    $days += $chatMember->getMembershipTariffDays();
                    if (!$chatMember->setMembershipTariffDaysBalance($days)) {
                        throw new \Exception("Cannot set membership days to: {$days} in chat member with id: {$chatMemberId} is not found");
                    }
                    $chatMember->save();
                    break;
            }

            $transaction->commit();

            // additional check after commit
            $walletTransaction = WalletTransaction::findOne($this->id);

            return $walletTransaction->id ?? false;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage());
            return false;
        }
    }

    public function getReceiverLabel()
    {
        if ($this->anonymity) {
            $label = Yii::t('bot', 'Hidden');

            if ($this->hasGroupLabel()) {
                $label = $this->getGroupLabel();
            }

            return $label;
        }

        return $this->toUser->botUser->getFullLink();
    }

    public function hasGroupLabel()
    {
        $itemId = $this->getData(self::CHAT_TIP_ID_DATA_KEY);
        $itemClass = ChatTip::class;

        if ($this->type == WalletTransaction::MEMBERSHIP_PAYMENT_TYPE) {
            $itemId = $this->getData(self::CHAT_MEMBER_ID_DATA_KEY);
            $itemClass = ChatMember::class;
        }

        if (!isset($itemId)) {
            return false;
        }

        $item = $itemClass::findOne($itemId);

        if (!isset($item->id) || !isset($item->chat->id)) {
            return false;
        }

        $label = $item->chat->title;

        if (!empty($item->chat->username)) {
            $label .= ' (@' . $item->chat->username . ')';
        }

        if (empty($label)) {
            return false;
        }

        return true;
    }

    public function getGroupLabel()
    {
        $itemId = $this->getData(self::CHAT_TIP_ID_DATA_KEY);
        $itemClass = ChatTip::class;

        if ($this->type == WalletTransaction::MEMBERSHIP_PAYMENT_TYPE) {
            $itemId = $this->getData(self::CHAT_MEMBER_ID_DATA_KEY);
            $itemClass = ChatMember::class;
        }

        if (!isset($itemId)) {
            return null;
        }

        $item = $itemClass::findOne($itemId);

        if (!isset($item->id) || !isset($item->chat->id)) {
            return null;
        }

        $label = $item->chat->title;

        if (!empty($item->chat->username)) {
            $label .= ' (@' . $item->chat->username . ')';
        }

        if (empty($label)) {
            return null;
        }

        return $label;
    }
}
