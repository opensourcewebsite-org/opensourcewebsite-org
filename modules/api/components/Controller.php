<?php

namespace app\modules\api\components;

use Yii;

/**
 * Class Controller
 *
 * @package app\modules\api
 */
class Controller extends \yii\rest\Controller
{
    public function behaviors()
    {
        return [
            'corsFilter' => [
                'class' => \yii\filters\Cors::class,
            ],
        ];
    }
}
