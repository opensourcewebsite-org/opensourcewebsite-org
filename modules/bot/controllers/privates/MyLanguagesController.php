<?php

namespace app\modules\bot\controllers\privates;

use app\models\Language;
use app\models\LanguageLevel;
use app\models\UserLanguage;
use app\models\Vacancy;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\components\response\ResponseBuilder;
use yii\data\Pagination;
use yii\db\StaleObjectException;

class MyLanguagesController extends Controller
{
    public function actionIndex($page = 1)
    {
        $userLanguageButton = PaginationButtons::buildFromQuery(
            $this->getUser()->getLanguages(),
            function ($page) {
                return self::createRoute('index', [
                    'page' => $page,
                ]);
            },
            function (UserLanguage $userLanguage) {
                return [
                    'text' => $userLanguage->getDisplayName(),
                    'callback_data' => self::createRoute('create-level', [
                        'languageId' => $userLanguage->language->id,
                    ]),
                ];
            },
            $page
        );

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('index'),
                array_merge($userLanguageButton, [
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
        $languageButtons = PaginationButtons::buildFromQuery(
            Language::find()->orderBy('code ASC'),
            function ($page) {
                return self::createRoute('create-language', [
                    'page' => $page,
                ]);
            },
            function (Language $language) {
                return [
                    'callback_data' => self::createRoute('create-level', [
                        'languageId' => $language->id,
                    ]),
                    'text' => strtoupper($language->code) . ' - ' . $language->name,
                ];
            },
            $page
        );

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('create-language'),
                array_merge($languageButtons, [
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
            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->answerCallbackQuery();
        }

        $levelButtons = PaginationButtons::buildFromQuery(
            LanguageLevel::find()->orderBy('value ASC'),
            function ($page) {
                return self::createRoute('create-level', [
                    'page' => $page,
                ]);
            },
            function (LanguageLevel $languageLevel) {
                return [
                    'text' => $languageLevel->getDisplayName(),
                    'callback_data' => self::createRoute('create', [
                        'languageId' => $languageLevel->id,
                        'levelId' => $languageLevel->id,
                    ]),
                ];
            },
            $page
        );

        $isEdit = $this->getUser()->getLanguages()->where([ 'language_id' => $languageId ])->exists();

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('create-level', [
                    'languageName' => $language->name,
                ]),
                array_merge($levelButtons, [
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
            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->answerCallbackQuery();
        }

        $userLanguage = $this->getUser()->getLanguages()->where([ 'language_id' => $languageId ])->one()
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
        $userLanguage = $this->getUser()->getLanguages()->where([ 'language_id' => $languageId ])->one();
        if (!isset($userLanguage)) {
            return ResponseBuilder::fromUpdate($this->getUpdate())
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
}