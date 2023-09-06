<?php

namespace app\modules\bot\controllers\privates;

use app\models\Language;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use Yii;
use yii\data\Pagination;

/**
 * Class MyLanguageController
 *
 * @package app\modules\bot\controllers\privates
 */
class LanguageController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        return $this->actionList();
    }

    /**
     * @param int $page
     * @return array
     */
    public function actionList($page = 1)
    {
        $this->getState()->setInputRoute(self::createRoute('input'));

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
                    'callback_data' => self::createRoute('select', [
                        'code' => $language->code,
                    ]),
                    'text' => strtoupper($language->code) . ' - ' . $language->name,
                ];
            }

            $paginationButtons = PaginationButtons::build($pagination, function ($page) {
                return self::createRoute('list', [
                    'page' => $page,
                ]);
            });

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $buttons[] = [
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::BACK,
            ]
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('list'),
                $buttons
            )
            ->build();
    }

    /**
     * @param string|null $code Language->code
     * @return array
     */
    public function actionSelect($code = null)
    {
        if ($code) {
            $language = Language::findOne([
                'code' => $code,
            ]);

            if ($language) {
                $user = $this->getTelegramUser();
                $user->language_id = $language->id;

                if ($user->save()) {
                    Yii::$app->language = $language->code;
                }

                return $this->run('start/index');
            }
        }

        return $this->getResponseBuilder()
        ->answerCallbackQuery()
        ->build();
    }

    /**
     * @return array
     */
    public function actionInput()
    {
        if ($this->getUpdate()->getMessage()) {
            if ($text = $this->getUpdate()->getMessage()->getText()) {
                if (strlen($text) <= 3) {
                    $language = Language::find()
                        ->orFilterWhere(['like', 'code', $text, false])
                        ->one();
                } else {
                    $language = Language::find()
                        ->orFilterWhere(['like', 'name', $text . '%', false])
                        ->orFilterWhere(['like', 'name_ascii', $text . '%', false])
                        ->one();
                }

                if ($language) {
                    return $this->actionSelect($language->code);
                }
            }
        }

        return $this->getResponseBuilder()
        ->answerCallbackQuery()
        ->build();
    }
}
