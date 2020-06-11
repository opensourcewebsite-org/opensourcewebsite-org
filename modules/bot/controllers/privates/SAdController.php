<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\models\AdSection;

/**
 * Class SAdController
 *
 * @package app\modules\bot\controllers\privates
 */
class SAdController extends Controller
{
    public function actionIndex()
    {
        if ($this->getTelegramUser()->provider_user_name) {
            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->editMessageTextOrSendMessage(
                    $this->render('index'),
                    [
                        [
                            [
                                'callback_data' => SAdSearchController::createRoute('index', ['adSection' => AdSection::BUY_SELL]),
                                'text' => '🔍 ' . AdSection::getAdSearchName(AdSection::BUY_SELL),
                            ],
                            [
                                'callback_data' => SAdOfferController::createRoute('index', ['adSection' => AdSection::BUY_SELL]),
                                'text' => '💰 ' . AdSection::getAdOfferName(AdSection::BUY_SELL),
                            ],
                        ],
                        [
                            [
                                'callback_data' => SAdSearchController::createRoute('index', ['adSection' => AdSection::RENT]),
                                'text' => '🔍 ' . AdSection::getAdSearchName(AdSection::RENT),
                            ],
                            [
                                'callback_data' => SAdOfferController::createRoute('index', ['adSection' => AdSection::RENT]),
                                'text' => '💰 ' . AdSection::getAdOfferName(AdSection::RENT),
                            ],
                        ],
                        [
                            [
                                'callback_data' => SAdSearchController::createRoute('index', ['adSection' => AdSection::SERVICES]),
                                'text' => '🔍 ' . AdSection::getAdSearchName(AdSection::SERVICES),
                            ],
                            [
                                'callback_data' => SAdOfferController::createRoute('index', ['adSection' => AdSection::SERVICES]),
                                'text' => '💰 ' . AdSection::getAdOfferName(AdSection::SERVICES),
                            ],
                        ],
                        [
                            [
                                'callback_data' => ServicesController::createRoute(),
                                'text' => Emoji::BACK,
                            ],
                            [
                                'callback_data' => MenuController::createRoute(),
                                'text' => Emoji::MENU,
                            ],
                        ],
                    ]
                )
                ->build();
        } else {
            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->editMessageTextOrSendMessage(
                    $this->render('no-requirements'),
                    [
                        [
                            [
                                'callback_data' => ServicesController::createRoute(),
                                'text' => Emoji::BACK,
                            ],
                            [
                                'callback_data' => MenuController::createRoute(),
                                'text' => Emoji::MENU,
                            ],
                        ],
                    ]
                )
                ->build();
        }
    }
}
