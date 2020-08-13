<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\models\Language;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use yii\data\Pagination;

/**
 * Class MyLanguageController
 *
 * @package app\modules\bot\controllers\privates
 */
class LanguageController extends Controller
{
    /**
     * @param int $page
     *
     * @return array
     */
    public function actionIndex($page = 1)
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
            return self::createRoute('index', [
                'page' => $page,
            ]);
        });

        $languageRows = array_map(function ($language) {
            return [
                [
                    'callback_data' => self::createRoute('update', [
                        'languageCode' => $language->code,
                    ]),
                    'text' => strtoupper($language->code) . ' - ' . $language->name,
                ]
            ];
        }, $languages);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('update'),
                array_merge($languageRows, [$paginationButtons], [
                    [
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ])
            )
            ->build();
    }

    public function actionUpdate($languageCode)
    {
        if ($languageCode) {
            $language = Language::findOne(['code' => $languageCode]);
            if ($language) {
                $telegramUser = $this->getTelegramUser();
                if ($telegramUser) {
                    $telegramUser->language_id = $language->id;
                    if ($telegramUser->save()) {
                        Yii::$app->language = $language->code;
                    }
                }
            }
        } else {
            return $this->actionIndex();
        }

        return $this->run('start/index');
    }

    public function actionSearch()
    {
        $text = $this->getUpdate()->getMessage()->getText();

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

        if (isset($language)) {
            return $this->actionUpdate($language->code);
        }
    }
}
