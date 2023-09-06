<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\filters\GroupActiveAdministratorAccessFilter;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;
use Yii;

/**
 * Class GroupJoinCaptchaController
 *
 * @package app\modules\bot\controllers\privates
 */
class GroupJoinCaptchaController extends Controller
{
    public function behaviors()
    {
        return [
            'groupActiveAdministratorAccess' => [
                'class' => GroupActiveAdministratorAccessFilter::class,
            ],
        ];
    }

    /**
    * @param int $id Chat->id
    * @return array
    */
    public function actionIndex($id = null)
    {
        $chat = Yii::$app->cache->get('chat');

        $this->getState()->clearInputRoute();

        $user = $this->getTelegramUser();

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'chat' => $chat,
                    'user' => $user,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('set-status', [
                                'id' => $chat->id,
                            ]),
                            'text' => $chat->isJoinCaptchaOn() ? Emoji::STATUS_ON . ' ON' : Emoji::STATUS_OFF . ' OFF',
                        ],
                    ],
                    [
                        [
                            'callback_data' => GroupController::createRoute('view', [
                                'chatId' => $chat->id,
                            ]),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                    ]
                ]
            )
            ->build();
    }

    /**
    * @param int $id Chat->id
    * @return array
    */
    public function actionSetStatus($id = null)
    {
        $chat = Yii::$app->cache->get('chat');
        $chatMember = Yii::$app->cache->get('chatMember');

        switch ($chat->join_captcha_status) {
            case ChatSetting::STATUS_ON:
                $chat->join_captcha_status = ChatSetting::STATUS_OFF;

                break;
            case ChatSetting::STATUS_OFF:
                if (!$chatMember->trySetChatSetting('join_captcha_status', ChatSetting::STATUS_ON)) {
                    return $this->getResponseBuilder()
                        ->answerCallbackQuery(
                            $this->render('alert-status-on', [
                                'requiredRating' => $chatMember->getRequiredRatingForChatSetting('join_captcha_status', ChatSetting::STATUS_ON),
                            ]),
                            true
                        )
                        ->build();
                }

                break;
        }

        return $this->actionIndex($chat->id);
    }
}
