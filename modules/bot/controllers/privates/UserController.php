<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\models\User;

/**
 * Class UserController
 *
 * @package app\modules\bot\controllers\privates
 */
class UserController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($providerUserId = null)
    {
        if ($providerUserId) {
            $telegramUser = User::findOne([
                'provider_user_id' => $providerUserId,
                'is_bot' => 0,
            ]);

            if (!isset($telegramUser)) {
                return $this->getResponseBuilder()
                    ->answerCallbackQuery()
                    ->build();
            }
        }

        $params = [
            'telegramUser' => $telegramUser,
            'user' => $telegramUser->globalUser,
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', $params),
                [
                    [
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                    ],
                ],
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }
}
