<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\models\AdSection;

/**
 * Class SAdController
 *
 * @package app\modules\bot\controllers\privates
 */
class SAdController extends Controller
{
    public function actionIndex()
    {
        $buttons = [
            [
                [
                    'callback_data' => SAdOfferController::createRoute('index', ['adSection' => AdSection::BUY_SELL]),
                    'text' => Emoji::AD_OFFER . ' ' . AdSection::getAdOfferName(AdSection::BUY_SELL),
                ],
                [
                    'callback_data' => SAdSearchController::createRoute('index', ['adSection' => AdSection::BUY_SELL]),
                    'text' => Emoji::AD_SEARCH . ' ' . AdSection::getAdSearchName(AdSection::BUY_SELL),
                ],
            ],
            [
                [
                    'callback_data' => SAdOfferController::createRoute('index', ['adSection' => AdSection::RENT]),
                    'text' => Emoji::AD_OFFER . ' ' . AdSection::getAdOfferName(AdSection::RENT),
                ],
                [
                    'callback_data' => SAdSearchController::createRoute('index', ['adSection' => AdSection::RENT]),
                    'text' => Emoji::AD_SEARCH . ' ' . AdSection::getAdSearchName(AdSection::RENT),
                ],
            ],
            [
                [
                    'callback_data' => SAdOfferController::createRoute('index', ['adSection' => AdSection::SERVICES]),
                    'text' => Emoji::AD_OFFER . ' ' . AdSection::getAdOfferName(AdSection::SERVICES),
                ],
                [
                    'callback_data' => SAdSearchController::createRoute('index', ['adSection' => AdSection::SERVICES]),
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
