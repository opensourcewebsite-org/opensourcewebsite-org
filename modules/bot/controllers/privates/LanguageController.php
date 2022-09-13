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
        return $this->actionSet();
    }

    /**
     * @param string|null $code Language->code
     * @param int $page
     * @return array
     */
    public function actionSet($code = null, $page = 1)
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
                    $user = $this->getTelegramUser();
                    $user->language_id = $language->id;

                    if ($user->save()) {
                        Yii::$app->language = $language->code;
                    }

                    return $this->run('start/index');
                }
            }
        }

        $this->getState()->setName(self::createRoute('set'));

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
                    'callback_data' => self::createRoute('set', [
                        'code' => $language->code,
                    ]),
                    'text' => strtoupper($language->code) . ' - ' . $language->name,
                ];
            }

            $paginationButtons = PaginationButtons::build($pagination, function ($page) {
                return self::createRoute('set', [
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
                $this->render('set'),
                $buttons
            )
            ->build();
    }
}
