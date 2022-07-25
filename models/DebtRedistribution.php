<?php

namespace app\models;

use app\components\debt\Redistribution;
use app\helpers\Number;
use app\interfaces\UserRelation\ByDebtInterface;
use app\interfaces\UserRelation\ByOwnerInterface;
use app\interfaces\UserRelation\ByOwnerTrait;
use app\models\queries\ContactQuery;
use app\models\queries\CurrencyQuery;
use app\models\queries\DebtBalanceQuery;
use app\models\queries\DebtRedistributionQuery;
use app\models\traits\FloatAttributeTrait;
use app\models\traits\SelectForUpdateTrait;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use Yii;

/**
 * This is the model class for table "debt_redistribution".
 *
 * @property int        $id
 * @property int        $user_id          {@see ByOwnerInterface}, {@see ByDebtInterface}
 * @property int        $link_user_id     {@see ByOwnerInterface}, {@see ByDebtInterface}
 * @property int        $currency_id
 * @property float|null $max_amount "NULL" - no limit - allow any amount. "0" - limit is 0, so deny to redistribute.
 *                                  max_amount is limit of {@see Debt}, which may be created by {@see Redistribution}.
 *                                  Note: other Debts, created not by Redistribution, don't depend from this limit.
 *                                  RU: сколько я (user_id) разрешаю моему контакту (link_user_id) быть должным мне,
 *                                      при Перераспределении долгов.
 *
 * @property Currency $currency
 * @property Contact $contact
 * @property DebtBalance $debtBalance
 * @property DebtBalance $counterDebtBalance
 */
class DebtRedistribution extends ActiveRecord implements ByOwnerInterface, ByDebtInterface
{
    use ByOwnerTrait;
    use SelectForUpdateTrait;
    use FloatAttributeTrait;

    /** @var null no limit - allow any amount. */
    public const MAX_AMOUNT_ANY  = null;
    /** @var int limit is 0, so deny to redistribute. Default value. */
    public const MAX_AMOUNT_DENY = 0;

    public static function tableName(): string
    {
        return '{{%debt_redistribution}}';
    }

    public function rules(): array
    {
        return [
            [['currency_id', 'user_id', 'link_user_id'], 'required'],
            [
                'max_amount',
                'double',
                'min' => 0,
                'max' => 9999999999999.99,
            ],
            'unique' => [['user_id', 'link_user_id', 'currency_id'], 'unique', 'targetAttribute' => ['user_id', 'link_user_id', 'currency_id']],
            ['currency_id', 'exist', 'targetRelation' => 'currency'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'link_user_id' => 'Link User ID',
            'currency_id' => Yii::t('app', 'Currency'),
            'max_amount' => Yii::t('app', 'Max. amount'),
        ];
    }

    public static function find(): DebtRedistributionQuery
    {
        return new DebtRedistributionQuery(get_called_class());
    }

    /**
     * @return CurrencyQuery|ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::class, ['id' => 'currency_id']);
    }

    /**
     * @return ContactQuery|ActiveQuery
     */
    public function getContact(): ContactQuery
    {
        return $this->hasOne(Contact::class, [
            'user_id' => 'user_id',
            'link_user_id' => 'link_user_id',
        ]);
    }

    /**
     * @return DebtBalanceQuery|ActiveQuery
     */
    public function getDebtBalance()
    {
        return $this->hasOne(DebtBalance::class, [
            'currency_id' => 'currency_id',
            'from_user_id' => 'user_id',
            'to_user_id' => 'link_user_id',
        ]);
    }

    /**
     * @return DebtBalanceQuery|ActiveQuery
     */
    public function getCounterDebtBalance()
    {
        return $this->hasOne(DebtBalance::class, [
            'currency_id' => 'currency_id',
            'from_user_id' => 'link_user_id',
            'to_user_id' => 'user_id',
        ]);
    }

    public function isMaxAmountAny(): bool
    {
        return $this->max_amount === self::MAX_AMOUNT_ANY;
    }

    public function isMaxAmountDeny(): bool
    {
        return !$this->isMaxAmountAny() && Number::isFloatEqual(self::MAX_AMOUNT_DENY, $this->max_amount, 2);
    }

    public function debtorUID($value = null)
    {
        return $this->linkedUID($value);
    }

    public function debtReceiverUID($value = null)
    {
        return $this->ownerUID($value);
    }

    public static function getDebtorAttribute(): string
    {
        return self::getLinkedAttribute();
    }

    public static function getDebtReceiverAttribute(): string
    {
        return self::getOwnerAttribute();
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    public function setUserId(int $userId)
    {
        $this->user_id = $userId;
    }

    public function getLinkUserId()
    {
        return $this->link_user_id;
    }

    public function setLinkUserId(int $userId)
    {
        $this->link_user_id = $userId;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLinkedUser()
    {
        if ($this->link_user_id) {
            return $this->hasOne(User::class, ['id' => 'link_user_id']);
        }

        return false;
    }

    public function getCurrencyId()
    {
        return $this->currency_id;
    }
}
