<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\components\Controller;

/**
 * Class MyCitizenshipController
 *
 * @package app\modules\bot\controllers
 */
class MyCitizenshipController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($citizenShip = null)
    {
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('index'),
                [
                    [
                        [
                            'callback_data' => self::createRoute(),
                            'text' => 'Country 1',
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute(),
                            'text' => 'Country 2',
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute(),
                            'text' => '<',
                        ],
                        [
                            'callback_data' => self::createRoute(),
                            'text' => '1/3',
                        ],
                        [
                            'callback_data' => self::createRoute(),
                            'text' => '>',
                        ],
                    ],
                    [
                        [
                            'callback_data' => MyProfileController::createRoute(),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => self::createRoute('add'),
                            'text' => Emoji::ADD,
                        ],
                    ],
                ]
            )
            ->build();
    }
}
