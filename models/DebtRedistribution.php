<?php

namespace app\models;

use app\components\debt\Redistribution;
use app\components\helpers\DebtHelper;
use app\helpers\Number;
use app\interfaces\UserRelation\ByDebtInterface;
use app\interfaces\UserRelation\ByOwnerInterface;
use app\interfaces\UserRelation\ByOwnerTrait;
use app\models\queries\ContactQuery;
use app\models\queries\CurrencyQuery;
use app\models\queries\DebtBalanceQuery;
use app\models\queries\DebtRedistributionQuery;
use app\models\traits\FloatAttributeTrait;
use app\models\traits\RelationToDebtBalanceTrait;
use app\models\traits\SelectForUpdateTrait;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

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
 * @property DebtBalance $debtBalanceDirectionBack
 * @property DebtBalance $debtBalanceDirectionSame
 */
class DebtRedistribution extends ActiveRecord implements ByOwnerInterface, ByDebtInterface
{
    use ByOwnerTrait;
    use SelectForUpdateTrait;
    use FloatAttributeTrait;
    use RelationToDebtBalanceTrait;

    /** @var null no limit - allow any amount. */
    public const MAX_AMOUNT_ANY  = null;
    /** @var int limit is 0, so deny to redistribute. Default value. */
    public const MAX_AMOUNT_DENY = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'debt_redistribution';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['currency_id', 'required'],

            //max_amount:
            ['max_amount', 'number', 'min' => 0],
            ['max_amount', $this->fnFormatMaxAmount(), 'skipOnEmpty' => false],
            ['max_amount', $this->getFloatRuleFilter()],

            //db:
            'unique' => [['user_id', 'link_user_id', 'currency_id'], 'unique', 'targetAttribute' => ['user_id', 'link_user_id', 'currency_id']],
            ['currency_id', 'exist', 'targetRelation' => 'currency'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'           => 'ID',
            'user_id'      => 'User ID',
            'link_user_id' => 'Link User ID',
            'currency_id'  => 'Currency',
            'max_amount'   => 'Max Amount',
        ];
    }

    /**
     * @return DebtRedistributionQuery
     */
    public static function find()
    {
        return new DebtRedistributionQuery(get_called_class());
    }

    /**
     * @return CurrencyQuery|ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::className(), ['id' => 'currency_id']);
    }

    /**
     * @return ContactQuery|ActiveQuery
     */
    public function getContact(): ContactQuery
    {
        return $this->hasOne(Contact::className(), [
            'user_id' => 'user_id',
            'link_user_id' => 'link_user_id',
        ]);
    }

    /**
     * @return DebtBalanceQuery|ActiveQuery
     */
    public function getDebtBalanceDirectionBack()
    {
        return $this->hasOne(DebtBalance::className(), [
            'currency_id' => 'currency_id',
            DebtBalance::getDebtReceiverAttribute() => self::getOwnerAttribute(),
            DebtBalance::getDebtorAttribute() => self::getLinkedAttribute(),
        ]);
    }

    /**
     * @return DebtBalanceQuery|ActiveQuery
     */
    public function getDebtBalanceDirectionSame()
    {
        return $this->hasOne(DebtBalance::className(), [
            'currency_id' => 'currency_id',
            DebtBalance::getDebtorAttribute() => self::getOwnerAttribute(),
            DebtBalance::getDebtReceiverAttribute() => self::getLinkedAttribute(),
        ]);
    }

    public function isMaxAmountAny(): bool
    {
        return $this->max_amount === self::MAX_AMOUNT_ANY;
    }

    public function isMaxAmountDeny(): bool
    {
        $scale = DebtHelper::getFloatScale();

        return !$this->isMaxAmountAny() && Number::isFloatEqual(self::MAX_AMOUNT_DENY, $this->max_amount, $scale);
    }

    private function fnFormatMaxAmount(): callable
    {
        return function () {
            $this->max_amount = ($this->max_amount === '') ? null : $this->max_amount;
        };
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
}
