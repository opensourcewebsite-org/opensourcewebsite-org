<?php

namespace app\behaviors;

use Yii;
use app\models\Currency;
use app\models\User;
use yii\base\Event;
use yii\behaviors\AttributeBehavior;
use yii\db\ActiveRecord;

/**
 * Class SetDefaultCurrencyBehavior
 *
 * @package app\behaviors
 */
class SetDefaultCurrencyBehavior extends AttributeBehavior
{
    /** @var \app\modules\bot\models\User */
    public $telegramUser;

    /**
     * @param Event $event
     *
     * @return string
     */
    protected function getValue($event)
    {
        if (!$this->telegramUser) {
            throw new \Exception('You should set the telegramUser property');
        }

        /** @var ActiveRecord $model */
        $model = $event->sender;

        if (!$model->currency_id) {
            $user = User::findOne($this->telegramUser);

            if ($user->currency_id) {
                return $user->currency_id;
            } elseif ($currencyCode = (Yii::$app->params['currency'] ?? '')) {
                $currency = Currency::findOne(['code' => $currencyCode]);

                if ($currency) {
                    return $currency->id;
                }
            }
        }

        return $model->currency_id;
    }
}
