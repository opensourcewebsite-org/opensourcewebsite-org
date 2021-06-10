<?php
declare(strict_types=1);

use yii\web\Application;

return [
    'on beforeRequest' => function ($event) {
        /** @var Application $app */
        $app = $event->sender;

        /** @var app\models\User|null $user */
        $user = $app->user->getIdentity();

        if (($user !== null) && !(Yii::$app->user->isGuest)) {
            $user->updateLastActivity();
        }
    },
];
