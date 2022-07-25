<?php

namespace app\modules\bot\controllers\privates;

use app\models\AdSection;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use Yii;

/**
 * Class AdController
 *
 * @package app\modules\bot\controllers\privates
 */
class AdController extends Controller
{
    public function actionIndex()
    {
        $this->getState()->setName(null);

        $buttons = [
            [
                [
                    'callback_data' => AdOfferController::createRoute('index', ['adSection' => AdSection::BUY_SELL]),
                    'text' => Emoji::AD_OFFER . ' ' . AdSection::getAdOfferName(AdSection::BUY_SELL),
                ],
                [
                    'callback_data' => AdSearchController::createRoute('index', ['adSection' => AdSection::BUY_SELL]),
                    'text' => Emoji::AD_SEARCH . ' ' . AdSection::getAdSearchName(AdSection::BUY_SELL),
                ],
            ],
            [
                [
                    'callback_data' => AdOfferController::createRoute('index', ['adSection' => AdSection::RENT]),
                    'text' => Emoji::AD_OFFER . ' ' . AdSection::getAdOfferName(AdSection::RENT),
                ],
                [
                    'callback_data' => AdSearchController::createRoute('index', ['adSection' => AdSection::RENT]),
                    'text' => Emoji::AD_SEARCH . ' ' . AdSection::getAdSearchName(AdSection::RENT),
                ],
            ],
            [
                [
                    'callback_data' => AdOfferController::createRoute('index', ['adSection' => AdSection::SERVICES]),
                    'text' => Emoji::AD_OFFER . ' ' . AdSection::getAdOfferName(AdSection::SERVICES),
                ],
                [
                    'callback_data' => AdSearchController::createRoute('index', ['adSection' => AdSection::SERVICES]),
                    'text' => Emoji::AD_SEARCH . ' ' . AdSection::getAdSearchName(AdSection::SERVICES),
                ],
            ],
            [
                [
                    'callback_data' => MenuController::createRoute(),
                    'text' => Emoji::MENU,
                ],
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index'),
                $buttons
            )
            ->build();
    }
}
