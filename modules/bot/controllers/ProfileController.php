<?php

namespace app\modules\bot\controllers;

use app\models\Currency;
use app\models\Language;
use app\modules\bot\CommandController;
use app\modules\bot\helpers\PaginationButtons;
use app\modules\bot\telegram\Message;
use yii\data\Pagination;

/**
 * Class ProfileController
 *
 * @package app\modules\bot\controllers
 */
class ProfileController extends CommandController
{

    /**
     * @return string
     */
    public function actionProfile()
    {
        /** @var Message $requestMessage */
        $requestMessage = \Yii::$app->requestMessage;

        return $this->render('profile', ['profile' => $requestMessage->getFrom()]);
    }

    /**
     * @return string
     */
    public function actionRating()
    {
        $params = [
            'active_rating' => 0,
            'overall_rating' => [0, 1000],
            'ranking' => [120, 120],
        ];

        return $this->render('rating', $params);
    }

    /**
     * @param null|string $language
     *
     * @return string
     */
    public function actionLanguage($language = null)
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

        return $this->render('language', compact('languageModel', 'currentCode', 'currentName'));
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

    /**
     * @param null|string $currency
     *
     * @return string
     */
    public function actionCurrency($currency = null)
    {
        \Yii::$app->responseMessage->setKeyboard(new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup(
            [
                [
                    ['callback_data' => 'currency_list', 'text' => \Yii::t('bot', 'Change Currency')],
                ],
            ]
        ));

        $botClient = \Yii::$app->botClient->getModel();

        $currencyModel = null;
        if ($currency) {
            $currencyModel = Currency::findOne(['code' => $currency]);
            if ($currencyModel) {
                if ($botClient) {
                    $botClient->currency_code = $currency;
                    $botClient->save();
                }
            }
        }

        $currentCode = $botClient->currency_code;
        $currentName = $currencyModel ? $currencyModel->name : Currency::findOne(['code' => $currentCode])->name;

        return $this->render('currency', compact('currencyModel', 'currentCode', 'currentName'));
    }

    /**
     * @param int $page
     *
     * @return string
     * @throws \TelegramBot\Api\InvalidArgumentException
     */
    public function actionCurrencyList($page = 1)
    {
        $currencyQuery = Currency::find()->orderBy('code ASC');
        $countQuery = clone $currencyQuery;
        $pagination = new Pagination([
            'totalCount' => $countQuery->count(),
            'params' => [
                'pageSize' => 20,
                'page' => $page,
            ],
        ]);

        $pagination->pageSizeParam = false;
        $pagination->validatePage = true;

        $currencies = $currencyQuery->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        \Yii::$app->responseMessage->setKeyboard(PaginationButtons::build('currency_list_<page>', $pagination));

        /** @var Message $responseMessage */
        $responseMessage = \Yii::$app->responseMessage;
        $responseMessage->setMessageId(\Yii::$app->requestMessage->getMessageId());

        return $this->render('currency-list', compact('currencies', 'pagination'));
    }
}