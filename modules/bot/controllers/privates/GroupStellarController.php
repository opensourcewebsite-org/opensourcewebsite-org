<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\validators\StellarAssetValidator;
use app\modules\bot\validators\StellarPublicKeyValidator;
use yii\validators\UrlValidator;

/**
 * Class GroupStellarController
 *
 * @package app\modules\bot\controllers\privates
 */
class GroupStellarController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(null);

        $isModeSigners = ($chat->stellar_mode == ChatSetting::STELLAR_MODE_SIGNERS);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', compact('chat', 'isModeSigners')),
                [
                    [
                        [
                            'callback_data' => self::createRoute('set-status', [
                                'chatId' => $chatId,
                            ]),
                            'text' => $chat->stellar_status == ChatSetting::STATUS_ON ? Emoji::STATUS_ON . ' ON' : Emoji::STATUS_OFF . ' OFF',
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-mode', [
                                'chatId' => $chatId,
                            ]),
                            'text' => Yii::t('bot', 'Mode') . ': ' . ($isModeSigners ? Yii::t('bot', 'Signers') : Yii::t('bot', 'Holders')),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-asset', [
                                'chatId' => $chatId,
                            ]),
                            'text' => Yii::t('bot', 'Asset'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-threshold', [
                                'chatId' => $chatId,
                            ]),
                            'text' => Yii::t('bot', 'Threshold for holders'),
                            'visible' => !$isModeSigners,
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('set-invite-link', [
                                'chatId' => $chatId,
                            ]),
                            'text' => Yii::t('bot', 'Invite link'),
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
                ],
                [
                        'disablePreview' => true,
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

        $this->getState()->setName(null);

        if ($chat->stellar_status == ChatSetting::STATUS_ON) {
            $chat->stellar_status = ChatSetting::STATUS_OFF;
        } else {
            $chat->stellar_status = ChatSetting::STATUS_ON;
        }

        return $this->actionIndex($chatId);
    }

    public function actionSetMode($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        if ($chat->stellar_mode == ChatSetting::STELLAR_MODE_SIGNERS) {
            $chat->stellar_mode = ChatSetting::STELLAR_MODE_HOLDERS;
        } else {
            $chat->stellar_mode = ChatSetting::STELLAR_MODE_SIGNERS;
        }

        return $this->actionIndex($chatId);
    }

    public function actionSetAsset($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        $this->getState()->setName(self::createRoute('set-asset', [
                'chatId' => $chatId,
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

                        $this->getState()->setName(null);

                        return $this->runAction('index', [
                            'chatId' => $chatId,
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
                                'chatId' => $chatId,
                            ]),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionSetThreshold($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        $this->getState()->setName(self::createRoute('set-threshold', [
                'chatId' => $chatId,
            ]));

        if ($this->getUpdate()->getMessage()) {
            if ($text = $this->getUpdate()->getMessage()->getText()) {
                if ($chat->validateSettingValue('stellar_threshold', $text)) {
                    $chat->stellar_threshold = $text;

                    $this->getState()->setName(null);

                    return $this->runAction('index', [
                        'chatId' => $chatId,
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
                                'chatId' => $chatId,
                            ]),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionSetInviteLink($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        $this->getState()->setName(self::createRoute('set-invite-link', [
                'chatId' => $chatId,
            ]));

        if ($this->getUpdate()->getMessage()) {
            if ($text = $this->getUpdate()->getMessage()->getText()) {
                $urlValidator = new UrlValidator();
                if ($urlValidator->validate($text)) {
                    $chat->stellar_invite_link = $text;

                    $this->getState()->setName(null);

                    return $this->runAction('index', [
                        'chatId' => $chatId,
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
                                'chatId' => $chatId,
                            ]),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }
}
