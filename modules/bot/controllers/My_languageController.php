<?php

namespace app\modules\bot\controllers;

use app\models\Language;
use app\modules\bot\helpers\PaginationButtons;
use app\modules\bot\telegram\Message;
use yii\data\Pagination;
use Yii;
use \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

/**
 * Class My_languageController
 *
 * @package app\modules\bot\controllers
 */
class My_languageController extends Controller
{
    /**
     * @param null|string $language
     *
     * @return string
     */
    public function actionIndex($language = null)
    {
        $languageModel = null;
        if ($language) {
            $languageModel = Language::findOne(['code' => $language]);
            if ($languageModel) {
                $botClient = $this->module->botClient;
                if ($botClient) {
                    $botClient->language_code = $language;
                    if ($botClient->save()) {
                        Yii::$app->language = $languageModel->code;
                    }
                }
            }
        }

        $currentCode = \Yii::$app->language;
        $currentName = $languageModel ? $languageModel->name : Language::findOne(['code' => $currentCode])->name;

        return [
            [
                'type' => 'message',
                'text' => $this->render('index', compact('languageModel', 'currentCode', 'currentName')),
                'replyMarkup' => new InlineKeyboardMarkup(
                            [
                                [
                                    [
                                        'callback_data' => '/language_list',
                                        'text' => Yii::t('bot', 'Change Language')
                                    ],
                                ],
                            ])
            ]
        ];
    }

    /**
     * @param int $page
     *
     * @return string
     * @throws \TelegramBot\Api\InvalidArgumentException
     */
    public function actionLanguageList($page = 1)
    {
        $languageQuery = Language::find()->orderBy('code ASC');
        $countQuery = clone $languageQuery;
        $pagination = new Pagination([
            'totalCount' => $countQuery->count(),
            'params' => [
                'pageSize' => 20,
                'page' => $page,
            ],
        ]);

        $pagination->pageSizeParam = false;
        $pagination->validatePage = true;

        $languages = $languageQuery->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        return [
            [
                'type' => 'editMessage',
                'text' => $this->render('language-list', compact('languages', 'pagination')),
                'replyMarkup' => PaginationButtons::build('language_list_<page>', $pagination)
            ]
        ];
    }
}
