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
    /**
     * @param int $page
     * @return array
     */
    public function actionIndex($page = 1)
    {
        $this->getState()->setName(null);

        $query = $this->globalUser->getLanguages();

        $pagination = new Pagination([
            'totalCount' => $query->count(),
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $buttons = [];

        $languages = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        if ($languages) {
            foreach ($languages as $language) {
                $buttons[][] = [
                    'callback_data' => self::createRoute('set-level', [
                        'id' => $language->language->id,
                    ]),
                    'text' => $language->getLabel(),
                ];
            }

            $paginationButtons = PaginationButtons::build($pagination, function ($page) {
                return self::createRoute('index', [
                    'page' => $page,
                ]);
            });

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $buttons[] = [
            [
                'callback_data' => MyProfileController::createRoute(),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
            [
                'callback_data' => self::createRoute('add'),
                'text' => Emoji::ADD,
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index'),
                $buttons
            )
            ->build();
    }

    /**
     * @param int $page
     * @return array
     */
    public function actionAdd($page = 1)
    {
        if ($this->getUpdate()->getMessage()) {
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

                if ($language) {
                    return $this->actionSetLevel($language->id);
                }
            }
        }

        $this->getState()->setName(self::createRoute('add'));

        $query = Language::find()
            ->orderBy([
                'code' => SORT_ASC,
            ]);

        $pagination = new Pagination([
            'totalCount' => $query->count(),
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $buttons = [];

        $languages = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        if ($languages) {
            foreach ($languages as $language) {
                $buttons[][] = [
                    'callback_data' => self::createRoute('set-level', [
                        'id' => $language->id,
                    ]),
                    'text' => strtoupper($language->code) . ' - ' . $language->name,
                ];
            }

            $paginationButtons = PaginationButtons::build($pagination, function ($page) {
                return self::createRoute('add', [
                    'page' => $page,
                ]);
            });

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
                $this->render('add'),
                $buttons
            )
            ->build();
    }

    /**
    * @param int|null $id Language->id
     * @param int $page
     * @return array
     */
    public function actionSetLevel($id = null, $page = 1)
    {
        $language = Language::findOne($id);

        if (!isset($language)) {
            return $this->getResponseBuilder()
                 ->answerCallbackQuery()
                 ->build();
        }

        $this->getState()->setName(null);

        $query = LanguageLevel::find()
            ->orderBy([
                'value' => SORT_ASC,
            ]);

        $pagination = new Pagination([
            'totalCount' => $query->count(),
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $levels = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        if ($levels) {
            foreach ($levels as $level) {
                $buttons[][] = [
                    'callback_data' => self::createRoute('create', [
                        'languageId' => $language->id,
                        'levelId' => $level->id,
                    ]),
                    'text' => $level->getLabel(),
                ];
            }

            $paginationButtons = PaginationButtons::build($pagination, function ($page) {
                return self::createRoute('set-level', [
                    'page' => $page,
                ]);
            });

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $languagesCount = $this->globalUser->getLanguages()->count();

        $buttons[] = [
            [
                'callback_data' => $languagesCount ? self::createRoute() : self::createRoute('add'),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => self::createRoute('delete', [
                    'id' => $language->id,
                ]),
                'text' => Emoji::DELETE,
                'visible' => $languagesCount > 1,
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set-level', [
                    'languageName' => $language->name,
                ]),
                $buttons
            )
            ->build();
    }

    /**
     * @param int|null $languageId Language->id
     * @param int|null $levelId LanguageLevel->id
     * @param int $page
     * @return array
     */
    public function actionCreate($languageId = null, $levelId = null)
    {
        $language = Language::findOne($languageId);
        $level = LanguageLevel::findOne($levelId);

        if (!isset($language) || !isset($level)) {
            return $this->getResponseBuilder()
                 ->answerCallbackQuery()
                 ->build();
        }

        $userLanguage = $this->globalUser
            ->getLanguages()
            ->where([
                'language_id' => $language->id,
            ])
            ->one() ?? new UserLanguage();

        $userLanguage->setAttributes([
            'user_id' => $this->globalUser->id,
            'language_id' => $language->id,
            'language_level_id' => $level->id,
        ]);
        $userLanguage->save();

        return $this->actionIndex();
    }

    /**
     * @param int|null $id Language->id
     * @return array
     */
    public function actionDelete($id = null)
    {
        $userLanguage = $this->globalUser
            ->getLanguages()
            ->where([
                'language_id' => $id,
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
}
