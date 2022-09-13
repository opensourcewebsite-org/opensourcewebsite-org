<?php

declare(strict_types=1);

namespace app\models\forms;

use app\models\Contact;
use app\models\Debt;
use Yii;
use yii\base\Model;

class CreateDebtForm extends Model
{
    public $counter_user_id;
    public $direction;
    public $amount;
    public $currency_id;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['counter_user_id', 'direction', 'currency_id', 'amount'], 'required'],
            [['counter_user_id', 'direction', 'currency_id'], 'integer'],
            ['counter_user_id', 'validateCounterUser', 'when' => function (self $model) {
                return $model->direction == Debt::DIRECTION_DEPOSIT;
            }],
            ['direction', 'in', 'range' => Debt::mapDirection()],
            [
                'amount',
                'double',
                'min' => 0.01,
                'max' => 9999999999999.99,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'counter_user_id' => Yii::t('app', 'User'),
            'direction' => Yii::t('app', 'Direction'),
            'currency_id' => Yii::t('app', 'Currency'),
            'amount' => Yii::t('app', 'Amount'),
        ];
    }

    public function validateCounterUser($attribute)
    {
        $counterContact = Contact::find()
            ->andWhere([
                'user_id' => $this->counter_user_id,
                'link_user_id' => Yii::$app->user->id,
            ])
            ->exists();

        if (!$counterContact) {
            $this->addError('counter_user_id', Yii::t('app', 'To create a deposit (a credit for another user) is possible only for users who added you to contacts.'));
        }
    }
}
