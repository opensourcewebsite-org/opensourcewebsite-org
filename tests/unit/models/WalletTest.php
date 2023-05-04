<?php

namespace models;

use app\models\Contact;
use app\models\Currency;
use app\models\User;
use app\models\Wallet;
use app\models\WalletTransaction;
use app\tests\fixtures\ContactFixture;
use app\tests\fixtures\UserFixture;
use Yii;

class WalletTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    // fixture data located in tests/_data/*.php
    public function _fixtures()
    {
        return [
            'user'    => [
                'class'    => UserFixture::class,
                'dataFile' => codecept_data_dir() . 'user.php',
            ],
            'contact' => [
                'class'    => ContactFixture::className(),
                'dataFile' => codecept_data_dir() . 'contact.php',
            ],
        ];
    }

    protected function _before()
    {
        parent::_before();
    }

    protected function _after()
    {
        parent::_after();
    }

    public function testRegularWalletTransaction()
    {
        $sourceUser = new User([
            'auth_key' => Yii::$app->security->generateRandomString(12),
            'password_hash' => Yii::$app->security->generateRandomString(12),
        ]);
        $sourceUser->save();

        $targetUser = new User([
            'auth_key' => Yii::$app->security->generateRandomString(12),
            'password_hash' => Yii::$app->security->generateRandomString(12),
        ]);
        $targetUser->save();

        $currency = Currency::findOne(1);

        $sourceWalletAmount = 134.84;

        $sourceUserWallet = new Wallet([
            'currency_id' => $currency->id,
            'user_id' => $sourceUser->id,
            'amount' => $sourceWalletAmount,
        ]);
        $sourceUserWallet->save();

        $walletTransaction = new WalletTransaction([
            'from_user_id' => $sourceUser->id,
            'to_user_id' => $targetUser->id,
            'currency_id' => $currency->id,
            'type' => 0,
            'anonymity' => 0,
        ]);

        $walletTransaction->amount = $sourceWalletAmount;
        expect("Cannot make transaction with amoun equal source wallet amount, because there is commission", $walletTransaction->createTransaction())->false();
        $walletTransaction->amount = 0;
        expect("Cannot make transaction with zero amount", $walletTransaction->createTransaction())->false();
        $walletTransaction->amount = $sourceWalletAmount + 1;
        expect("Cannot make transaction with amount greater than wallet amount", $walletTransaction->createTransaction())->false();
        $walletTransaction->amount = $sourceWalletAmount / 2;
        expect("Can make transaction with valid amount", $walletTransaction->createTransaction())->numeric();
    }

    public function testCannotCreateSameCurrencyWalletsForSingleUser()
    {
        $firstUser = new User([
            'auth_key' => Yii::$app->security->generateRandomString(12),
            'password_hash' => Yii::$app->security->generateRandomString(12),
        ]);
        $firstUser->save();

        $firstUserWallet = new Wallet([
            'currency_id' => 1,
        ]);

        $firstUserWallet->save();

        $secondUserWallet = new Wallet([
            'currency_id' => 1,
        ]);

        expect("Contact can't be saved because owner and linked users can't be same", $secondUserWallet->save())->false();
    }
}
