<?php

namespace app\modules\bot\controllers\privates;

use Yii;

use app\modules\bot\components\Controller;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\BotCommandAlias;
use app\modules\bot\controllers\publics\TopController;
use app\modules\bot\components\helpers\PaginationButtons;
use yii\data\Pagination;

/**
 * Class AdminStarTopController
 *
 * @package app\controllers\bot
 */
class AdminStarTopController extends Controller
{
    const PAGE_WORDS_COUNT = 9;
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
                                'callback_data' => self::createRoute('like-wordlist', [
                                    'chatId' => $chatId,
                                ]),
                                'text' => Yii::t('bot', 'Like wordlist'),
                            ],
                        ],
                        [
                            [
                                'callback_data' => self::createRoute('dislike-wordlist', [
                                    'chatId' => $chatId,
                                ]),
                                'text' => Yii::t('bot', 'Dislike wordlist'),
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

        $statusSetting = $chat->getSetting(ChatSetting::STAR_TOP_STATUS);

        if ($statusSetting->value == ChatSetting::STAR_TOP_STATUS_ON) {
            $statusSetting->value = ChatSetting::STAR_TOP_STATUS_OFF;
        } else {
            $statusSetting->value = ChatSetting::STAR_TOP_STATUS_ON;
        }

        $statusSetting->save();

        return $this->actionIndex($chatId);
    }

    /**
    * @return array
    */
    public function actionLikeWordlist($chatId = null, $page = 1)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        $this->getState()->setName(null);

        $command = $this->getLikeCommand();
        $phraseQuery = BotCommandAlias::find()->where(['command' => $command]);

        $pagination = new Pagination([
                'totalCount' => $phraseQuery->count(),
                'pageSize' => self::PAGE_WORDS_COUNT,
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
            return self::createRoute($this->action->id, [
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
                        'command' => $command,
                        'chatId' => $chatId,
                    ]),
                    'text' => '➕',
                ],
            ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('like-wordlist', compact('chatTitle')),
                $buttons
            )
            ->build();
    }

    /**
    * @return array
    */
    public function actionDislikeWordlist($chatId = null, $page = 1)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        $this->getState()->setName(null);

        $command = $this->getDislikeCommand();
        $phraseQuery = BotCommandAlias::find()->where(['command' => $command]);

        $pagination = new Pagination([
                'totalCount' => $phraseQuery->count(),
                'pageSize' => self::PAGE_WORDS_COUNT,
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
            return self::createRoute('$this->action->id', [
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
                        'command' => $command,
                        'chatId' => $chatId,
                    ]),
                    'text' => '➕',
                ],
            ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('dislike-wordlist', compact('chatTitle')),
                $buttons
            )
            ->build();
    }

    /**
    * @return array
    */
    public function actionNewphrase($command, $chatId)
    {
        $this->getState()->setName(self::createRoute('newphrase-update', [
                        'command' => $command,
                        'chatId' => $chatId,
                    ]));

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('newphrase'),
                [
                    [
                        [
                            'callback_data' => $command == $this->getLikeCommand()
                            ? self::createRoute('like-wordlist', [
                                'chatId' => $chatId,
                            ])
                            : self::createRoute('dislike-wordlist', [
                                'chatId' => $chatId,
                            ]),
                            'text' => '🔙',
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionNewphraseUpdate($command, $chatId)
    {
        $update = $this->getUpdate();
        $text = $update->getMessage()->getText();

        if (!BotCommandAlias::find()->where(['command' => $command, 'chat_id' => $chatId, 'text' => $text])->exists()) {
            $phrase = new BotCommandAlias();

            $phrase->setAttributes([
                                'chat_id' => $chatId,
                                'command' => $command,
                                'text' => $text,
                                'created_by' => $this->getTelegramUser()->id,
                            ]);

            $phrase->save();
        }

        $this->getState()->setName($command == $this->getLikeCommand()
                        ? self::createRoute('like-wordlist', [
                            'chatId' => $chatId,
                        ])
                        : self::createRoute('dislike-wordlist', [
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

        $phrase = BotCommandAlias::findOne($phraseId);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('phrase', compact('phrase')),
                [
                    [
                        [
                            'callback_data' => $phrase->command == $this->getLikeCommand()
                            ? self::createRoute('like-wordlist', [
                                'chatId' => $phrase->chat_id,
                            ])
                            : self::createRoute('dislike-wordlist', [
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
        $phrase = BotCommandAlias::findOne($phraseId);

        $chatId = $phrase->chat_id;

        $isLikeAlias = $phrase->command == $this->getLikeCommand();
        $phrase->delete();

        $update = $this->getUpdate();
        $update->getCallbackQuery()->setData($isLikeAlias
                            ? self::createRoute('like-wordlist', [
                                'chatId' => $chatId,
                            ])
                            : self::createRoute('dislike-wordlist', [
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

        $phrase = BotCommandAlias::findOne($phraseId);

        $text = $update->getMessage()->getText();

        if (!BotCommandAlias::find()->where([
                                    'chat_id' => $phrase->chat_id,
                                    'text' => $text,
                                    'command' => $phrase->command
                                    ])->exists()) {
            $phrase->text = $text;
            $phrase->save();

            return $this->actionPhrase($phraseId);
        }
    }

    private function getLikeCommand()
    {
        return TopController::createRoute('start-like');
    }

    private function getDislikeCommand()
    {
        return TopController::createRoute('start-hate');
    }
}
