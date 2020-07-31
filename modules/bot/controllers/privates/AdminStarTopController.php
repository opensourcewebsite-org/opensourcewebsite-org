<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\BotRouteAlias;
use app\modules\bot\controllers\publics\TopController;
use app\modules\bot\components\actions\privates\wordlist\WordlistAdminComponent;
use app\modules\bot\components\helpers\PaginationButtons;
use yii\data\Pagination;
use app\modules\bot\components\helpers\Emoji;

/**
 * Class AdminStarTopController
 *
 * @package app\controllers\bot
 */
class AdminStarTopController extends Controller
{
    public function actions()
    {
        return array_merge(
            parent::actions(),
            Yii::createObject([
                'class' => WordlistAdminComponent::className(),
                'wordModelClass' => BotRouteAlias::className(),
                'actionGroupName' => 'likewords',
                'modelAttributes' => [
                    'route' => TopController::createRoute('start-like')
                ]
            ])->actions(),
            Yii::createObject([
                'class' => WordlistAdminComponent::className(),
                'wordModelClass' => BotRouteAlias::className(),
                'actionGroupName' => 'dislikewords',
                'modelAttributes' => [
                    'route' => TopController::createRoute('start-dislike')
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

        $statusSetting = $chat->getSetting(ChatSetting::STAR_TOP_STATUS);

        if (!isset($statusSetting)) {
            $statusSetting = new ChatSetting();

            $statusSetting->setAttributes([
                'chat_id' => $chatId,
                'setting' => ChatSetting::STAR_TOP_STATUS,
                'value' => ChatSetting::STAR_TOP_STATUS_OFF,
            ]);

            $statusSetting->save();
        }

        $chatTitle = $chat->title;
        $statusOn = ($statusSetting->value == ChatSetting::STAR_TOP_STATUS_ON);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', compact('chatTitle')),
                [
                        [
                            [
                                'callback_data' => self::createRoute('update', [
                                    'chatId' => $chatId,
                                ]),
                                'text' => Yii::t('bot', 'Status') . ': ' . Yii::t('bot', ($statusOn ? 'ON' : 'OFF')),
                            ],
                        ],
                        [
                            [
                                'callback_data' => self::createRoute('likewords-word-list', [
                                    'chatId' => $chatId,
                                ]),
                                'text' => Yii::t('bot', 'Aliases for') . ' «+»',
                            ],
                        ],
                        [
                            [
                                'callback_data' => self::createRoute('dislikewords-word-list', [
                                    'chatId' => $chatId,
                                ]),
                                'text' => Yii::t('bot', 'Aliases for') . ' «-»',
                            ],
                        ],
                        [
                            [
                                'callback_data' => AdminChatController::createRoute('index', [
                                    'chatId' => $chatId,
                                ]),
                                'text' => Emoji::BACK,
                            ],
                        ]
                ]
            )
            ->build();
    }

    public function actionUpdate($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        $statusSetting = $chat->getSetting(ChatSetting::STAR_TOP_STATUS);

        if ($statusSetting->value == ChatSetting::STAR_TOP_STATUS_ON) {
            $statusSetting->value = ChatSetting::STAR_TOP_STATUS_OFF;
        } else {
            $statusSetting->value = ChatSetting::STAR_TOP_STATUS_ON;
        }

        $statusSetting->save();

        return $this->actionIndex($chatId);
    }
}
