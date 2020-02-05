<?php

namespace app\modules\bot\controllers;

use app\models\Language;
use app\modules\bot\helpers\PaginationButtons;
use app\modules\bot\telegram\Message;
use yii\data\Pagination;

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
        \Yii::$app->responseMessage->setKeyboard(new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup(
            [
                [
                    ['callback_data' => 'language_list', 'text' => \Yii::t('bot', 'Change Language')],
                ],
            ]
        ));

        $languageModel = null;
        if ($language) {
            $languageModel = Language::findOne(['code' => $language]);
            if ($languageModel) {
                $botClient = \Yii::$app->botClient->getModel();
                if ($botClient) {
                    $botClient->language_code = $language;
                    if ($botClient->save()) {
                        \Yii::$app->language = $languageModel->code;
                    }
                }
            }
        }

        $currentCode = \Yii::$app->language;
        $currentName = $languageModel ? $languageModel->name : Language::findOne(['code' => $currentCode])->name;

        return $this->render('index', compact('languageModel', 'currentCode', 'currentName'));
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

        \Yii::$app->responseMessage->setKeyboard(PaginationButtons::build('language_list_<page>', $pagination));

        /** @var Message $responseMessage */
        $responseMessage = \Yii::$app->responseMessage;
        $responseMessage->setMessageId(\Yii::$app->requestMessage->getMessageId());

        return $this->render('language-list', compact('languages', 'pagination'));
    }
}
