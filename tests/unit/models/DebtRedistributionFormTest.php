<?php

namespace tests\models;

use Yii;
use app\models\DebtRedistribution;
use app\models\DebtRedistributionForm;
use app\models\User;
use app\tests\fixtures\ContactFixture;
use app\tests\fixtures\UserFixture;
use Codeception\Test\Unit;

class DebtRedistributionFormTest extends Unit
{
    // fixture data located in tests/_data/*.php
    public function _fixtures()
    {
        return [
            'user'    => [
                'class'    => UserFixture::className(),
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

        DebtRedistribution::deleteAll();

        $user = User::findOne(['id' => 100]);

        Yii::$app->user->login($user, 3600);
    }

    protected function _after()
    {
        Yii::$app->user->logout();

        parent::_after();
    }

    /**
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @throws \yii\web\NotFoundHttpException
     */
    public function testFindAndSave()
    {
        $model = DebtRedistributionForm::factory();
        $model->load([
            'contactId'   => 1,
            'currency_id' => 1,
            'max_amount'  => 15,
        ], '');
        expect('save() is success', $model->save())->true(); // create any for test

        $model = DebtRedistributionForm::findModel($model->id);
        expect('DebtRedistributionForm::getModel() works fine', $model)->notEmpty();
        expect('attribute "max_amount" is correct', $model->max_amount)->equals(15);
        expect('attribute "currency_id" is correct', $model->currency_id)->equals(1);

        $model->load([
            'contactId'   => 1,
            'currency_id' => 108,
            'max_amount'  => 20,
        ], '');
        expect('save() is success', $model->save())->true();

        $model = DebtRedistributionForm::findModel($model->id);
        expect('DebtRedistributionForm::getModel() works fine', $model)->notEmpty();
        expect('attribute "max_amount" is correct', $model->max_amount)->equals(20);
        expect('attribute "currency_id" is correct', $model->currency_id)->equals(108);
    }

    /**
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function testIsSenseToStore()
    {
        $model = DebtRedistributionForm::factory();
        $model->load([
            'contactId'   => 1,
            'currency_id' => 1,
            'max_amount'  => DebtRedistributionForm::MAX_AMOUNT_DENY,
        ], '');

        expect('save() is false, because no sense to store default values', $model->save())->false();

        $model->max_amount = 9; //any value not '0'
        expect('save() is success', $model->save())->true();
        $exists = DebtRedistributionForm::find()->where(['id' => $model->id])->exists();
        expect('model is exist in DB', $exists)->true();

        $model->max_amount = DebtRedistributionForm::MAX_AMOUNT_DENY;
        expect('save() is success', $model->save())->true();
        $exists = DebtRedistributionForm::find()->where(['id' => $model->id])->exists();
        expect('model is NOT exist in DB, because no sense to store default values - it was deleted', $exists)->false();
    }

    /**
     * @param $valid
     * @param $data
     * @param $newAttributes
     * @param array $errorAttr
     *
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     *
     * @dataProvider getData
     */
    public function testValidation($valid, $data, $newAttributes, $errorAttr = [])
    {
        $model = DebtRedistributionForm::factory();
        $model->load($data, '');

        expect('validate()', $model->validate())->equals($valid);

        if ($valid) {
            foreach ($newAttributes as $attr => $v) {
                expect($attr, $model->$attr)->equals($v);
            }

            expect('save()', $model->save(false))->true();
            $exists = DebtRedistributionForm::find()->where(['id' => $model->id])->exists();
            expect('model is exist in DB', $exists)->true();
        } else {
            foreach ($errorAttr as $attr) {
                expect("attribute '$attr' has error", $model->hasErrors($attr))->true();
            }
        }
    }

    public function getData(): array
    {
        return [
            //VALID:
            "max_amount: '' => null" => [
                'valid' => true,
                ['contactId' => 1, 'currency_id' => 1, 'max_amount' => ''  ],
                ['contactId' => 1, 'currency_id' => 1, 'max_amount' => null],
            ],
            'max_amount: null => null' => [
                'valid' => true,
                ['contactId' => 1, 'currency_id' => 1, 'max_amount' => null],
                ['contactId' => 1, 'currency_id' => 1, 'max_amount' => null],
            ],
            'not empty: 5'          => [
                'valid' => true,
                ['contactId' => 1, 'currency_id' => 1, 'max_amount' => 5],
                ['contactId' => 1, 'currency_id' => 1, 'max_amount' => 5],
            ],
            'max_amount can be decimal'                         => [
                'valid' => true,
                ['contactId' => 1, 'currency_id' => 1, 'max_amount' => 5.4],
                ['contactId' => 1, 'currency_id' => 1, 'max_amount' => 5.4],
            ],

            //INVALID:
            'invalid: -5'                         => [
                'valid' => false,
                ['contactId' => 1, 'currency_id' => 1, 'max_amount' => -5],
                [],
                ['max_amount'],
            ],
            'invalid: is not number'                      => [
                'valid' => false,
                ['contactId' => 1, 'currency_id' => 1, 'max_amount' => 'text'],
                [],
                ['max_amount'],
            ],
            //currency
            "invalid: currency_id = ''"               => [
                'valid' => false,
                ['contactId' => 1, 'currency_id' => '', 'max_amount' => 5],
                [],
                ['currency_id'],
            ],
            'invalid: currency_id = null'               => [
                'valid' => false,
                ['contactId' => 1, 'currency_id' => null, 'max_amount' => 5],
                [],
                ['currency_id'],
            ],
            'invalid: currency_id = set, but not exist' => [
                'valid' => false,
                ['contactId' => 1, 'currency_id' => 99999, 'max_amount' => 5],
                [],
                ['currency_id'],
            ],

            //contact
            "invalid: contactId = ''"               => [
                'valid' => false,
                ['contactId' => '', 'currency_id' => 1, 'max_amount' => 5],
                [],
                ['contactId'],
            ],
            'invalid: contactId = null'               => [
                'valid' => false,
                ['contactId' => null, 'currency_id' => 1, 'max_amount' => 5],
                [],
                ['contactId'],
            ],
            'invalid: contactId = set, but not exist' => [
                'valid' => false,
                ['contactId' => 999, 'currency_id' => 1, 'max_amount' => 5],
                [],
                ['contactId'],
            ],
            'invalid: `contact` belongs not to current user' => [
                'valid' => false,
                ['contactId' => 4, 'currency_id' => 1, 'max_amount' => 5],
                [],
                ['contactId'],
            ],
            'invalid: `contact`.`link_user_id` is empty' => [
                'valid' => false,
                ['contactId' => 2, 'currency_id' => 1, 'max_amount' => 5],
                [],
                ['contactId'],
            ],
            'invalid: `contact`.`link_user_id` is NOT empty, but not exist' => [
                'valid' => false,
                ['contactId' => 3, 'currency_id' => 1, 'max_amount' => 5],
                [],
                ['contactId'],
            ],
        ];
    }
}
