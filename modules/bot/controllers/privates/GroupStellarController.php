<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\validators\StellarAssetValidator;
use app\modules\bot\validators\StellarPublicKeyValidator;
use Yii;
use yii\validators\UrlValidator;

/**
 * Class GroupStellarController
 *
 * @package app\modules\bot\controllers\privates
 */
class GroupStellarController extends Controller
{
    /**
     * @param int|null $id Chat->id
     * @return array
     */
    public function actionIndex($id = null)
    {
        $chat = Chat::findOne($id);

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->clearInputRoute();

        $isModeSigners = ($chat->stellar_mode == ChatSetting::STELLAR_MODE_SIGNERS);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', compact('chat', 'isModeSigners')),
                [
                    [
                        [
                            'callback_data' => self::createRoute('set-status', [
                                'id' => $chat->id,
                            ]),
                            'text' => $chat->stellar_status == ChatSetting::STATUS_ON ? Emoji::STATUS_ON . ' ON' : Emoji::STATUS_OFF . ' OFF',
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-mode', [
                                'id' => $chat->id,
                            ]),
                            'text' => Yii::t('bot', 'Mode') . ': ' . ($isModeSigners ? Yii::t('bot', 'Signers') : Yii::t('bot', 'Holders')),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-asset', [
                                'id' => $chat->id,
                            ]),
                            'text' => Yii::t('bot', 'Asset'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-threshold', [
                                'id' => $chat->id,
                            ]),
                            'text' => Yii::t('bot', 'Threshold for holders'),
                            'visible' => !$isModeSigners,
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-invite-link', [
                                'id' => $chat->id,
                            ]),
                            'text' => Yii::t('bot', 'Invite link'),
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
                ],
                [
                        'disablePreview' => true,
                ]
            )
            ->build();
    }

    /**
     * @param int|null $id Chat->id
     * @return array
     */
    public function actionSetStatus($id = null)
    {
        $chat = Chat::findOne($id);

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        switch ($chat->stellar_status) {
            case ChatSetting::STATUS_ON:
                $chat->stellar_status = ChatSetting::STATUS_OFF;

                break;
            case ChatSetting::STATUS_OFF:
                $chatMember = $chat->getChatMemberByUserId();

                if (!$chatMember->trySetChatSetting('stellar_status', ChatSetting::STATUS_ON)) {
                    return $this->getResponseBuilder()
                        ->answerCallbackQuery(
                            $this->render('alert-status-on', [
                                'requiredRating' => $chatMember->getRequiredRatingForChatSetting('stellar_status', ChatSetting::STATUS_ON),
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
     * @param int|null $id Chat->id
     * @return array
     */
    public function actionSetMode($id = null)
    {
        $chat = Chat::findOne($id);

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        switch ($chat->stellar_mode) {
            case ChatSetting::STELLAR_MODE_SIGNERS:
                $chat->stellar_mode = ChatSetting::STELLAR_MODE_HOLDERS;

                break;
            case ChatSetting::STELLAR_MODE_HOLDERS:
                $chat->stellar_mode = ChatSetting::STELLAR_MODE_SIGNERS;

                break;
        }

        return $this->actionIndex($chat->id);
    }

    /**
     * @param int|null $id Chat->id
     * @return array
     */
    public function actionSetAsset($id = null)
    {
        $chat = Chat::findOne($id);

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setInputRoute(self::createRoute('set-asset', [
                'id' => $chat->id,
            ]));

        if ($this->getUpdate()->getMessage()) {
            if ($text = $this->getUpdate()->getMessage()->getText()) {
                $text = str_replace(['-', ' '], PHP_EOL, $text);
                $rows = explode(PHP_EOL, $text);

                if (count($rows) == 2) {
                    $stellarAssetValidator = new StellarAssetValidator();
                    $stellarPublicKeyValidator = new StellarPublicKeyValidator();

                    if (($stellarAssetValidator->validate($rows[0])) && ($stellarPublicKeyValidator->validate($rows[1]))) {
                        $chat->stellar_asset = $rows[0];
                        $chat->stellar_issuer = $rows[1];

                        $this->getState()->clearInputRoute();

                        return $this->runAction('index', [
                            'id' => $chat->id,
                        ]);
                    }
                }
            }
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set-asset'),
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
     * @param int|null $id Chat->id
     * @return array
     */
    public function actionSetThreshold($id = null)
    {
        $chat = Chat::findOne($id);

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setInputRoute(self::createRoute('set-threshold', [
                'id' => $chat->id,
            ]));

        if ($this->getUpdate()->getMessage()) {
            if ($text = $this->getUpdate()->getMessage()->getText()) {
                if ($chat->validateSettingValue('stellar_threshold', $text)) {
                    $chat->stellar_threshold = $text;

                    $this->getState()->clearInputRoute();

                    return $this->runAction('index', [
                        'id' => $chat->id,
                    ]);
                }
            }
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set-threshold'),
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
     * @param int|null $id Chat->id
     * @return array
     */
    public function actionSetInviteLink($id = null)
    {
        $chat = Chat::findOne($id);

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setInputRoute(self::createRoute('set-invite-link', [
                'id' => $chat->id,
            ]));

        if ($this->getUpdate()->getMessage()) {
            if ($text = $this->getUpdate()->getMessage()->getText()) {
                $urlValidator = new UrlValidator();
                if ($urlValidator->validate($text)) {
                    $chat->stellar_invite_link = $text;

                    $this->getState()->clearInputRoute();

                    return $this->runAction('index', [
                        'id' => $chat->id,
                    ]);
                }
            }
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set-invite-link'),
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
}
