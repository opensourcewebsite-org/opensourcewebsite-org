<?php

namespace models;

use Yii;
use app\models\User;
use app\models\Contact;
use app\tests\fixtures\UserFixture;
use app\tests\fixtures\ContactFixture;

class ContactTest extends \Codeception\Test\Unit
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

        $user = User::findOne(['id' => 100]);

        Yii::$app->user->login($user, 3600);
    }

    protected function _after()
    {
        Yii::$app->user->logout();

        parent::_after();
    }

    public function testCreateWithOwnerAndLinkedUsersSame()
    {
        $contact = new Contact();

        $contact->user_id = "103";
        $contact->userIdOrName = "103";

        expect("Contact can't be saved because owner and linked users can't be same", $contact->save())->false();
        expect("Attribute 'userIdOrName' is not valid because it's equal to user owner", $contact->hasErrors("userIdOrName"))->true();
    }

    public function testUpdateWithOwnerAndLinkedUsersSame()
    {
        $contact = Contact::find()->where(['id' => 1])->one();

        $contact->userIdOrName = (string) $contact->user_id;

        expect("Contact can't be updated because owner and linked users can't be same", $contact->save())->false();
        expect("Attribute 'userIdOrName' is not valid because it's equal to user owner", $contact->hasErrors("userIdOrName"))->true();
    }

    public function testCreateContacts()
    {
        $prevContact = new Contact();
        $prevContact->user_id = Yii::$app->user->id;
        $prevContact->userIdOrName = "102";
        expect("prevContact save() is success", $prevContact->save())->true();
        expect("prevContact is user", $prevContact->isUser())->true();

        $newContact = new Contact();
        $newContact->name = 'name';
        $newContact->user_id = Yii::$app->user->id;
        expect("newContact save() is success", $newContact->save())->true();
        expect("newContact is non-user", $newContact->isNonUser())->true();
    }
}
