<?php

namespace app\modules\bot\controllers;

use app\models\Currency;
use app\modules\bot\components\CommandController as Controller;
use app\modules\bot\helpers\PaginationButtons;
use app\modules\bot\telegram\Message;
use yii\data\Pagination;

/**
 * Class My_currencyController
 *
 * @package app\modules\bot\controllers
 */
class My_currencyController extends Controller
{
    /**
     * @param null|string $currency
     *
     * @return string
     */
    public function actionIndex($currency = null)
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

        return $this->render('index', compact('currencyModel', 'currentCode', 'currentName'));
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
