<?php

namespace app\tests\fixtures;

use app\models\Css;
use yii\test\ActiveFixture;

class CssFixture extends ActiveFixture
{
    public $modelClass = Css::class;
    public $depends = ['app\tests\fixtures\MoqupFixture'];
}