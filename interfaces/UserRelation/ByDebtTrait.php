<?php

namespace app\interfaces\UserRelation;

use app\models\Contact;
use app\models\DebtRedistribution;
use app\models\queries\ContactQuery;
use app\models\queries\DebtRedistributionQuery;
use app\models\queries\UserQuery;
use app\models\User;
use yii\db\ActiveQuery;

/**
 * Trait ByDebtTrait
 *
 * @property User $fromUser
 * @property User $toUser
 * @property Contact $fromContact
 * @property Contact $toContact
 * @property Contact[] $fromContacts
 * @property Contact[] $toContacts
 * @property DebtRedistribution $fromDebtRedistribution
 * @property DebtRedistribution $toDebtRedistribution
 * @property DebtRedistribution[] $fromDebtRedistributions
 * @property DebtRedistribution[] $toDebtRedistributions
 */
trait ByDebtTrait
{
    use UserRelationTrait;

    /**
     * @return mixed
     * @see ByDebtInterface::debtorUID()
     */
    public function debtorUID($value = null)
    {
        if (isset($value)) {
            $this->from_user_id = $value;
        }

        return $this->from_user_id;
    }

    /**
     * @return mixed
     * @see ByDebtInterface::debtReceiverUID()
     */
    public function debtReceiverUID($value = null)
    {
        if (isset($value)) {
            $this->to_user_id = $value;
        }

        return $this->to_user_id;
    }

    /**
     * @see ByDebtInterface::getDebtorAttribute()
     */
    public static function getDebtorAttribute(): string
    {
        return 'from_user_id';
    }

    /**
     * @see ByDebtInterface::getDebtReceiverAttribute()
     */
    public static function getDebtReceiverAttribute(): string
    {
        return 'to_user_id';
    }

    /**
     * @return ActiveQuery|UserQuery
     */
    public function getFromUser()
    {
        return $this->hasOne(User::className(), ['id' => self::getDebtorAttribute()]);
    }

    /**
     * @return ActiveQuery|UserQuery
     */
    public function getToUser()
    {
        return $this->hasOne(User::className(), ['id' => self::getDebtReceiverAttribute()]);
    }

    /**
     * @return ActiveQuery|ContactQuery
     */
    public function getFromContact()
    {
        return $this->hasOne(Contact::className(), [
            Contact::getOwnerAttribute() => self::getDebtorAttribute(),
            Contact::getLinkedAttribute() => self::getDebtReceiverAttribute(),
        ]);
    }

    /**
     * @return ActiveQuery|ContactQuery
     */
    public function getToContact()
    {
        return $this->hasOne(Contact::className(), [
            Contact::getOwnerAttribute() => self::getDebtReceiverAttribute(),
            Contact::getLinkedAttribute() => self::getDebtorAttribute(),
        ]);
    }

    /**
     * @return ActiveQuery|ContactQuery
     */
    public function getFromContacts()
    {
        return $this->hasMany(Contact::className(), [
            Contact::getOwnerAttribute() => self::getDebtorAttribute(),
        ]);
    }

    /**
     * @return ActiveQuery|ContactQuery
     */
    public function getToContacts()
    {
        return $this->hasMany(Contact::className(), [
            Contact::getOwnerAttribute() => self::getDebtReceiverAttribute(),
        ]);
    }

    /**
     * @return ActiveQuery|DebtRedistributionQuery
     */
    public function getFromDebtRedistribution()
    {
        return $this->hasOne(DebtRedistribution::className(), [
            'currency_id' => 'currency_id',
            DebtRedistribution::getOwnerAttribute() => self::getDebtorAttribute(),
            DebtRedistribution::getLinkedAttribute() => self::getDebtReceiverAttribute(),
        ]);
    }

    /**
     * @return ActiveQuery|DebtRedistributionQuery
     */
    public function getToDebtRedistribution()
    {
        return $this->hasOne(DebtRedistribution::className(), [
            'currency_id' => 'currency_id',
            DebtRedistribution::getOwnerAttribute() => self::getDebtReceiverAttribute(),
            DebtRedistribution::getLinkedAttribute() => self::getDebtorAttribute(),
        ]);
    }

    /**
     * @return ActiveQuery|DebtRedistributionQuery
     */
    public function getFromDebtRedistributions()
    {
        return $this->hasMany(DebtRedistribution::className(), [
            'currency_id' => 'currency_id',
            DebtRedistribution::getOwnerAttribute() => self::getDebtorAttribute(),
        ]);
    }

    /**
     * @return ActiveQuery|DebtRedistributionQuery
     */
    public function getToDebtRedistributions()
    {
        return $this->hasMany(DebtRedistribution::className(), [
            'currency_id' => 'currency_id',
            DebtRedistribution::getOwnerAttribute() => self::getDebtReceiverAttribute(),
        ]);
    }

    public function factoryContact(bool $directionFrom): Contact
    {
        $contact = new Contact;

        if ($directionFrom) {
            $contact->ownerUID($this->debtorUID());
            $contact->linkedUID($this->debtReceiverUID());
        } else {
            $contact->ownerUID($this->debtReceiverUID());
            $contact->linkedUID($this->debtorUID());
        }

        return $contact;
    }
}
