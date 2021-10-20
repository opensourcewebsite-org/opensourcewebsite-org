<?php

namespace tests\models;

use Yii;
use app\models\forms\LoginForm;
use app\models\Rating;
use app\models\forms\SignupForm;
use app\models\User;
use Codeception\Test\Unit;

class SignupFormTest extends Unit
{
    public $tester;
    private $model;

    public function testSignupWithNoCredentials()
    {
        $this->model = new SignupForm([
            'username' => '',
            'password' => '',
            'password_repeat' => '',
        ]);

        expect_not($this->model->signup());
        expect_that(\Yii::$app->user->isGuest);
        expect($this->model->errors)->hasKey('username');
        expect($this->model->errors)->hasKey('password');
    }

    public function testSignupWithUsernameAlreadyInUse()
    {
        $this->model = new SignupForm([
            'username' => 'webmaster',
            'password' => 'webmaster',
            'password_repeat' => 'webmaster',
        ]);

        expect_not($this->model->signup());
        expect_that(\Yii::$app->user->isGuest);
        expect($this->model->errors)->hasKey('username');
    }

    public function testSignupWithCorrectCredentials()
    {
        $this->model = new SignupForm([
            'username' => 'user1',
            'password' => 'webmaster',
            'password_repeat' => 'webmaster',
        ]);

        expect_that($this->model->signup());
        expect($this->model->errors)->hasNotKey('username');
        expect($this->model->errors)->hasNotKey('password');
    }

    public function testSignupWithMismatchedPasswords()
    {
        $this->model = new SignupForm([
            'username' => 'webmaster',
            'password' => 'webmaster',
            'password_repeat' => 'demo1',
        ]);

        expect_not($this->model->signup());
        expect_that(\Yii::$app->user->isGuest);
        expect($this->model->errors)->hasKey('password_repeat');
    }

    protected function _after()
    {
        Rating::deleteAll();

        Yii::$app->user->logout();
    }
}
