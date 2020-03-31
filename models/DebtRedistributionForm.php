<?php

namespace app\models;

use Yii;

class DebtRedistributionForm extends DebtRedistribution
{
    public $contactId;

    /** @var Contact */
    private $contact;
    private $isSenseToStore = true;

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['contactId'], 'required'],
            [['contactId'], $this->fnValidateContact()],
        ]);
    }

    public static function getModel($id = null): DebtRedistributionForm
    {
        if ($id && ($model = DebtRedistributionForm::findOne($id)) !== null) {
            return $model;
        }

        return new DebtRedistributionForm();
    }

    public function loadContact(Contact $contact): void
    {
        $this->contactId = $contact->id;

        if ($contact->debtRedistribution) {
            $this->setAttributes($contact->debtRedistribution->attributes, false);
        }
    }

    public function afterValidate()
    {
        parent::afterValidate();

        if ($this->isMaxAmountDeny() && $this->isPriorityEmpty()) {
            $this->isSenseToStore = false;  //no sense to store default values
        }
    }

    public function beforeSave($insert)
    {
        return $this->isSenseToStore && parent::beforeSave($insert);
    }

    /**
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        $res = parent::save($runValidation, $attributeNames);

        if (!$this->isSenseToStore) {
            $this->isSenseToStore = true;
            if (!$this->isNewRecord) {
                $this->delete();
            }
            return true;
        }

        return $res;
    }

    private function fnValidateContact(): callable
    {
        return function () {
            $this->contact = Contact::find()
                ->where(['id' => $this->contactId])
                ->currentUserOwner()
                ->virtual(false)
                ->one();

            if (!$this->contact || !$this->contact->linkedUser) {
                $this->addError('contactId', 'Contact is wrong. Reload page, please.');
                return;
            }

            $this->from_user_id = $this->contact->user_id;
            $this->to_user_id   = $this->contact->link_user_id;
            $this->populateRelation('toUser', $this->contact->linkedUser);
        };
    }
}
