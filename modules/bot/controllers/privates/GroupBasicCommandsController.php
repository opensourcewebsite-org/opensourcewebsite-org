<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\filters\GroupActiveAdministratorAccessFilter;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;
use Yii;

/**
 * Class GroupBasicCommandsController
 *
 * @package app\modules\bot\controllers\privates
 */
class GroupBasicCommandsController extends Controller
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

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'chat' => $chat,
                ]),
                [
                        [
                            [
                                'callback_data' => self::createRoute('set-status', [
                                    'id' => $chat->id,
                                ]),
                                'text' => $chat->isBasicCommandsOn() ? Emoji::STATUS_ON . ' ON' : Emoji::STATUS_OFF . ' OFF',
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

        switch ($chat->basic_commands_status) {
            case ChatSetting::STATUS_ON:
                $chat->basic_commands_status = ChatSetting::STATUS_OFF;

                break;
            case ChatSetting::STATUS_OFF:
                $chat->basic_commands_status = ChatSetting::STATUS_ON;

                break;
        }

        return $this->actionIndex($chat->id);
    }
}
