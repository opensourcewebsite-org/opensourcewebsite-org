<?php

namespace app\modules\bot\controllers;

use app\modules\bot\telegram\Message;

/**
 * Class My_profileController
 *
 * @package app\modules\bot\controllers
 */
class My_profileController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        /** @var Message $requestMessage */
        $requestMessage = \Yii::$app->requestMessage;

        return $this->render('index', ['profile' => $requestMessage->getFrom()]);
    }
}
