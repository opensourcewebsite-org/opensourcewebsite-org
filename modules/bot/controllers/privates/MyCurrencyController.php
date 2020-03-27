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
use app\modules\bot\components\Controller as Controller;

/**
 * Class MyCurrencyController
 *
 * @package app\modules\bot\controllers
 */
class MyCurrencyController extends Controller
{
    /**
     * @param null|string $currency
     *
     * @return array
     */
    public function actionIndex($currency = null)
    {
        $telegramUser = $this->getTelegramUser();

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
                                'callback_data' => MyProfileController::createRoute(),
                                'text' => '🔙',
                            ],
                            [
                                'callback_data' => MyCurrencyController::createRoute('list'),
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
     */
    public function actionList($page = 1)
    {
        $update = $this->getUpdate();

        $currencyQuery = Currency::find()->orderBy('code ASC');
        $pagination = new Pagination([
            'totalCount' => $currencyQuery->count(),
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

        $paginationButtons = PaginationButtons::build($pagination, function ($page) {
            return self::createRoute('list', [
                'page' => $page,
            ]);
        });
        $buttons = [];

        if ($currencies) {
            foreach ($currencies as $currency) {
                $buttons[][] = [
                    'callback_data' => self::createRoute('index', [
                        'currency' => $currency->code,
                    ]),
                    'text' => strtoupper($currency->code) . ' - ' . $currency->name,
                ];
            }

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }

            $buttons[][] = [
                'callback_data' => self::createRoute(),
                'text' => '🔙',
            ];
        }

        return [
            new EditMessageTextCommand(
                $this->getTelegramChat()->chat_id,
                $update->getCallbackQuery()->getMessage()->getMessageId(),
                $this->render('list'),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup($buttons),
                ]
            ),
            new AnswerCallbackQueryCommand(
                $update->getCallbackQuery()->getId()
            ),
        ];
    }
}
