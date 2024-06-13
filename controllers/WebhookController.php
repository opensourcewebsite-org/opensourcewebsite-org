<?php

namespace app\controllers;

use app\modules\bot\WebHookAction;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Class WebhookController
 *
 * @package app\controllers
 */
class WebhookController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;

        return parent::beforeAction($action);
    }

    public function actions()
    {
        return [
            'telegram-bot' => WebHookAction::class,
        ];
    }
}
