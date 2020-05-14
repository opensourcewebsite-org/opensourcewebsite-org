<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\helpers\Emoji;

use Yii;
use app\models\Language;
use app\modules\bot\components\helpers\PaginationButtons;
use yii\data\Pagination;
use app\modules\bot\components\Controller;

/**
 * Class MyLanguageController
 *
 * @package app\modules\bot\controllers
 */
class LanguageController extends Controller
{
    /**
     * @param null|string $languageCode
     *
     * @return array
     */
    public function actionIndex($languageCode = null)
    {
        $telegramUser = $this->getTelegramUser();

        $language = null;
        if ($languageCode) {
            $language = Language::findOne(['code' => $languageCode]);
            if ($language) {
                if ($telegramUser) {
                    $telegramUser->language_id = $language->id;
                    if ($telegramUser->save()) {
                        Yii::$app->language = $language->code;
                    }
                }
            }
        }

        $language = $language ?? $telegramUser->language;
        $languageCode = isset($language) ? $language->code : null;
        $languageName = isset($language) ? $language->name : null;
        
        return $this->getResponseBuilder()($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('index', compact('languageCode', 'languageName')),
                [
                    [
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => LanguageController::createRoute('list'),
                            'text' => Emoji::EDIT,
                        ],
                    ],
                ]
            )
            ->build();
    }

    /**
     * @param int $page
     *
     * @return array
     */
    public function actionList($page = 1)
    {
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
            return self::createRoute('list', [
                'page' => $page,
            ]);
        });

        $languageRows = array_map(function ($language) {
            return [
                [
                    'callback_data' => self::createRoute('index', [
                        'languageCode' => $language->code,
                    ]),
                    'text' => strtoupper($language->code) . ' - ' . $language->name,
                ]
            ];
        }, $languages);

        return $this->getResponseBuilder()($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('list'),
                array_merge($languageRows, [ $paginationButtons ], [
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
}
