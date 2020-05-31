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
        $buttons = [];

        foreach (AdCategory::find()->all() as $adCategory) {
            $buttons[] = [
                [
                    'callback_data' => FindAdsController::createRoute('index', ['adCategoryId' => $adCategory->id]),
                    'text' => '🔍 ' . Yii::t('bot', $adCategory->find_name),
                ],
                [
                    'callback_data' => PlaceAdController::createRoute('index', ['adCategoryId' => $adCategory->id]),
                    'text' => '💰 ' . Yii::t('bot', $adCategory->place_name),
                ],
            ];
        }

        $buttons[] = [
            [
                'callback_data' => ServicesController::createRoute(),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
        ];

        return ResponseBuilder::fromUpdate($this->getUpdate())
        	->editMessageTextOrSendMessage(
        		$this->render('index'),
        		$buttons
        	)
        	->build();
    }

    public function actionCreate()
    {
    	$buttons = [];

    	foreach (AdCategory::find()->all() as $adCategory) {
    		$buttons[][] = [
    			'callback_data' => PlaceAdController::createRoute('index', ['ad_category_id' => $adCategory->id]),
    			'text' => Yii::t('bot', $adCategory->name),
    		];
    	}

    	$buttons[][] = [
    		'callback_data' => self::createRoute(),
    		'text' => Emoji::BACK,
    	];

    	return ResponseBuilder::fromUpdate($this->getUpdate())
    		->editMessageTextOrSendMessage(
    			$this->render('create'),
    			$buttons
    		)->build();
    }
}
