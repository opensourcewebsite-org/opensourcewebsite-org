<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\filters\GroupCreatorAccessFilter;
use app\modules\bot\models\ChatSetting;
use Yii;

/**
 * Class GroupInviterController
 *
 * @package app\modules\bot\controllers\privates
 */
class GroupInviterController extends Controller
{
    public function behaviors()
    {
        return [
            'groupCreatorAccess' => [
                'class' => GroupCreatorAccessFilter::class,
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
                            'text' => $chat->isInviterOn() ? Emoji::STATUS_ON . ' ON' : Emoji::STATUS_OFF . ' OFF',
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-reward-amount', [
                                'id' => $chat->id,
                            ]),
                            'text' => Emoji::EDIT . ' ' . Yii::t('bot', 'Reward amount') . ($chat->inviter_reward_amount ? ': ' . $chat->getDisplayRewardAmount() : ''),
                        ],
                    ],
                                        [
                        [
                            'callback_data' => self::createRoute('set-wallet', [
                                'id' => $chat->id,
                            ]),
                            'text' => Emoji::EDIT . ' ' . Yii::t('bot', 'Wallet') . ($chat->inviter_wallet_id ? ': ' . $chat->getDisplayRewardAmount() : ''),
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

        switch ($chat->inviter_status) {
            case ChatSetting::STATUS_ON:
                $chat->inviter_status = ChatSetting::STATUS_OFF;

                break;
            case ChatSetting::STATUS_OFF:
                if (!$chatMember->trySetChatSetting('inviter_status', ChatSetting::STATUS_ON)) {
                    return $this->getResponseBuilder()
                        ->answerCallbackQuery(
                            $this->render('alert-status-on', [
                                'requiredRating' => $chatMember->getRequiredRatingForChatSetting('inviter_status', ChatSetting::STATUS_ON),
                            ]),
                            true
                        )
                        ->build();
                }

                break;
        }

        return $this->actionIndex($chat->id);
    }

    /**
     * @param int $id Chat->id
     * @return array
     */
    public function actionSetRewardAmount($id = null)
    {
        $chat = Yii::$app->cache->get('chat');

        $this->getState()->setInputRoute(self::createRoute('input-reward-amount', [
            'id' => $chat->id,
        ]));

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set-reward-amount', [
                    'chat' => $chat,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('index', [
                                'id' => $chat->id,
                            ]),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }

       /**
     * @param int $id Chat->id
     * @return array
     */
    public function actionInputRewardAmount($id = null)
    {
        $chat = Yii::$app->cache->get('chat');

        if ($this->getUpdate()->getMessage()) {
            if ($text = $this->getUpdate()->getMessage()->getText()) {
                if ($chat->validateSettingValue('inviter_reward_amount', $text)) {
                    $chat->inviter_reward_amount = $text;

                    return $this->runAction('index', [
                        'id' => $chat->id,
                    ]);
                }
            }
        }

        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }
}
