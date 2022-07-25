<?php

use yii\db\Migration;
use app\models\Currency;
use app\models\PaymentMethod;
use app\models\PaymentMethodCurrency;

/**
 * Class m210626_083342_add_stablecoins_to_payment_method_currency_table
 */
class m210626_083342_add_stablecoins_to_payment_method_currency_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $currencies = Currency::find()->all();

        foreach ($currencies as $currency) {
            $paymentMethod = new PaymentMethod();
            $paymentMethod->name = 'OSW ' . $currency->code;
            $paymentMethod->type = PaymentMethod::TYPE_STABLECOIN;
            $paymentMethod->url = 'https://stellar.expert/explorer/public/asset/' . $currency->code . '-' . 'GC45AYPXRDYKK75HFWH5CUANMDFLQW34ZAXD5ZRTIAGS262XSBTFTCLH';
            $paymentMethod->save();

            $paymentMethodCurrency = new PaymentMethodCurrency();
            $paymentMethodCurrency->payment_method_id = $paymentMethod->id;
            $paymentMethodCurrency->currency_id = $currency->id;
            $paymentMethodCurrency->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210626_083342_add_stablecoins_to_payment_method_currency_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210626_083342_add_stablecoins_to_payment_method_currency_table cannot be reverted.\n";

        return false;
    }
    */
}
