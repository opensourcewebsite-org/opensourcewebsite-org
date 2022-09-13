<?php

declare(strict_types=1);

namespace app\models\events;

use app\models\User;
use yii\base\Event;

class ViewedByUserEvent extends Event
{
    public User $user;
}
