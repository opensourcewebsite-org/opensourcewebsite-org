<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\Controller;

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
                ->editMessageTextOrSendMessage(
                    $this->render('header')
                )
                ->sendLocation(
                    $telegramUser->location_lat,
                    $telegramUser->location_lon
                )
                ->sendMessage(
                    $this->render('footer'),
                    [
                        [
                            [
                                'callback_data' => MyProfileController::createRoute(),
                                'text' => Emoji::BACK,
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
                            'callback_data' => MyProfileController::createRoute(),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionUpdate()
    {
        $telegramUser = $this->getTelegramUser();
        $update = $this->getUpdate();

        if ($update->getMessage() && ($location = $update->getMessage()->getLocation())) {
            $telegramUser->setAttributes([
                'location_lon' => $location->getLongitude(),
                'location_lat' => $location->getLatitude(),
                'location_at' => time(),
            ]);
            $telegramUser->save();
        }

        return $this->actionIndex();
    }
}
