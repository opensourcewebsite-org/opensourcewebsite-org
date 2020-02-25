<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\models\Currency;
use app\modules\bot\helpers\PaginationButtons;
use yii\data\Pagination;
use \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use \app\modules\bot\components\response\SendMessageCommand;
use \app\modules\bot\components\response\EditMessageTextCommand;
use \app\modules\bot\components\response\AnswerCallbackQueryCommand;
use app\modules\bot\components\Controller;

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
     * @return array
     */
    public function actionIndex($currency = null)
    {
        $telegramUser = $this->getTelegramUser();
        $update = $this->getUpdate();

        $currencyModel = null;
        if ($currency) {
            $currencyModel = Currency::findOne(['code' => $currency]);
            if ($currencyModel) {
                if ($telegramUser) {
                    $telegramUser->currency_code = $currency;
                    $telegramUser->save();
                }
            }
        }

        $currentCode = $telegramUser->currency_code;
        $currentName = $currencyModel ? $currencyModel->name : Currency::findOne(['code' => $currentCode])->name;

        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('index', compact('currencyModel', 'currentCode', 'currentName')),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/my_profile',
                                'text' => '🔙',
                            ],
                            [
                                'callback_data' => '/currency_list',
                                'text' => '✏️',
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }

    /**
     * @param int $page
     *
     * @return array
     * @throws \TelegramBot\Api\InvalidArgumentException
     */
    public function actionCurrencyList($page = 1)
    {
        $update = $this->getUpdate();

        $currencyQuery = Currency::find()->orderBy('code ASC');
        $countQuery = clone $currencyQuery;
        $pagination = new Pagination([
            'totalCount' => $countQuery->count(),
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
        ]);

        $pagination->pageSizeParam = false;
        $pagination->validatePage = true;

        $currencies = $currencyQuery->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $paginationButtons = PaginationButtons::build('/currency_list_', $pagination);
        $listButtons = [];
        if ($currencies) {
            foreach ($currencies as $currency) {
                $listButtons[][] = ['callback_data' => '/my_currency_' . $currency->code, 'text' => strtoupper($currency->code) . ' - ' . $currency->name];
            }

            if ($paginationButtons) {
                $listButtons[] = $paginationButtons;
            }

            $listButtons[][] = ['callback_data' => '/my_currency', 'text' => '🔙'];
        }

        Yii::warning($listButtons);

        return [
            new EditMessageTextCommand(
                $this->getTelegramChat()->chat_id,
                $update->getCallbackQuery()->getMessage()->getMessageId(),
                $this->render('currency-list'),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup($listButtons),
                ]
            ),
            new AnswerCallbackQueryCommand(
                $update->getCallbackQuery()->getId()
            ),
        ];
    }
}
