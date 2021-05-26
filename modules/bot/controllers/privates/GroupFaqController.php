<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\components\actions\privates\wordlist\WordlistComponent;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\BotChatFaqQuestion;
use yii\data\Pagination;
use app\modules\bot\components\helpers\Emoji;

/**
 * Class GroupFaqController
 *
 * @package app\modules\bot\controllers\privates
 */
class GroupFaqController extends Controller
{
    public function actions()
    {
        return array_merge(
            parent::actions(),
            Yii::createObject([
                'class' => WordlistComponent::class,
                'wordModelClass' => BotChatFaqQuestion::class,
                'buttons' => [
                    [
                        'field' => 'answer',
                        'text' => Yii::t('bot', 'Answer'),
                    ],
                ]
            ])->actions()
        );
    }

    /**
     * @return array
     */
    public function actionIndex($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        $chatTitle = $chat->title;

        $statusSetting = $chat->getSetting(ChatSetting::FAQ_STATUS);
        $statusOn = ($statusSetting->value == ChatSetting::FAQ_STATUS_ON);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', compact('chatTitle')),
                [
                        [
                            [
                                'callback_data' => self::createRoute('set-status', [
                                    'chatId' => $chatId,
                                ]),
                                'text' => $statusOn ? Emoji::STATUS_ON . ' ON' : Emoji::STATUS_OFF . ' OFF',
                            ],
                        ],
                        [
                            [
                                'callback_data' => self::createRoute('word-list', [
                                    'chatId' => $chatId,
                                ]),
                                'text' => Yii::t('bot', 'Questions'),
                            ],
                        ],
                        [
                            [
                                'callback_data' => GroupController::createRoute('view', [
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

        $statusSetting = $chat->getSetting(ChatSetting::FAQ_STATUS);

        if ($statusSetting->value == ChatSetting::FAQ_STATUS_ON) {
            $statusSetting->value = ChatSetting::FAQ_STATUS_OFF;
        } else {
            $statusSetting->value = ChatSetting::FAQ_STATUS_ON;
        }

        $statusSetting->save();

        return $this->actionIndex($chatId);
    }
}
