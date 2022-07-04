<?php

namespace app\modules\bot\controllers\privates;

use app\behaviors\SetAttributeValueBehavior;
use app\behaviors\SetDefaultCurrencyBehavior;
use app\models\Currency;
use app\models\CurrencyExchangeOrder;
use app\models\CurrencyExchangeOrderBuyingPaymentMethod;
use app\models\CurrencyExchangeOrderMatch;
use app\models\CurrencyExchangeOrderSellingPaymentMethod;
use app\models\matchers\CurrencyExchangeOrderMatcher;
use app\models\PaymentMethod;
use app\models\User;
use app\modules\bot\components\Controller;
use app\modules\bot\components\crud\rules\ExplodeStringFieldComponent;
use app\modules\bot\components\crud\rules\LocationToArrayFieldComponent;
use app\modules\bot\components\crud\services\IntermediateFieldService;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\validators\RadiusValidator;
use Yii;
use yii\data\Pagination;
use yii\db\ActiveRecord;

/**
 * Class CaController
 *
 * @link https://opensourcewebsite.org/currency-exchange-order
 * @package app\modules\bot\controllers\privates
 */
class CaController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        return $this->actionMatches();
    }

    /**
     * @param int $page
     *
     * @return array
     */
    public function actionMatches($page = 1)
    {
        $this->getState()->setName(null);

        $globalUser = $this->getUser();

        $londonCenter = [51.509865, -0.118092];

        // TODO add custom order
        $order = new CurrencyExchangeOrder([
            'user_id' => $globalUser->id,
            'selling_currency_id' => 108, // USD
            'buying_currency_id' => 35, // EUR
            'selling_delivery_radius' => 1000,
            'buying_delivery_radius' => 1000,
            'selling_location_lat' => $londonCenter[0],
            'selling_location_lon' => $londonCenter[1],
            'buying_location_lat' => $londonCenter[0],
            'buying_location_lon' => $londonCenter[1],
        ]);

        if (!isset($order)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $query = $order->getCashMatchesOrderByRank();
        $matchesCount = $query->count();

        if (!$matchesCount) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery(
                    $this->render('alert-no-matches'),
                    true
                )
                ->build();
        }

        $pagination = new Pagination([
            'totalCount' => $matchesCount,
            'pageSize' => 1,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $matchOrder = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->one();

        if (!$matchOrder) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery(
                    $this->render('alert-no-matches'),
                    true
                )
                ->build();
        }

        $buttons[] = PaginationButtons::build($pagination, function ($page) use ($order) {
            return self::createRoute('matches', [
                'page' => $page,
            ]);
        });

        $buttons[] = [
            [
                'callback_data' => self::createRoute(),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('match', [
                    'model' => $matchOrder,
                ]),
                $buttons,
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }
}
