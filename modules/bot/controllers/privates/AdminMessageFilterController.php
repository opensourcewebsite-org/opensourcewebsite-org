<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\Phrase;
use yii\data\Pagination;

/**
* Class AdminMessageFilterController
*
* @package app\controllers\bot
*/
class AdminMessageFilterController extends Controller
{
    /**
    * @return array
    */
    public function actionIndex($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        $statusSetting = $chat->getSetting(ChatSetting::FILTER_STATUS);

        if (!isset($statusSetting)) {
            $statusSetting = new ChatSetting();

            $statusSetting->setAttributes([
                'chat_id' => $chatId,
                'setting' => ChatSetting::FILTER_STATUS,
                'value' => ChatSetting::FILTER_STATUS_OFF,
            ]);

            $statusSetting->save();
        }

        $modeSetting = $chat->getSetting(ChatSetting::FILTER_MODE);

        if (!isset($modeSetting)) {
            $modeSetting = new ChatSetting();

            $modeSetting->setAttributes([
                'chat_id' => $chatId,
                'setting' => ChatSetting::FILTER_MODE,
                'value' => ChatSetting::FILTER_MODE_BLACKLIST,
            ]);

            $modeSetting->save();
        }

        $chatTitle = $chat->title;
        $isFilterOn = ($statusSetting->value == ChatSetting::FILTER_STATUS_ON);
        $isFilterModeBlack = ($modeSetting->value == ChatSetting::FILTER_MODE_BLACKLIST);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', compact('chatTitle', 'isFilterOn', 'isFilterModeBlack')),
                [
                    [
                        [
                            'callback_data' => self::createRoute('status', [
                                'chatId' => $chatId,
                            ]),
                            'text' => Yii::t('bot', 'Status') . ': ' . ($isFilterOn ? 'ON' : 'OFF'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('update', [
                                'chatId' => $chatId,
                            ]),
                            'text' => Yii::t('bot', 'Mode') . ': ' . ($isFilterModeBlack ? Yii::t('bot', 'Blacklist') : Yii::t('bot', 'Whitelist')),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('whitelist', [
                                'chatId' => $chatId,
                            ]),
                            'text' => Yii::t('bot', 'Whitelist'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('blacklist', [
                                'chatId' => $chatId,
                            ]),
                            'text' => Yii::t('bot', 'Blacklist'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => AdminChatController::createRoute('index', [
                                'chatId' => $chatId,
                            ]),
                            'text' => '🔙',
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

        $modeSetting = $chat->getSetting(ChatSetting::FILTER_MODE);

        if ($modeSetting->value == ChatSetting::FILTER_MODE_BLACKLIST) {
            $modeSetting->value = ChatSetting::FILTER_MODE_WHITELIST;
        } else {
            $modeSetting->value = ChatSetting::FILTER_MODE_BLACKLIST;
        }

        $modeSetting->save();

        return $this->actionIndex($chatId);
    }

    public function actionStatus($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        $statusSetting = $chat->getSetting(ChatSetting::FILTER_STATUS);

        if ($statusSetting->value == ChatSetting::FILTER_STATUS_ON) {
            $statusSetting->value = ChatSetting::FILTER_STATUS_OFF;
        } else {
            $statusSetting->value = ChatSetting::FILTER_STATUS_ON;
        }

        $statusSetting->save();

        return $this->actionIndex($chatId);
    }

    /**
    * @return array
    */
    public function actionBlacklist($chatId = null, $page = 1)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        $this->getState()->setName(null);

        $phraseQuery = $chat->getBlacklistPhrases();

        $pagination = new Pagination([
                'totalCount' => $phraseQuery->count(),
                'pageSize' => 9,
                'params' => [
                    'page' => $page,
                ],
            ]);

        $pagination->pageSizeParam = false;
        $pagination->validatePage = true;

        $chatTitle = $chat->title;
        $phrases = $phraseQuery->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $paginationButtons = PaginationButtons::build($pagination, function ($page) use ($chatId) {
            return self::createRoute('index', [
                    'chatId' => $chatId,
                    'page' => $page,
                ]);
        });
        $buttons = [];

        if ($phrases) {
            foreach ($phrases as $phrase) {
                $buttons[][] = [
                        'callback_data' => self::createRoute('phrase', [
                            'phraseId' => $phrase->id,
                        ]),
                        'text' => $phrase->text
                    ];
            }

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $buttons[] = [
                [
                    'callback_data' => self::createRoute('index', [
                        'chatId' => $chatId,
                    ]),
                    'text' => '🔙',
                ],
                [
                    'callback_data' => self::createRoute('newphrase', [
                        'type' => Phrase::TYPE_BLACKLIST,
                        'chatId' => $chatId,
                    ]),
                    'text' => '➕',
                ],
            ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('blacklist', compact('chatTitle')),
                $buttons
            )
            ->build();
    }

    /**
    * @return array
    */
    public function actionWhitelist($chatId = null, $page = 1)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        $this->getState()->setName(null);

        $phraseQuery = $chat->getWhitelistPhrases();

        $pagination = new Pagination([
                    'totalCount' => $phraseQuery->count(),
                    'pageSize' => 9,
                    'params' => [
                        'page' => $page,
                    ],
                ]);

        $pagination->pageSizeParam = false;
        $pagination->validatePage = true;

        $chatTitle = $chat->title;
        $phrases = $phraseQuery->offset($pagination->offset)
                ->limit($pagination->limit)
                ->all();

        $paginationButtons = PaginationButtons::build($pagination, function ($page) use ($chatId) {
            return self::createRoute('index', [
                'chatId' => $chatId,
                'page' => $page,
            ]);
        });
        $buttons = [];

        if ($phrases) {
            foreach ($phrases as $phrase) {
                $buttons[][] = [
                            'callback_data' => self::createRoute('phrase', [
                                'phraseId' => $phrase->id,
                            ]),
                            'text' => $phrase->text
                        ];
            }

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $buttons[] = [
                    [
                        'callback_data' => self::createRoute('index', [
                            'chatId' => $chatId,
                        ]),
                        'text' => '🔙',
                    ],
                    [
                        'callback_data' => self::createRoute('newphrase', [
                            'type' => Phrase::TYPE_WHITELIST,
                            'chatId' => $chatId,
                        ]),
                        'text' => '➕',
                    ],
                ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('whitelist', compact('chatTitle')),
                $buttons
            )
            ->build();
    }

    /**
    * @return array
    */
    public function actionNewphrase($type = null, $chatId = null)
    {
        $this->getState()->setName(self::createRoute('newphrase-update', [
                        'type' => $type,
                        'chatId' => $chatId,
                    ]));

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('newphrase'),
                [
                    [
                        [
                            'callback_data' => $type == Phrase::TYPE_BLACKLIST
                            ? self::createRoute('blacklist', [
                                'chatId' => $chatId,
                            ])
                            : self::createRoute('whitelist', [
                                'chatId' => $chatId,
                            ]),
                            'text' => '🔙',
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionNewphraseUpdate($type = null, $chatId = null)
    {
        $update = $this->getUpdate();
        $text = $update->getMessage()->getText();

        if (!Phrase::find()->where(['type' => $type, 'chat_id' => $chatId, 'text' => $text])->exists()) {
            $phrase = new Phrase();

            $phrase->setAttributes([
                                'chat_id' => $chatId,
                                'type' => $type,
                                'text' => $text,
                                'created_by' => $this->getTelegramUser()->id,
                            ]);

            $phrase->save();
        }

        $this->getState()->setName($type == Phrase::TYPE_BLACKLIST
                        ? self::createRoute('blacklist', [
                            'chatId' => $chatId,
                        ])
                        : self::createRoute('whitelist', [
                            'chatId' => $chatId,
                        ]));

        $this->module->dispatchRoute($update);
    }

    /**
    * @return array
    */
    public function actionPhrase($phraseId = null)
    {
        $this->getState()->setName(null);

        $phrase = Phrase::findOne($phraseId);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('phrase', compact('phrase')),
                [
                    [
                        [
                            'callback_data' => $phrase->isTypeBlack()
                            ? self::createRoute('blacklist', [
                                'chatId' => $phrase->chat_id,
                            ])
                            : self::createRoute('whitelist', [
                                'chatId' => $phrase->chat_id,
                            ]),
                            'text' => '🔙',
                        ],
                        [
                            'callback_data' => self::createRoute('phrase-create', [
                                'phraseId' => $phraseId,
                            ]),
                            'text' => '✏️',
                        ],
                        [
                            'callback_data' => self::createRoute('phrase-delete', [
                                'phraseId' => $phraseId,
                            ]),
                            'text' => '🗑',
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionPhraseDelete($phraseId = null)
    {
        $phrase = Phrase::findOne($phraseId);

        $chatId = $phrase->chat_id;

        $isTypeBlack = $phrase->isTypeBlack();
        $phrase->delete();

        $update = $this->getUpdate();
        $update->getCallbackQuery()->setData($isTypeBlack
                            ? self::createRoute('blacklist', [
                                'chatId' => $chatId,
                            ])
                            : self::createRoute('whitelist', [
                                'chatId' => $chatId,
                            ]));

        $this->module->dispatchRoute($update);
    }

    public function actionPhraseCreate($phraseId = null)
    {
        $this->getState()->setName(self::createRoute('phrase-update', [
                                'phraseId' => $phraseId,
                            ]));

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('phrase-create'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('phrase', [
                                'phraseId' => $phraseId,
                            ]),
                            'text' => '🔙',
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionPhraseUpdate($phraseId = null)
    {
        $update = $this->getUpdate();

        $phrase = Phrase::findOne($phraseId);

        $text = $update->getMessage()->getText();

        if (!Phrase::find()->where([
                                    'chat_id' => $phrase->chat_id,
                                    'text' => $text,
                                    'type' => $phrase->type
                                    ])->exists()) {
            $phrase->text = $text;
            $phrase->save();

            return $this->actionPhrase($phraseId);
        }
    }
}
