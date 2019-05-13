<?php

namespace tests\models;


class MoqupTest extends \Codeception\Test\Unit
{
    public function testGetUser()
    {
        $moqup = $this->tester->grabFixture('moqup', 0);
        expect_that($user = $moqup->getUser());
        expect_that($user = $user->one());
        expect($user->id)->equals(100);
        expect($user->username)->equals('admin');

        $moqup = $this->tester->grabFixture('moqup', 1);
        expect_that($user = $moqup->getUser());
        expect_that($user = $user->one());
        expect($user->email)->equals('demo@example.com');
        expect($user->username)->equals('webmaster');
    }

    public function testGetCss()
    {
        $moqup = $this->tester->grabFixture('moqup', 0);
        expect_that($css = $moqup->getCss());
        expect_that($css = $css->one());
        expect($css->css)->equals('h1{color:#000}');

        $moqup = $this->tester->grabFixture('moqup', 1);
        expect_that($css = $moqup->getCss());
        expect_that($css = $css->one());
        expect($css->css)->equals('h2{color:#aaa}');

        $moqup = $this->tester->grabFixture('moqup', 2);
        expect_that($css = $moqup->getCss());
        expect_that($css = $css->one());
        expect($css->css)->equals('h3{color:#ff0000}');
    }

    public function testGetFollowers()
    {
        $moqup = $this->tester->grabFixture('moqup', 0);
        expect_that($followers = $moqup->getFollowers());
        expect_that($followers = $followers->asArray()->all());
        expect_that($followers[0]['username']);
        expect($followers[0]['username'])->equals('admin');
        expect($followers[1]['username'])->equals('webmaster');
    }

    public function testGetFollowersNumber()
    {
        $moqup = $this->tester->grabFixture('moqup', 0);
        expect_that($followers = $moqup->getFollowersNumber());
        expect($followers)->equals(2);

        $moqup = $this->tester->grabFixture('moqup', 1);
        expect_that($followers = $moqup->getFollowersNumber());
        expect($followers)->equals(1);
    }

    public function testGetOrigin()
    {
        $moqup = $this->tester->grabFixture('moqup', 0);
        expect_that($origin = $moqup->getOrigin());
        expect_that($origin = $origin->one());
        expect($origin->id)->equals(3);
        expect($origin->title)->equals('test3');

        $moqup = $this->tester->grabFixture('moqup', 2);
        expect_that($origin = $moqup->getOrigin());
        expect_that($origin = $origin->one());
        expect($origin->id)->equals(1);
        expect($origin->title)->equals('test1');
    }

    public function testGetForks()
    {
        $moqup = $this->tester->grabFixture('moqup', 2);
        expect_that($forks = $moqup->getForks());
        expect_that($forks = $forks->asArray()->all());
        expect_that($forks[1]);
        expect($forks[1]['title'])->equals('test2');
        expect_that($forks[0]);
        expect($forks[0]['title'])->equals('test1');

        $moqup = $this->tester->grabFixture('moqup', 0);
        expect_that($forks = $moqup->getForks());
        expect_that($forks = $forks->asArray()->all());
        expect_that($forks[0]);
        expect($forks[0]['title'])->equals('test3');
    }
    public function testGetForksNumber()
    {
        $moqup = $this->tester->grabFixture('moqup', 0);
        expect_that($forks = $moqup->getForksNumber());
        expect($forks)->equals(1);

        $moqup = $this->tester->grabFixture('moqup', 1);
        expect_not($forks = $moqup->getForksNumber());
        expect($forks)->equals(0);

        $moqup = $this->tester->grabFixture('moqup', 2);
        expect_that($forks = $moqup->getForksNumber());
        expect($forks)->equals(2);
    }
}
