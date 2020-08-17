<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\crud\rules\LocationToArrayFieldComponent;
use app\modules\bot\components\helpers\Emoji;

/**
 * Class MyLocationController
 *
 * @package app\modules\bot\controllers
 */
class MyLocationController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $telegramUser = $this->getTelegramUser();

        $this->getState()->setName(self::createRoute('update'));

        if (isset($telegramUser->location_lon) && isset($telegramUser->location_lat)) {
            return $this->getResponseBuilder()
                ->sendLocation(
                    $telegramUser->location_lat,
                    $telegramUser->location_lon
                )
                ->editMessageTextOrSendMessage(
                    $this->render('index'),
                    [
                        [
                            [
                                'callback_data' => MyAccountController::createRoute(),
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

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index'),
                [
                    [
                        [
                            'callback_data' => MyAccountController::createRoute(),
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

    public function actionUpdate()
    {
        $telegramUser = $this->getTelegramUser();
        $locationComponent = Yii::createObject([
            'class' => LocationToArrayFieldComponent::class,
        ], [$this, []]);
        $text = '';
        if ($message = $this->getUpdate()->getMessage()) {
            $text = $message->getText();
        }
        $locations = $locationComponent->prepare($text);
        if ($locations['location_lat'] && $locations['location_lon']) {
            $telegramUser->setAttributes([
                'location_lon' => $locations['location_lon'],
                'location_lat' => $locations['location_lat'],
                'location_at' => time(),
            ]);
            $telegramUser->save();
        }

        return $this->actionIndex();
    }
}
