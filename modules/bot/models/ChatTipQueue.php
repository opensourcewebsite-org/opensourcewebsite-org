<?php

declare(strict_types=1);

namespace app\modules\bot\models;

use app\helpers\Number;
use app\models\Currency;
use app\models\traits\FloatAttributeTrait;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "bot_chat_tip_queue".
 *
 * @property int $id
 * @property int $chat_id
 * @property int $currency_id
 * @property int $message_id
 * @property int $user_count
 * @property float $user_amount
 *
 * @package app\modules\bot\models
 */
class ChatTipQueue extends ActiveRecord
{
    use FloatAttributeTrait;

    public const USER_MIN_COUNT = 1;
    public const USER_MAX_COUNT = 100;

    public const USER_MIN_AMOUNT = 0.01;
    public const USER_MAX_AMOUNT = 9999999999999.99;

    public const USER_CHECK_FLAG = 1;
    public const CHAT_CHECK_FLAG = 2;
    public const CURRENCY_CHECK_FLAG = 4;
    public const USER_COUNT_CHECK_FLAG = 8;
    public const USER_AMOUNT_CHECK_FLAG = 16;

    public const OPEN_STATE = 0;
    public const CLOSED_STATE = 1;

    public const MUTEX_KEY = 'CHAT_TIP_QUEUE_MUTEX_KEY';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bot_chat_tip_queue}}';
    }

    public function init()
    {
        parent::init();
        if ($this->isNewRecord) {
            $this->user_count = $this->user_count ?? self::USER_MIN_COUNT;
            $this->user_amount = $this->user_amount ?? self::USER_MIN_AMOUNT;
            $this->state = $this->state ?? self::OPEN_STATE;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['chat_id', 'currency_id', 'user_id'], 'required'],
            [['chat_id', 'currency_id', 'user_id'], 'integer'],
            [['message_id'], 'integer'],
            [['state'], 'integer'],
            [['state'], 'default', 'value' => self::OPEN_STATE],
            [['user_count'], 'integer', 'min' => self::USER_MIN_COUNT, 'max' => self::USER_MAX_COUNT],
            [['user_count'], 'default', 'value' => self::USER_MIN_COUNT],
            [['user_amount'], 'double', 'min' => self::USER_MIN_AMOUNT, 'max' => self::USER_MAX_AMOUNT],
            [['user_amount'], 'default', 'value' => self::USER_MIN_AMOUNT],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['chat_id'], 'exist', 'skipOnError' => true, 'targetClass' => Chat::class, 'targetAttribute' => ['chat_id' => 'id']],
            [['currency_id'], 'exist', 'skipOnError' => true, 'targetClass' => Currency::class, 'targetAttribute' => ['currency_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'chat_id' => Yii::t('bot', 'Chat ID'),
            'currency_id' => Yii::t('app', 'Currency'),
            'message_id' => Yii::t('bot', 'Message ID'),
            'user_count' => Yii::t('app', 'User count'),
            'user_amount' => Yii::t('app', 'User amount'),
        ];
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Gets query for [[Chat]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChat()
    {
        return $this->hasOne(Chat::class, ['id' => 'chat_id']);
    }

    public function getChatId()
    {
        return $this->chat_id;
    }

    /**
     * Gets query for [[Currency]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::class, ['id' => 'currency_id']);
    }

    public function getCurrencyId()
    {
        return $this->currency_id;
    }

    public function getMessageId()
    {
        return $this->message_id;
    }

    public function getUserCount()
    {
        return $this->user_count ?? self::USER_MIN_COUNT;
    }

    public function getUserAmount()
    {
        return $this->user_amount ?? self::USER_MIN_AMOUNT;
    }

    public function getState()
    {
        return $this->state ?? self::OPEN_STATE;
    }

    public function check($flag = self::USER_CHECK_FLAG | self::CHAT_CHECK_FLAG)
    {
        if ($flag & self::USER_CHECK_FLAG) {
            $checkItem = $this->getUser()->one();
            if (empty($checkItem->id)) {
                return false;
            }
        }

        if ($flag & self::CHAT_CHECK_FLAG) {
            $checkItem = $this->getChat()->one();
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

        if ($flag & self::USER_COUNT_CHECK_FLAG) {
            $checkItem = $this->user_count;
            if ($checkItem < self::USER_MIN_COUNT || $checkItem > self::USER_MAX_COUNT) {
                return false;
            }
        }

        if ($flag & self::USER_AMOUNT_CHECK_FLAG) {
            $checkItem = $this->user_amount;
            if ($checkItem < self::USER_MIN_AMOUNT || $checkItem > self::USER_MAX_AMOUNT) {
                return false;
            }
        }

        return true;
    }

    public function getQueueUsers(): ActiveQuery
    {
        return $this->hasMany(ChatTipQueueUser::class, ['queue_id' => 'id']);
    }

    public function getQueueProcessedUsersCount()
    {
        return $this->getQueueUsers()->where(['>', 'transaction_id', '0'])->count();
    }

    public function getQueueAvailableUsersCount()
    {
        return $this->userCount - $this->getQueueProcessedUsersCount();
    }

    public function getQueuePaidSum()
    {
        return Number::floatMul($this->getQueueProcessedUsersCount(), $this->userAmount);
    }

    public function close()
    {
        $this->state = self::CLOSED_STATE;
        $this->save();
    }
}
