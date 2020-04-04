<?php

namespace app\models;

use Yii;
use yii\web\NotFoundHttpException;

class DebtRedistributionForm extends DebtRedistribution
{
    public $contactId;

    private $isSenseToStore = true;

    /**
     * use {@see DebtRedistributionForm::factory()} instead
     *
     * {@inheritDoc}
     *
     * @param array $config
     */
    protected function __construct($config = [])
    {
        parent::__construct($config);
    }

    /**
     * @param int|null|DebtRedistribution|Contact $data
     *
     * @return self
     */
    public static function factory($data = null): self
    {
        $model = new self();
        $model->loadDefaultValues();

        if ($data instanceof DebtRedistribution) {
            $model->setAttributes($data->attributes, false);
        } elseif ($data instanceof Contact) {
            $model->contactId = $data->id;
        } elseif (is_numeric($data)) {
            $model->contactId = $data;
        }

        return $model;
    }

    /**
     * @param int $id
     *
     * @return null|self
     * @throws NotFoundHttpException
     */
    public static function findModel($id): ?self
    {
        return self::find()
            ->where(['id' => $id])
            ->fromUser()
            ->one();
    }

    public function rules()
    {
        $msg = Yii::t('app', 'You are trying to save default values. Just close this form.');

        return array_merge(parent::rules(), [
            ['id', 'integer', 'min' => 1],

            ['contactId', 'required',
                'when'       => static function (self $model) { return !$model->id; },
                'whenClient' => 'function () {return false;}', //no sense to check it on client
            ],
            ['contactId', $this->fnValidateContact(), 'skipOnEmpty' => true],

            /** this rule is only for UI behavior - to explain user why this particular case will not saved.
             * if you remove it - backend logic will not changed
             * to change backend logic - {@see self::$isSenseToStore}
             */
            ['max_amount', 'required',
                'when'       => [$this, 'getIsNewRecord'],
                'whenClient' => 'function () {return false;}',
                'isEmpty'    => function () { return $this->isMaxAmountDeny(); },
                'message'    => $msg,
            ],
        ]);
    }

    public function afterValidate()
    {
        parent::afterValidate();

        if (!$this->hasErrors() && $this->isMaxAmountDeny()) {
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
            $contact = Contact::find()->forDebtRedistribution($this->contactId)->one();

            if (!$contact || !$contact->linkedUser) {
                $this->addError('contactId', 'Contact is wrong. Reload page, please.');
                return;
            }

            $this->from_user_id = $contact->user_id;
            $this->to_user_id   = $contact->link_user_id;
            $this->populateRelation('toUser', $contact->linkedUser);
        };
    }
}
