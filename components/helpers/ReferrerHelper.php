<?php

namespace app\components\helpers;

use app\models\User;
use yii\base\Component;
use yii\web\Cookie;
use Yii;

class ReferrerHelper extends Component
{
    /**
     * @return Cookie
     */
    public static function getReferrerFromCookie()
    {
        return Yii::$app->request->cookies->get('referrer');
    }

    /**
     * @return string
     */
    public static function getReferrerIdFromCookie()
    {
        return self::getReferrerFromCookie()->value ?? null;
    }

    /**
     * @param User $user
     */
    public static function addReferrer(User $user)
    {
        Yii::$app->response->cookies->add(new Cookie([
            'name' => 'referrer',
            'value' => $user->id,
            'expire' => time() + 365 * 24 * 60 * 60,
        ]));
    }

    /**
     * @param User $user
     */
    public static function changeReferrer(User $user)
    {
        Yii::$app->response->cookies->remove('referrer');

        self::addReferrer($user);
    }
}
