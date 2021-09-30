<?php

namespace tests\models;

use app\models\User;

class UserTest extends \Codeception\Test\Unit
{
    public function testFindUserById()
    {
        expect_that($user = User::findById(100));
        expect($user->username)->equals('admin');

        expect_not(User::findById(999));
    }

    public function testFindUserByUsername()
    {
        expect_that($user = User::findByUsername('admin'));
        expect_not(User::findByUsername('not-admin'));
    }

    public function testValidateUser()
    {
        $user = User::findByUsername('admin');

        expect_that($user->validateAuthKey('test100key'));
        expect_not($user->validateAuthKey('test102key'));

        expect_that($user->validatePassword('admin'));
        expect_not($user->validatePassword('123456'));

        $user->username = 'admin';
        expect_that($user->validate('username'));
        $user->username = 'Admin';
        expect_that($user->validate('username'));
        $user->username = 'ADMIN';
        expect_that($user->validate('username'));
        $user->username = 'user_name';
        expect_that($user->validate('username'));
        $user->username = 'user_1_name';
        expect_that($user->validate('username'));
        $user->username = '1username1';
        expect_that($user->validate('username'));
        $user->username = '1user1name1';
        expect_that($user->validate('username'));
        $user->username = '1_user_1_name_1';
        expect_that($user->validate('username'));

        $user->username = 'webmaster';
        expect_not($user->validate('username'));
        $user->username = '_user1name';
        expect_not($user->validate('username'));
        $user->username = 'user1name_';
        expect_not($user->validate('username'));
        $user->username = '_user1name_';
        expect_not($user->validate('username'));
        $user->username = 'user__name';
        expect_not($user->validate('username'));
        $user->username = 'a';
        expect_not($user->validate('username'));
        $user->username = '1';
        expect_not($user->validate('username'));
        $user->username = '11';
        expect_not($user->validate('username'));
    }
}
