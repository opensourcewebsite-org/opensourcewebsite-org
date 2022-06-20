<?php

namespace app\modules\bot\controllers\privates;

use app\models\Currency;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use yii\data\Pagination;

/**
 * Class MyCurrencyController
 *
 * @package app\modules\bot\controllers\privates
 */
class MyCurrencyController extends Controller
{
    /**
     * @param null|string $currencyCode
     *
     * @return array
     */
    public function actionIndex()
    {
        $globalUser = $this->getUser();

        if (!$globalUser->currency_id) {
            return $this->actionList();
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'currency' => $globalUser->currency,
                ]),
                [
                    [
                        [
                            'callback_data' => MyProfileController::createRoute(),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                        [
                            'callback_data' => MyCurrencyController::createRoute('list'),
                            'text' => Emoji::EDIT,
                        ],
                    ],
                ]
            )
            ->build();
    }

    /**
     * @param int $page
     *
     * @return array
     */
    public function actionList($page = 1)
    {
        $this->getState()->setName(self::createRoute('input'));

        $globalUser = $this->getUser();

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

        $currencies = $query
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $paginationButtons = PaginationButtons::build($pagination, function ($page) {
            return self::createRoute('update', [
                'page' => $page,
            ]);
        });

        $buttons = [];

        if ($currencies) {
            foreach ($currencies as $currency) {
                $buttons[][] = [
                    'callback_data' => self::createRoute('select', [
                        'code' => $currency->code,
                    ]),
                    'text' => $currency->code . ' - ' . $currency->name,
                ];
            }

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $buttons[] = [
            [
                'callback_data' => ($globalUser->currency_id ? self::createRoute() : MyProfileController::createRoute()),
                'text' => Emoji::BACK,
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('list'),
                $buttons
            )
            ->build();
    }

    public function actionSelect($code = null)
    {
        $globalUser = $this->getUser();

        if (!$code) {
            return $this->actionList();
        }

        $currency = Currency::findOne([
            'code' => $code,
        ]);

        if ($currency) {
            $globalUser->currency_id = $currency->id;
            $globalUser->save();
        }

        return $this->actionIndex();
    }

    public function actionInput()
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
                return $this->actionSelect($currency->code);
            }
        }

        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }
}
