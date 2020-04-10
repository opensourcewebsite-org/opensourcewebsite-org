<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\response\ResponseBuilder;
use app\models\Currency;
use app\modules\bot\components\helpers\PaginationButtons;
use yii\data\Pagination;
use app\modules\bot\components\Controller;

/**
 * Class MyCurrencyController
 *
 * @package app\modules\bot\controllers
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

        $currency = null;
        if ($currencyCode) {
            $currency = Currency::findOne(['code' => $currencyCode]);
            if ($currency) {
                if ($user) {
                    $user->currency_id = $currency->id;
                    $user->save();
                }
            }
        }

        $currency = $currency ?? $user->currency;
        $currencyCode = isset($currency) ? $currency->code : null;
        $currencyName = isset($currency) ? $currency->name : null;

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('index', compact('currencyCode', 'currencyName')),
                [
                    [
                        [
                            'callback_data' => MyProfileController::createRoute(),
                            'text' => Emoji::BACK,
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
        $currencyButtons = PaginationButtons::buildFromQuery(
            Currency::find()->orderBy('code ASC'),
            function ($page) {
                return self::createRoute('list', [
                    'page' => $page,
                ]);
            },
            function (Currency $currency) {
                return [
                    'callback_data' => self::createRoute('index', [
                        'currencyCode' => $currency->code,
                    ]),
                    'text' => strtoupper($currency->code) . ' - ' . $currency->name,
                ];
            },
            $page
        );

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('list'),
                array_merge($currencyButtons, [
                    [
                        [
                            'callback_data' => self::createRoute(),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ])
            )
            ->build();
    }
}
