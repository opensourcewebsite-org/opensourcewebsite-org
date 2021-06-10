<?php
declare(strict_types=1);

namespace app\models\events\interfaces;

use app\models\events\ViewedByUserEvent;

interface ViewedByUserInterface {
    const EVENT_VIEWED_BY_USER = 'viewedByUser';

    function markViewedByUser(ViewedByUserEvent $event);
}
