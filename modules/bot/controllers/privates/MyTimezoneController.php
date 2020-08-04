<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\PaginationButtons;
use yii\data\Pagination;
use app\components\helpers\TimeHelper;
use app\modules\bot\components\helpers\Emoji;

/**
 * Class MyTimezoneController
 *
 * @package app\modules\bot\controllers
 */
class MyTimezoneController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($timezone = null)
    {
        $user = $this->getUser();
        $timezones = TimeHelper::timezonesList();

        if (($timezone >= -720) && ($timezone <= 840)) {
            $user->timezone = $timezone;
            $user->save();
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'timezone' => TimeHelper::getNameByOffset($user->timezone),
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
                            'callback_data' => self::createRoute('list'),
                            'text' => Emoji::EDIT,
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionList($page = 2)
    {
        $timezones = TimeHelper::timezonesList();

        $pagination = new Pagination([
            'totalCount' => count($timezones),
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
        ]);

        $pagination->pageSizeParam = false;
        $pagination->validatePage = true;
        $timezones = array_slice($timezones, $pagination->offset, $pagination->limit, true);

        $paginationButtons = PaginationButtons::build($pagination, function ($page) {
            return self::createRoute('list', [
                'page' => $page,
            ]);
        });

        $buttons = [];

        foreach ($timezones as $timezone => $name) {
            $buttons[][] = [
                'text' => $name,
                'callback_data' => self::createRoute('index', [
                    'timezone' => $timezone,
                ]),
            ];
        }

        if ($paginationButtons) {
            $buttons[] = $paginationButtons;
        }

        $buttons[][] = [
            'callback_data' => self::createRoute(),
            'text' => Emoji::BACK,
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('list'),
                $buttons
            )
            ->build();
    }
}
