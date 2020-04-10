<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\response\ResponseBuilder;
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
        
        return ResponseBuilder::fromUpdate($this->getUpdate())
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
        $languageButtons = PaginationButtons::buildFromQuery(
            Language::find()->orderBy('code ASC'),
            function ($page) {
                return self::createRoute('list', [
                    'page' => $page,
                ]);
            },
            function (Language $language) {
                return [
                    'callback_data' => self::createRoute('index', [
                        'languageCode' => $language->code,
                    ]),
                    'text' => strtoupper($language->code) . ' - ' . $language->name,
                ];
            }, $page
        );

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('list'),
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
}
