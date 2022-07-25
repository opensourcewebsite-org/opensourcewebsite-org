<?php

namespace app\tests\fixtures;

use app\models\Moqup;
use yii\test\ActiveFixture;

class MoqupFixture extends ActiveFixture
{
    public $modelClass = Moqup::class;
    public $depends = ['app\tests\fixtures\UserFixture'];
}