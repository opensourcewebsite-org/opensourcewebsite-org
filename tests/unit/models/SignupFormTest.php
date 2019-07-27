<?php

namespace tests\models;

use app\models\LoginForm;
use app\models\Rating;
use app\models\SignupForm;
use app\models\User;
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

    public function testConfirmEmailWithoutLogin()
    {
        expect_that(\Yii::$app->user->isGuest);
        expect_not(SignupForm::confirmEmail(102, 'test102key'));
    }

    public function testConfirmEmailWithWrongLogin()
    {
        $this->model = new LoginForm([
            'email' => 'demo@example.com',
            'password' => 'webmaster',
        ]);

        expect_that($this->model->login());
        expect_not(\Yii::$app->user->isGuest);
        expect_not(SignupForm::confirmEmail(102, 'test102key'));
    }

    public function testConfirmEmailWrongUserId()
    {
        $this->model = new LoginForm([
            'email' => 'newuser@example.com',
            'password' => 'newuser',
        ]);

        expect_that($this->model->login());
        expect_not(\Yii::$app->user->isGuest);

        expect_not(SignupForm::confirmEmail(101, 'test102key'));
    }

    public function testConfirmEmailWrongUserAuthKey()
    {
        $this->model = new LoginForm([
            'email' => 'newuser@example.com',
            'password' => 'newuser',
        ]);

        expect_that($this->model->login());
        expect_not(\Yii::$app->user->isGuest);

        expect_not(SignupForm::confirmEmail(102, 'test100key'));
    }

    public function testConfirmEmailCorrect()
    {
        $this->model = new LoginForm([
            'email' => 'newuser@example.com',
            'password' => 'newuser',
        ]);

        expect_that($this->model->login());
        expect_not(\Yii::$app->user->isGuest);

        expect($user = SignupForm::confirmEmail(102, 'test102key'))->notNull();
        expect($user->is_email_confirmed)->equals(1);
        expect($user->status)->equals(User::STATUS_ACTIVE);
    }

    /**
     * @depends testConfirmEmailCorrect
     */
    public function testAddRatingConfirmEmail($user)
    {
        $user = User::findIdentity(102);
        expect_that($user->addRating(Rating::CONFIRM_EMAIL, 1, false));
        expect($user->getRating())->equals(1);

        expect($user->addRating(Rating::CONFIRM_EMAIL, 1, false))->false();
        expect($user->getRating())->notEquals(2);
    }

    protected function _after()
    {
        \Yii::$app->user->logout();
    }
}
