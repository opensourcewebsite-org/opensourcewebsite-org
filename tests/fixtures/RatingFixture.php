<?php

namespace app\tests\fixtures;

use app\models\Rating;
use yii\test\ActiveFixture;

class RatingFixture extends ActiveFixture
{
    public $modelClass = Rating::class;
    public $depends = ['app\tests\fixtures\UserFixture'];
}