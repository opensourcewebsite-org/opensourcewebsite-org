<?php

namespace app\modules\bot\controllers\privates;

use app\models\Currency;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;
use Yii;
use yii\data\Pagination;

/**
 * Class GroupCurrencyController
 *
 * @package app\modules\bot\controllers\privates
 */
class GroupCurrencyController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($chatId = null)
    {
        return $this->actionList($chatId);
    }

    public function actionList($chatId = null, $page = 1)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(self::createRoute('input', [
            'chatId' => $chatId,
        ]));

        $query = Currency::find()
            ->orderBy(['code' => SORT_ASC]);

        $pagination = new Pagination([
            'totalCount' => $query->count(),
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $currencies = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $buttons = [];

        if ($currencies) {
            foreach ($currencies as $currency) {
                $buttons[][] = [
                    'callback_data' => self::createRoute('select', [
                        'chatId' => $chatId,
                        'code' => $currency->code,
                    ]),
                    'text' => $currency->code . ' - ' . $currency->name,
                ];
            }

            $paginationButtons = PaginationButtons::build($pagination, function ($page) use ($chatId) {
                return self::createRoute('list', [
                    'chatId' => $chatId,
                    'page' => $page,
                ]);
            });

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $buttons[][] = [
            'callback_data' => GroupController::createRoute('view', [
                'chatId' => $chatId,
            ]),
            'text' => Emoji::BACK,
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('list'),
                $buttons
            )
            ->build();
    }

    public function actionSelect($chatId = null, $code = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if (!$code) {
            return $this->actionList();
        }

        $currency = Currency::findOne([
            'code' => $code,
        ]);

        if (!$currency) {
            return $this->actionList();
        }

        $chat->currency_id = $currency->id;

        if ($chat->validate('currency_id') && $chat->save(false)) {
            return $this->run('group/view', [
                'chatId' => $chatId,
            ]);
        }

        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }

    public function actionInput($chatId = null)
    {
        if ($text = $this->getUpdate()->getMessage()->getText()) {
            if (strlen($text) <= 3) {
                $currency = Currency::find()
                    ->orFilterWhere(['like', 'code', $text, false])
                    ->one();
            } else {
                $currency = Currency::find()
                    ->orFilterWhere(['like', 'name', $text . '%', false])
                    ->one();
            }

            if (isset($currency)) {
                return $this->actionSelect($chatId, $currency->code);
            }
        }

        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }
}
