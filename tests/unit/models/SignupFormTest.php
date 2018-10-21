<?php

namespace tests\models;

use app\models\SignupForm;
use Codeception\Test\Unit;

class SignupFormTest extends Unit
{

    public $tester;
    private $model;

    public function testSignupWithNoCredentials()
    {
        $this->model = new SignupForm([
            'email' => '',
            'password' => '',
        ]);

        expect_not($this->model->signup());
        expect_that(\Yii::$app->user->isGuest);
        expect($this->model->errors)->hasKey('email');
        expect($this->model->errors)->hasKey('password');
    }

    public function testSignupWithEmailAlreadyInUse()
    {
        $this->model = new SignupForm([
            'email' => 'demo@example.com',
            'password' => 'webmaster',
        ]);

        expect_not($this->model->signup());
        expect_that(\Yii::$app->user->isGuest);
        expect($this->model->errors)->hasKey('email');
    }

    public function testSignupWithValidCredentials()
    {
        $this->model = new SignupForm([
            'email' => 'user1@example.com',
            'password' => 'webmaster',
        ]);

        expect_that($this->model->signup());
        expect($this->model->errors)->hasntKey('email');
        expect($this->model->errors)->hasntKey('password');
    }

    public function testIsSignupConfirmationEmailSend()
    {
        $this->model = new SignupForm([
            'email' => 'user2@example.com',
            'password' => 'webmaster',
        ]);

        $user = $this->model->signup();
        expect_that($user->sendConfirmationEmail($user));
        expect($this->model->errors)->hasntKey('email');
        expect($this->model->errors)->hasntKey('password');

        $this->tester->seeEmailIsSent();
        $email = $this->tester->grabLastSentEmail();
        expect($email->getTo())->hasKey($user->email);
        expect($email->getSubject())->equals('Register for My Application');
    }

    protected function _after()
    {
        \Yii::$app->user->logout();
    }
}
