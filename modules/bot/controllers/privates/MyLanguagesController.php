<?php

namespace app\modules\bot\controllers\privates;

use app\models\Language;
use app\models\LanguageLevel;
use app\models\UserLanguage;
use app\models\Vacancy;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use yii\data\Pagination;
use yii\db\StaleObjectException;

/**
 * Class MyLanguagesController
 *
 * @package app\modules\bot\controllers\privates
 */
class MyLanguagesController extends Controller
{
    public function actionIndex($page = 1)
    {
        $this->getState()->setName(null);

        $query = $this->getUser()->getLanguages();

        $pagination = new Pagination([
            'totalCount' => $query->count(),
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

        $buttons = [];

        $languages = $query
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        if ($languages) {
            foreach ($languages as $language) {
                $buttons[][] = [
                    'callback_data' => self::createRoute('list-level', [
                        'languageId' => $language->language->id,
                    ]),
                    'text' => $language->getLabel(),
                ];
            }

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $buttons[] = [
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
                'callback_data' => self::createRoute('list'),
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index'),
                $buttons
            )
            ->build();
    }

    public function actionList($page = 1)
    {
        $this->getState()->setName(self::createRoute('input'));

        $query = Language::find()
            ->orderBy(['code' => SORT_ASC]);

        $pagination = new Pagination([
            'totalCount' => $query->count(),
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $paginationButtons = PaginationButtons::build($pagination, function ($page) {
            return self::createRoute('list', [
                'page' => $page,
            ]);
        });

        $buttons = [];

        $languages = $query
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        if ($languages) {
            foreach ($languages as $language) {
                $buttons[][] = [
                    'callback_data' => self::createRoute('list-level', [
                        'languageId' => $language->id,
                    ]),
                    'text' => strtoupper($language->code) . ' - ' . $language->name,
                ];
            }

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $buttons[] = [
            [
                'callback_data' => self::createRoute(),
                'text' => Emoji::BACK,
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('list'),
                $buttons
            )
            ->build();
    }

    public function actionListLevel($languageId = null, $page = 1)
    {
        $language = Language::findOne($languageId);

        if (!isset($language)) {
            return $this->getResponseBuilder()
                 ->answerCallbackQuery()
                 ->build();
        }

        $this->getState()->setName(null);

        $query = LanguageLevel::find()
            ->orderBy(['value' => SORT_ASC]);

        $pagination = new Pagination([
            'totalCount' => $query->count(),
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $paginationButtons = PaginationButtons::build($pagination, function ($page) {
            return self::createRoute('list-level', [
                'page' => $page,
            ]);
        });

        $levels = $query
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $languagesCount = $this->getUser()->getLanguages()->count();

        if ($levels) {
            foreach ($levels as $level) {
                $buttons[][] = [
                    'callback_data' => self::createRoute('create', [
                        'languageId' => $languageId,
                        'levelId' => $level->id,
                    ]),
                    'text' => $level->getLabel(),
                ];
            }

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $buttons[] = [
            [
                'callback_data' => $languagesCount ? self::createRoute() : self::createRoute('list'),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => self::createRoute('delete', [
                    'languageId' => $languageId,
                ]),
                'text' => Emoji::DELETE,
                'visible' => $languagesCount > 1,
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('list-level', [
                    'languageName' => $language->name,
                ]),
                $buttons
            )
            ->build();
    }

    public function actionCreate($languageId = null, $levelId = null)
    {
        $language = Language::findOne($languageId);
        $level = Language::findOne($levelId);

        if (!isset($language) || !isset($level)) {
            return $this->getResponseBuilder()
                 ->answerCallbackQuery()
                 ->build();
        }

        $userLanguage = $this
            ->getUser()
            ->getLanguages()
            ->where([
                'language_id' => $languageId,
            ])
            ->one() ?? new UserLanguage();

        $userLanguage->setAttributes([
            'user_id' => $this->getUser()->id,
            'language_id' => $languageId,
            'language_level_id' => $levelId,
        ]);
        $userLanguage->save();

        return $this->actionIndex();
    }

    public function actionDelete($languageId = null)
    {
        $userLanguage = $this
            ->getUser()
            ->getLanguages()
            ->where([
                'language_id' => $languageId,
            ])
            ->one();

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

    public function actionInput()
    {
        if ($text = $this->getUpdate()->getMessage()->getText()) {
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
                return $this->actionListLevel($language->id);
            }
        }

        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }
}
