<?php

namespace tests\models;

use app\models\LoginForm;
use app\tests\fixtures\CssFixture;
use app\tests\fixtures\MoqupFixture;
use app\tests\fixtures\UserFixture;
use app\tests\fixtures\UserMoqupFollowFixture;
use app\tests\fixtures\RatingFixture;

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
                // fixture data located in tests/_data/moqup.php
                'dataFile' => codecept_data_dir() . 'rating.php',
            ],
            'moqup' => [
                'class' => MoqupFixture::className(),
                // fixture data located in tests/_data/moqup.php
                'dataFile' => codecept_data_dir() . 'moqup.php',
            ],
            'user_moqup' => [
                'class' => UserMoqupFollowFixture::className(),
                // fixture data located in tests/_data/user_moqup_follow.php
                'dataFile' => codecept_data_dir() . 'user_moqup_follow.php',
            ],
            'css' => [
                'class' => CssFixture::className(),
                // fixture data located in tests/_data/user_moqup_follow.php
                'dataFile' => codecept_data_dir() . 'css.php',
            ],
        ];
    }
	
    public function testLoginNoUser()
    {
        $this->model = new LoginForm([
            'email' => 'not_existing_email',
            'password' => 'not_existing_password',
        ]);

        expect_not($this->model->login());
        expect_that(\Yii::$app->user->isGuest);
    }

    public function testLoginWrongEmail()
    {
        $this->model = new LoginForm([
            'email' => 'example@demo.com',
            'password' => 'webmaster',
        ]);

        expect_not($this->model->login());
        expect_that(\Yii::$app->user->isGuest);
        expect($this->model->errors)->hasKey('password');
    }

    public function testLoginWrongPassword()
    {
        $this->model = new LoginForm([
            'email' => 'demo@example.com',
            'password' => 'wrong_password',
        ]);

        expect_not($this->model->login());
        expect_that(\Yii::$app->user->isGuest);
        expect($this->model->errors)->hasKey('password');
    }

    public function testLoginCorrect()
    {
        $this->model = new LoginForm([
            'email' => 'demo@example.com',
            'password' => 'webmaster',
        ]);

        expect_that($this->model->login());
        expect_not(\Yii::$app->user->isGuest);
        expect($this->model->errors)->hasntKey('password');
    }

    protected function _after()
    {
        \Yii::$app->user->logout();
    }

}
