<?php

namespace tests\models;

use app\models\forms\LoginForm;
use app\tests\fixtures\RatingFixture;
use app\tests\fixtures\UserFixture;
use Yii;

class LoginFormTest extends \Codeception\Test\Unit
{
    private $model;

    public function _fixtures()
    {
        return [
            'user' => [
                'class' => UserFixture::className(),
                // fixture data located in tests/_data/user.php
                'dataFile' => codecept_data_dir() . 'user.php',
            ],
            'rating' => [
                'class' => RatingFixture::className(),
                // fixture data located in tests/_data/rating.php
                'dataFile' => codecept_data_dir() . 'rating.php',
            ],
        ];
    }

    public function testLoginNoUser()
    {
        $this->model = new LoginForm([
            'username' => 'not_existing_username',
            'password' => 'not_existing_password',
        ]);

        expect_not($this->model->login());
        expect_that(\Yii::$app->user->isGuest);
    }

    public function testLoginWrongUsername()
    {
        $this->model = new LoginForm([
            'username' => 'admin',
            'password' => 'webmaster',
        ]);

        expect_not($this->model->login());
        expect_that(\Yii::$app->user->isGuest);
        expect($this->model->errors)->hasKey('password');
    }

    public function testLoginWrongId()
    {
        $this->model = new LoginForm([
            'username' => '999',
            'password' => 'webmaster',
        ]);

        expect_not($this->model->login());
        expect_that(\Yii::$app->user->isGuest);
        expect($this->model->errors)->hasKey('password');
    }

    public function testLoginWrongEmail()
    {
        $this->model = new LoginForm([
            'username' => 'demo@example.com',
            'password' => 'webmaster',
        ]);

        expect_not($this->model->login());
        expect_that(\Yii::$app->user->isGuest);
        expect($this->model->errors)->hasKey('password');
    }

    public function testLoginWrongPassword()
    {
        $this->model = new LoginForm([
            'username' => 'webmaster',
            'password' => 'wrong_password',
        ]);

        expect_not($this->model->login());
        expect_that(\Yii::$app->user->isGuest);
        expect($this->model->errors)->hasKey('password');
    }

    public function testLoginCorrectUsername()
    {
        $this->model = new LoginForm([
            'username' => 'webmaster',
            'password' => 'webmaster',
        ]);

        expect_that($this->model->login());
        expect_not(\Yii::$app->user->isGuest);
        expect($this->model->errors)->hasNotKey('password');
    }

    public function testLoginCorrectId()
    {
        $this->model = new LoginForm([
            'username' => '101',
            'password' => 'webmaster',
        ]);

        expect_that($this->model->login());
        expect_not(\Yii::$app->user->isGuest);
        expect($this->model->errors)->hasNotKey('password');
    }

    public function testLoginCorrectEmail()
    {
        $this->model = new LoginForm([
            'username' => 'webmaster@example.com',
            'password' => 'webmaster',
        ]);

        expect_that($this->model->login());
        expect_not(\Yii::$app->user->isGuest);
        expect($this->model->errors)->hasNotKey('password');
    }

    protected function _after()
    {
        Yii::$app->user->logout();
    }
}
