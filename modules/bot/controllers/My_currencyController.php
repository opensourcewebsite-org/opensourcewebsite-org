<?php

namespace app\modules\bot\controllers;

use app\models\Currency;
use app\modules\bot\helpers\PaginationButtons;
use yii\data\Pagination;
use Yii;
use \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

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
        $botClient = $this->module->botClient;

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

        return [
            [
                'type' => 'message',
                'text' => $this->render('index', compact('currencyModel', 'currentCode', 'currentName')),
                'replyMarkup' => new InlineKeyboardMarkup(
            [
                [
                    [
                        'callback_data' => '/currency_list',
                        'text' => Yii::t('bot', 'Change Currency')
                    ],
                ],
            ]
        )
            ]
        ];
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

        return [
            [
                'type' => 'editMessage',
                'text' => $this->render('currency-list', compact('currencies', 'pagination')),
                'replyMarkup' => PaginationButtons::build('currency_list_<page>', $pagination)
            ]
        ];
    }
}
