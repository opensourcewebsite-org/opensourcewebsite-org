<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\BotRouteAlias;
use app\modules\bot\components\actions\privates\wordlist\WordlistAdminComponent;
use app\modules\bot\controllers\publics\VotebanController;
use app\modules\bot\components\helpers\Emoji;

/**
* Class AdminVoteBanController
*
* @package app\modules\bot\controllers\privates
*/
class AdminVoteBanController extends Controller
{
    public function actions()
    {
        return array_merge(
            parent::actions(),
            Yii::createObject([
                'class' => WordlistAdminComponent::className(),
                'wordModelClass' => BotRouteAlias::className(),
                'modelAttributes' => [
                    'route' => VotebanController::createRoute('index')
                ]
            ])->actions()
        );
    }

    /**
    * @return array
    */
    public function actionIndex($chatId = null)
    {
        $this->actions();
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        $statusSetting = $chat->getSetting(ChatSetting::VOTE_BAN_STATUS);

        if (!isset($statusSetting)) {
            $statusSetting = new ChatSetting();

            $statusSetting->setAttributes([
                'chat_id' => $chatId,
                'setting' => ChatSetting::VOTE_BAN_STATUS,
                'value' => ChatSetting::VOTE_BAN_STATUS_OFF,
            ]);

            $statusSetting->save();
        }

        $chatTitle = $chat->title;
        $statusOn = ($statusSetting->value == ChatSetting::VOTE_BAN_STATUS_ON);

        $limitSetting = $chat->getSetting(ChatSetting::VOTE_BAN_LIMIT);
        $limit = $limitSetting->value ?? ChatSetting::VOTE_BAN_LIMIT_DEFAULT;

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', compact('chatTitle')),
                [
                    [
                        [
                            'callback_data' => self::createRoute('set-status', [
                                'chatId' => $chatId,
                            ]),
                            'text' => Yii::t('bot', 'Status') . ': ' . Yii::t('bot', ($statusOn ? 'ON' : 'OFF')),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-limit', [
                                'chatId' => $chatId,
                            ]),
                            'text' => Yii::t('bot', 'Limit') . ': ' . $limit,
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('word-list', [
                                'chatId' => $chatId,
                            ]),
                            'text' => Yii::t('bot', 'Aliases for') . ' «voteban»',
                        ],
                    ],
                    [
                        [
                            'callback_data' => AdminChatController::createRoute('index', [
                                'chatId' => $chatId,
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

    public function actionSetStatus($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        $statusSetting = $chat->getSetting(ChatSetting::VOTE_BAN_STATUS);

        if ($statusSetting->value == ChatSetting::VOTE_BAN_STATUS_ON) {
            $statusSetting->value = ChatSetting::VOTE_BAN_STATUS_OFF;
        } else {
            $statusSetting->value = ChatSetting::VOTE_BAN_STATUS_ON;
        }

        $statusSetting->save();

        return $this->actionIndex($chatId);
    }

    public function actionSetLimit($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        $this->getState()->setName(self::createRoute('save-limit', [
                'chatId' => $chatId,
            ]));

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set-limit'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('index', [
                                'chatId' => $chatId,
                            ]),
                            'text' => Emoji::BACK,
                        ],
                    ]
                ]
            )
            ->build();
    }

    public function actionSaveLimit($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        $text = (int) $this->getUpdate()->getMessage()->getText();

        if (!(($text >= ChatSetting::VOTE_BAN_LIMIT_MIN) && ($text <= ChatSetting::VOTE_BAN_LIMIT_MAX))) {
            return $this->getResponseBuilder()
                ->deleteMessage()
                ->build();
        }

        $limitSetting = $chat->getSetting(ChatSetting::VOTE_BAN_LIMIT);
        $limitSetting->value = (string) $text;
        $limitSetting->save();

        $this->getState()->setName(null);

        return $this->runAction('index', [
            'chatId' => $chatId,
        ]);
    }
}
