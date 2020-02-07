<?php

namespace app\modules\bot\controllers;

use app\models\Currency;
use app\modules\bot\helpers\PaginationButtons;
use yii\data\Pagination;
use Yii;
use \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use \app\modules\bot\components\response\SendMessageCommandSender;
use \app\modules\bot\components\response\EditMessageTextCommandSender;
use \app\modules\bot\components\response\AnswerCallbackQueryCommandSender;
use \app\modules\bot\components\response\commands\SendMessageCommand;
use \app\modules\bot\components\response\commands\EditMessageTextCommand;
use \app\modules\bot\components\response\commands\AnswerCallbackQueryCommand;
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
        $botClient = $this->getBotClient();
        $update = $this->getUpdate();

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

        $text = $this->render('index', compact('currencyModel', 'currentCode', 'currentName'));

        return [
            new SendMessageCommandSender(
                new SendMessageCommand([
                    'chatId' => $update->getMessage()->getChat()->getId(),
                    'parseMode' => 'html',
                    'text' => $this->prepareText($text),
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/currency_list',
                                'text' => Yii::t('bot', 'Change Currency')
                            ],
                        ],
                    ])
                ])
            ),
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
        $update = $this->getUpdate();

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

        $text = $this->render('currency-list', compact('currencies', 'pagination'));

        return [
            new EditMessageTextCommandSender(
                new EditMessageTextCommand([
                    'chatId' => $update->getCallbackQuery()->getMessage()->getChat()->getId(),
                    'messageId' => $update->getCallbackQuery()->getmessage()->getMessageId(),
                    'parseMode' => 'html',
                    'text' => $this->prepareText($text),
                    'replyMarkup' => PaginationButtons::build('/currency_list_<page>', $pagination),
                ])
            ),
            new AnswerCallbackQueryCommandSender(
                new AnswerCallbackQueryCommand([
                    'callbackQueryId' => $update->getCallbackQuery()->getId(),
                ])
            ),
        ];
    }
}
