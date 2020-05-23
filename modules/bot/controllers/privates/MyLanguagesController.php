<?php

namespace app\modules\bot\controllers\privates;

use app\models\Language;
use app\models\LanguageLevel;
use app\models\UserLanguage;
use app\models\Vacancy;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\components\response\commands\DeleteMessageCommand;
use yii\data\Pagination;
use yii\db\StaleObjectException;
use TelegramBot\Api\BotApi;

class MyLanguagesController extends Controller
{
    public function actionIndex($page = 1)
    {
        $languagesQuery = $this->getUser()->getLanguages();
        $pagination = new Pagination([
            'totalCount' => $languagesQuery->count(),
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);
        $paginationButtons = PaginationButtons::build($pagination, function ($page) {
            return self::createRoute('index', [
                'page' => $page,
            ]);
        });
        $languages = $languagesQuery
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $rows = array_map(function ($language) {
            return [
                [
                    'text' => $language->getDisplayName(),
                    'callback_data' => self::createRoute('create-level', [
                        'languageId' => $language->language->id,
                    ]),
                ],
            ];
        }, $languages);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index'),
                array_merge($rows, [$paginationButtons], [
                    [
                        [
                            'text' => Emoji::BACK,
                            'callback_data' => MyProfileController::createRoute(),
                        ],
                        [
                            'text' => Emoji::MENU,
                            'callback_data' => MenuController::createRoute(),
                        ],
                        [
                            'text' => Emoji::ADD,
                            'callback_data' => self::createRoute('create-language'),
                        ],
                    ],
                ])
            )
            ->build();
    }

    public function actionCreateLanguage($page = 1)
    {
        $this->getState()->setName(self::createRoute('search'));
        $languageQuery = Language::find()->orderBy('code ASC');
        $pagination = new Pagination([
            'totalCount' => $languageQuery->count(),
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $languages = $languageQuery->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $paginationButtons = PaginationButtons::build($pagination, function ($page) {
            return self::createRoute('create-language', [
                'page' => $page,
            ]);
        });

        $languageRows = array_map(function ($language) {
            return [
                [
                    'callback_data' => self::createRoute('create-level', [
                        'languageId' => $language->id,
                    ]),
                    'text' => strtoupper($language->code) . ' - ' . $language->name,
                ]
            ];
        }, $languages);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('create-language'),
                array_merge($languageRows, [$paginationButtons], [
                    [
                        [
                            'callback_data' => self::createRoute(),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ])
            )
            ->build();
    }

    public function actionCreateLevel($languageId, $page = 1)
    {
        $language = Language::findOne($languageId);
        if (!isset($language)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery();
        }

        $levelQuery = LanguageLevel::find()->orderBy('value ASC');
        $pagination = new Pagination([
            'totalCount' => $levelQuery->count(),
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $levels = $levelQuery->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $paginationButtons = PaginationButtons::build($pagination, function ($page) {
            return self::createRoute('create-level', [
                'page' => $page,
            ]);
        });

        $levelRows = array_map(function ($level) use ($languageId) {
            return [
                [
                    'text' => $level->getDisplayName(),
                    'callback_data' => self::createRoute('create', [
                        'languageId' => $languageId,
                        'levelId' => $level->id,
                    ]),
                ]
            ];
        }, $levels);

        $isEdit = $this->getUser()->getLanguages()->where(['language_id' => $languageId])->exists();

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('create-level', [
                    'languageName' => $language->name,
                ]),
                array_merge($levelRows, [$paginationButtons], [
                    array_merge([
                        [
                            'text' => Emoji::BACK,
                            'callback_data' => $isEdit
                                ? self::createRoute()
                                : self::createRoute('create-language'),
                        ],
                    ],
                    ($isEdit)
                        ? [
                            [
                                'text' => Emoji::DELETE,
                                'callback_data' => self::createRoute('delete', [
                                    'languageId' => $languageId,
                                ]),
                            ],
                        ]
                        : []
                    ),
                ])
            )
            ->build();
    }

    public function actionCreate($languageId, $levelId)
    {
        $language = Language::findOne($languageId);
        $level = Language::findOne($levelId);
        if (!isset($language) || !isset($level)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery();
        }

        $userLanguage = $this->getUser()->getLanguages()->where(['language_id' => $languageId])->one()
            ?? new UserLanguage();
        $userLanguage->setAttributes([
            'user_id' => $this->getUser()->id,
            'language_id' => $languageId,
            'language_level_id' => $levelId,
        ]);
        $userLanguage->save();

        return $this->actionIndex();
    }

    public function actionDelete($languageId)
    {
        $userLanguage = $this->getUser()->getLanguages()->where(['language_id' => $languageId])->one();
        if (!isset($userLanguage)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        try {
            $userLanguage->delete();
        } catch (StaleObjectException $e) {
        } catch (\Throwable $e) {
        }

        return $this->actionIndex();
    }

    public function actionSearch()
    {
        $update = $this->getUpdate();
        $text = $update->getMessage()->getText();

        if (strlen($text) <= 3) {
            $language = Language::find()
                ->orFilterWhere(['like', 'code', $text, false])
                ->one();
        } else {
            $language = Language::find()
                ->orFilterWhere(['like', 'code', $text, false])
                ->orFilterWhere(['like', 'name', $text . '%', false])
                ->orFilterWhere(['like', 'name_ascii', $text . '%', false])
                ->one();
        }

        if (isset($language)) {
            $chatId = $this->getUpdate()->getMessage()->getChat()->getId();
            $messageId = $this->getUpdate()->getMessage()->getMessageId();

            $deleteBotMessage = new DeleteMessageCommand($chatId, $messageId - 1);
            $deleteBotMessage->send($this->getBotApi());

            $deleteUserMessage = new DeleteMessageCommand($chatId, $messageId);
            $deleteUserMessage->send($this->getBotApi());

            return $this->actionCreateLevel($language->id);
        } else {
            $chatId = $this->getUpdate()->getMessage()->getChat()->getId();
            $messageId = $this->getUpdate()->getMessage()->getMessageId();
            $deleteBotMessage = new DeleteMessageCommand($chatId, $messageId - 1);
            $deleteBotMessage->send($this->getBotApi());
            $deleteUserMessage = new DeleteMessageCommand($chatId, $messageId);
            $deleteUserMessage->send($this->getBotApi());

            return $this->actionCreateLanguage();
        }
    }
}
