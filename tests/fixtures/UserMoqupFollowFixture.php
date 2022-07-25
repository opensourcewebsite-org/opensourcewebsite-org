<?php

namespace app\tests\fixtures;

use app\models\UserMoqupFollow;
use yii\test\ActiveFixture;

class UserMoqupFollowFixture extends ActiveFixture
{
    public $modelClass = UserMoqupFollow::class;
    public $depends = ['app\tests\fixtures\UserFixture','app\tests\fixtures\MoqupFixture'];
}