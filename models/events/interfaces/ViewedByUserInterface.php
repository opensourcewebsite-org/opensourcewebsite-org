<?php
declare(strict_types=1);

namespace app\models\events\interfaces;

use app\models\User;
use yii\base\Event;

interface ViewedByUserInterface {
    const EVENT_VIEWED_BY_USER = 'viewedByUser';

    function markViewedByUser(Event $event);
}
