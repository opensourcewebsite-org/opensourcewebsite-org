<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller;
use app\models\Currency;
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
    public function actionIndex($currencyCode = null)
    {
        $user = $this->getUser();

        if ($currencyCode) {
            $currency = Currency::findOne(['code' => $currencyCode]);

            if ($currency) {
                $user->currency_id = $currency->id;
                $user->save();
            }
        }

        if (!$user->currency_id) {
            return $this->actionUpdate();
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'currency' => $user->currency,
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
                            'callback_data' => MyCurrencyController::createRoute('update'),
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
    public function actionUpdate($page = 1)
    {
        $user = $this->getUser();

        $this->getState()->setName(self::createRoute('search'));

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
            return self::createRoute('update', [
                'page' => $page,
            ]);
        });

        $buttons = [];

        if ($currencies) {
            foreach ($currencies as $currency) {
                $buttons[][] = [
                    'callback_data' => self::createRoute('index', [
                        'currencyCode' => $currency->code,
                    ]),
                    'text' => $currency->code . ' - ' . $currency->name,
                ];
            }

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }

            $buttons[][] = [
                'callback_data' => ($user->currency_id ? self::createRoute() : MyProfileController::createRoute()),
                'text' => Emoji::BACK,
            ];
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('update'),
                $buttons
            )
            ->build();
    }

    public function actionSearch()
    {
        $text = $this->getUpdate()->getMessage()->getText();

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
            return $this->actionIndex($currency->code);
        }
    }
}
