<?php

namespace app\controllers;

use yii\web\Controller;

class WebhookController extends Controller
{

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /**
     * @param string $token the bot token
     * @return mixed
     */
    public function actionTelegram($token = '')
    {
        $postdata = file_get_contents('php://input');
        if ($postdata) {
            $postdata = json_decode($postdata, true);
        }
        //\Yii::warning($postdata);
    }
}
