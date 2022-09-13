<?php

namespace app\modules\bot\controllers\privates;

use app\models\UserLocation;
use app\modules\bot\components\Controller;
use app\modules\bot\components\crud\rules\LocationToArrayFieldComponent;
use app\modules\bot\components\helpers\Emoji;
use Yii;

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
        if (!$userLocation = $this->globalUser->userLocation) {
            return $this->actionSet();
        }

        $this->getState()->setName(null);

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
                            'callback_data' => self::createRoute('set'),
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

    public function actionSet()
    {
        $userLocation = $this->globalUser->userLocation ?: $this->globalUser->newUserLocation;

        if ($this->getUpdate()->getMessage()) {
            if ($text = $this->getUpdate()->getMessage()->getText()) {
                $locationComponent = Yii::createObject([
                    'class' => LocationToArrayFieldComponent::class,
                ], [$this, []]);

                $locations = $locationComponent->prepare($text);

                if ($locations['location_lat'] && $locations['location_lon']) {
                    $userLocation->location_lat = $locations['location_lat'];
                    $userLocation->location_lon = $locations['location_lon'];

                    if ($userLocation->validate()) {
                        $userLocation->save(false);
                        unset($this->globalUser->userLocation);

                        return $this->actionIndex();
                    }
                }
            }
        }

        $this->getState()->setName(self::createRoute('set'));

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set'),
                [
                    [
                        [
                            'callback_data' => (!$userLocation->isNewRecord ? self::createRoute() : MyProfileController::createRoute()),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionDelete(): array
    {
        if ($userLocation = $this->globalUser->userLocation) {
            $userLocation->delete();
            unset($this->globalUser->userLocation);
        }

        return $this->run('my-profile/index');
    }
}
