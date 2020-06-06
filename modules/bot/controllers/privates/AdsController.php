<?php
namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\models\AdCategory;

class AdsController extends Controller
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
                                'callback_data' => FindAdsController::createRoute('index', ['adCategoryId' => AdCategory::BUY_SELL_ID]),
                                'text' => '🔍 ' . AdCategory::getFindName(AdCategory::BUY_SELL_ID),
                            ],
                            [
                                'callback_data' => PlaceAdController::createRoute('index', ['adCategoryId' => AdCategory::BUY_SELL_ID]),
                                'text' => '💰 ' . AdCategory::getPlaceName(AdCategory::BUY_SELL_ID),
                            ],
                        ],
                        [
                            [
                                'callback_data' => FindAdsController::createRoute('index', ['adCategoryId' => AdCategory::RENT_ID]),
                                'text' => '🔍 ' . AdCategory::getFindName(AdCategory::RENT_ID),
                            ],
                            [
                                'callback_data' => PlaceAdController::createRoute('index', ['adCategoryId' => AdCategory::RENT_ID]),
                                'text' => '💰 ' . AdCategory::getPlaceName(AdCategory::RENT_ID),
                            ],
                        ],
                        [
                            [
                                'callback_data' => FindAdsController::createRoute('index', ['adCategoryId' => AdCategory::SERVICES_ID]),
                                'text' => '🔍 ' . AdCategory::getFindName(AdCategory::SERVICES_ID),
                            ],
                            [
                                'callback_data' => PlaceAdController::createRoute('index', ['adCategoryId' => AdCategory::SERVICES_ID]),
                                'text' => '💰 ' . AdCategory::getPlaceName(AdCategory::SERVICES_ID),
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
