<?php

namespace tests\models;

use app\models\DebtRedistribution;
use app\models\DebtRedistributionForm;
use app\models\User;
use app\tests\fixtures\ContactFixture;
use app\tests\fixtures\UserFixture;
use Codeception\Test\Unit;
use Yii;

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

        $user = User::findOne(['email' => 'admin@example.com']); // id=100
        Yii::$app->user->login($user, 3600);
    }

    protected function _after()
    {
        Yii::$app->user->logout();

        parent::_after();
    }

    public function testFindAndSave()
    {
        $model = new DebtRedistributionForm();
        $model->load([
            'contactId'  => 1,
            'max_amount' => 15,
            'priority'   => 5,
        ], '');
        expect('save() is success', $model->save())->true(); // create any for test

        $model = DebtRedistributionForm::getModel($model->id);
        expect('DebtRedistributionForm::getModel() works fine', $model)->notEmpty();
        expect('attribute "max_amount" is correct', $model->max_amount)->equals(15);
        expect('attribute "priority" is correct', $model->priority)->equals(5);

        $model->load([
            'contactId'  => 1,
            'max_amount' => 20,
            'priority'   => 15,
        ], '');
        expect('save() is success', $model->save())->true();

        $model = DebtRedistributionForm::getModel($model->id);
        expect('DebtRedistributionForm::getModel() works fine', $model)->notEmpty();
        expect('attribute "max_amount" is correct', $model->max_amount)->equals(20);
        expect('attribute "priority" is correct', $model->priority)->equals(15);
    }

    public function testIsSenseToStore()
    {
        $model = new DebtRedistributionForm();
        $model->load([
            'contactId'  => 1,
            'max_amount' => DebtRedistributionForm::MAX_AMOUNT_DENY,
            'priority'   => DebtRedistributionForm::PRIORITY_NO,
        ], '');

        expect('save() is success', $model->save())->true();
        expect('model is still NewRecord, because no sense to store default values', $model->isNewRecord)->true();

        $model->priority = 9; //any value not '0'
        expect('save() is success', $model->save())->true();
        $exists = DebtRedistributionForm::find()->where(['id' => $model->id])->exists();
        expect('model is exist in DB', $exists)->true();

        $model->priority = DebtRedistributionForm::PRIORITY_NO;
        expect('save() is success', $model->save())->true();
        $exists = DebtRedistributionForm::find()->where(['id' => $model->id])->exists();
        expect('model is NOT exist in DB, because no sense to store default values', $exists)->false();
    }

    /**
     * @dataProvider getData
     */
    public function testValidation($valid, $data, $newAttributes, $errorAttr = [])
    {

        $model = new DebtRedistributionForm();
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
            "priority: '' => 0"        => [
                'valid' => true,
                ['contactId' => 1, 'max_amount' => 50, 'priority' => ''],
                ['contactId' => 1, 'max_amount' => 50, 'priority' => 0 ],
            ],
            'priority: null => 0'      => [
                'valid' => true,
                ['contactId' => 1, 'max_amount' => 50, 'priority' => null],
                ['contactId' => 1, 'max_amount' => 50, 'priority' => 0   ],
            ],
            "max_amount: '' => null" => [
                'valid' => true,
                ['contactId' => 1, 'max_amount' => ''  , 'priority' => 0],
                ['contactId' => 1, 'max_amount' => null, 'priority' => 0],
            ],
            'max_amount: null => null' => [
                'valid' => true,
                ['contactId' => 1, 'max_amount' => null, 'priority' => 0],
                ['contactId' => 1, 'max_amount' => null, 'priority' => 0],
            ],
            'not empty: 5, 0'          => [
                'valid' => true,
                ['contactId' => 1, 'max_amount' => 5, 'priority' => 0],
                ['contactId' => 1, 'max_amount' => 5, 'priority' => 0],
            ],
            'not empty: 0, 5'                         => [
                'valid' => true,
                ['contactId' => 1, 'max_amount' => 0, 'priority' => 5],
                ['contactId' => 1, 'max_amount' => 0, 'priority' => 5],
            ],
            'invalid: -5, -5'                         => [
                'valid' => false,
                ['contactId' => 1, 'max_amount' => -5, 'priority' => -5],
                [],
                ['max_amount', 'priority'],
            ],
            'invalid: 5.4, text'                      => [
                'valid' => false,
                ['contactId' => 1, 'max_amount' => 5.4, 'priority' => 'text'],
                [],
                ['max_amount', 'priority'],
            ],
            "invalid: contactId = ''"               => [
                'valid' => false,
                ['contactId' => '', 'max_amount' => 5, 'priority' => 5],
                [],
                ['contactId'],
            ],
            'invalid: contactId = null'               => [
                'valid' => false,
                ['contactId' => null, 'max_amount' => 5, 'priority' => 5],
                [],
                ['contactId'],
            ],
            'invalid: contactId = set, but not exist' => [
                'valid' => false,
                ['contactId' => 999, 'max_amount' => 5, 'priority' => 5],
                [],
                ['contactId'],
            ],
            'invalid: `contact` belongs not to current user' => [
                'valid' => false,
                ['contactId' => 4, 'max_amount' => 5, 'priority' => 5],
                [],
                ['contactId'],
            ],
            'invalid: `contact`.`link_user_id` is empty' => [
                'valid' => false,
                ['contactId' => 2, 'max_amount' => 5, 'priority' => 5],
                [],
                ['contactId'],
            ],
            'invalid: `contact`.`link_user_id` is NOT empty, but not exist' => [
                'valid' => false,
                ['contactId' => 3, 'max_amount' => 5, 'priority' => 5],
                [],
                ['contactId'],
            ],
        ];
    }
}
