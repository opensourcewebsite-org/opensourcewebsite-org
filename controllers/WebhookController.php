<?php

namespace app\controllers;

use app\models\SupportGroupBot;
use app\modules\bot\WebHookAction;
use app\services\WebHookService;
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
    public WebHookService $webHookService;

    public function __construct()
    {
        parent::__construct(...func_get_args());

        $this->webHookService = new WebHookService();
    }

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

    /**
     * @param string $token the bot token
     *
     * @return mixed
     */
    public function actionTelegram($token = '')
    {
        $result = false;
        try {
            $postdata = file_get_contents('php://input');
            if ($postdata) {
                $postdata = json_decode($postdata, true);

                $botInfo = SupportGroupBot::findOne(['token' => $token]);
                if ($botInfo) {
                    $result = $this->webHookService->handleSupportGroupBot($botInfo, $postdata);
                } else {
                    throw new NotFoundHttpException('The requested page does not exist.');
                }
            }
        } catch (\Exception $e) {
            Yii::error($e->getMessage());
        }

        return $result;
    }
}
