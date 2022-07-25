<?php

namespace tests\models;

use app\models\Setting;

class SettingTest extends \Codeception\Test\Unit
{
    public function testValidateSetting()
    {
        $this->model = new Setting();

        $this->model->key = 'key_key';
        expect_that($this->model->validate('key'));
        $this->model->key = 'key_key_key';
        expect_that($this->model->validate('key'));
        $this->model->key = 'key';
        expect_that($this->model->validate('key'));

        $this->model->key = '_key';
        expect_not($this->model->validate('key'));
        $this->model->key = 'key_';
        expect_not($this->model->validate('key'));
        $this->model->key = '_key_';
        expect_not($this->model->validate('key'));
        $this->model->key = 'key__key';
        expect_not($this->model->validate('key'));
        $this->model->key = 'key_1_key';
        expect_not($this->model->validate('key'));
        $this->model->key = '1key_key1';
        expect_not($this->model->validate('key'));
        $this->model->key = 'a';
        expect_not($this->model->validate('key'));
        $this->model->key = '1';
        expect_not($this->model->validate('key'));
        $this->model->key = '11';
        expect_not($this->model->validate('key'));
    }
}
