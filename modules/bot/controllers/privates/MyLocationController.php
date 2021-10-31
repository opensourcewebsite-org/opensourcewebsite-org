<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\crud\rules\LocationToArrayFieldComponent;
use app\modules\bot\components\helpers\Emoji;
use app\models\UserLocation;

/**
 * Class MyLocationController
 *
 * @package app\modules\bot\controllers\privates
 */
class MyLocationController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        if ($userLocation = $this->user->userLocation) {
            return $this->getResponseBuilder()
                ->editMessageTextOrSendMessage(
                    $this->render('index', [
                        'userLocation' => $userLocation,
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
                                'callback_data' => self::createRoute('update'),
                                'text' => Emoji::EDIT,
                            ],
                            [
                                'callback_data' => self::createRoute('delete'),
                                'text' => Emoji::DELETE,
                            ],
                        ],
                    ],
                    [
                        'disablePreview' => true,
                    ]
                )
                ->build();
        }

        return $this->actionUpdate();
    }

    public function actionUpdate()
    {
        $this->getState()->setName(self::createRoute('update'));

        if (!$userLocation = $this->user->userLocation) {
            $userLocation = new UserLocation();
            $userLocation->user_id = $this->user->id;
        }

        $locationComponent = Yii::createObject([
            'class' => LocationToArrayFieldComponent::class,
        ], [$this, []]);

        $text = '';

        if ($message = $this->getUpdate()->getMessage()) {
            $text = $message->getText();
        }

        $locations = $locationComponent->prepare($text);

        if ($locations['location_lat'] && $locations['location_lon']) {
            if ($userLocation->isNewRecord || ($userLocation->location_lat != $locations['location_lat']) || ($userLocation->location_lon != $locations['location_lon'])) {
                $userLocation->location_lat = $locations['location_lat'];
                $userLocation->location_lon = $locations['location_lon'];
            }

            if ($userLocation->getDirtyAttributes() && $userLocation->save()) {
                unset($telegramUser->userLocation);

                $this->getState()->setName(null);

                return $this->actionIndex();
            }
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('update'),
                [
                    [
                        [
                            'callback_data' => ($userLocation->isNewRecord ? MyProfileController::createRoute() : self::createRoute()),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionDelete(): array
    {
        if ($userLocation = $this->user->userLocation) {
            $userLocation->delete();
            unset($this->user->userLocation);
        }

        return $this->run('my-profile/index');
    }
}
