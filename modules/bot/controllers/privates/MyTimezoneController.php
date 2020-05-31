<?php

namespace app\modules\bot\controllers\privates;

use app\models\Timezone;
use app\modules\bot\components\helpers\Emoji;

use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\components\Controller;

/**
 * Class MyTimezoneController
 *
 * @package app\modules\bot\controllers
 */
class MyTimezoneController extends Controller
{
    /**
     * @param null $timezoneId
     * @return array
     */
    public function actionIndex($timezoneId = null)
    {
        $user = $this->getUser();

        $timezone = Timezone::findOne($timezoneId);
        if (isset($timezone)) {
            $user->timezone_id = $timezone->id;
            $user->save();
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'timezone' => $user->timezone->getUTCOffset(),
                ]),
                [
                    [
                        [
                            'callback_data' => MyProfileController::createRoute(),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => self::createRoute('list'),
                            'text' => Emoji::EDIT,
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionList($page = 22)
    {
        $timezoneButtons = PaginationButtons::buildFromQuery(
            Timezone::find()->orderBy('offset'),
            function ($page) {
                return self::createRoute('list', [
                    'page' => $page,
                ]);
            },
            function (Timezone $timezone) {
                return [
                    'text' => $timezone->getUTCOffset(),
                    'callback_data' => self::createRoute('index', [
                        'timezoneId' => $timezone->id,
                    ]),
                ];
            },
            $page
        );

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $text = $this->render('list'),
                array_merge($timezoneButtons, [
                    [
                        [
                            'callback_data' => self::createRoute(),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ])
            )
            ->build();
    }
}
