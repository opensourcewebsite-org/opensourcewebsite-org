<?php

namespace app\modules\bot\controllers\privates;

use app\models\Currency;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\filters\GroupActiveAdministratorAccessFilter;
use Yii;
use yii\data\Pagination;

/**
 * Class GroupCurrencyController
 *
 * @package app\modules\bot\controllers\privates
 */
class GroupCurrencyController extends Controller
{
    public function behaviors()
    {
        return [
            'groupActiveAdministratorAccess' => [
                'class' => GroupActiveAdministratorAccessFilter::class,
            ],
        ];
    }

    /**
     * @param int $id Chat->id
     * @return array
     */
    public function actionIndex($id = null)
    {
        return $this->actionList($id);
    }

    /**
     * @param int $id Chat->id
     * @return array
     */
    public function actionList($id = null, $page = 1)
    {
        $chat = Yii::$app->cache->get('chat');

        $this->getState()->setInputRoute(self::createRoute('input', [
            'id' => $chat->id,
        ]));

        $query = Currency::find()
            ->orderBy([
                'code' => SORT_ASC,
            ]);

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
                        'id' => $chat->id,
                        'code' => $currency->code,
                    ]),
                    'text' => $currency->code . ' - ' . $currency->name,
                ];
            }

            $paginationButtons = PaginationButtons::build($pagination, function ($page) use ($chat) {
                return self::createRoute('list', [
                    'id' => $chat->id,
                    'page' => $page,
                ]);
            });

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $buttons[] = [
            [
                'callback_data' => GroupController::createRoute('view', [
                    'chatId' => $chat->id,
                ]),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => self::createRoute('delete', [
                    'id' => $chat->id,
                ]),
                'text' => Emoji::DELETE,
                'visible' => (bool)$chat->currency,
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('list'),
                $buttons
            )
            ->build();
    }

    /**
     * @param int $id Chat->id
     * @return array
     */
    public function actionSelect($id = null, $code = null)
    {
        $chat = Yii::$app->cache->get('chat');

        if ($code) {
            $currency = Currency::findOne([
                'code' => $code,
            ]);

            if ($currency) {
                $chat->currency_id = $currency->id;

                if ($chat->validate('currency_id') && $chat->save(false)) {
                    return $this->run('group/view', [
                        'chatId' => $chat->id,
                    ]);
                }
            }
        }

        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }

    /**
     * @param int $id Chat->id
     * @return array
     */
    public function actionInput($id = null)
    {
        $chat = Yii::$app->cache->get('chat');

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
                return $this->actionSelect($chat->id, $currency->code);
            }
        }

        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }

    /**
     * @param int $id Chat->id
     * @return array
     */
    public function actionDelete($id = null)
    {
        $chat = Yii::$app->cache->get('chat');

        if ($chat->currency) {
            $chat->currency_id = null;

            if ($chat->save(false)) {
                return $this->run('group/view', [
                    'chatId' => $chat->id,
                ]);
            }
        }

        return $this->getResponseBuilder()
        ->answerCallbackQuery()
        ->build();
    }
}
