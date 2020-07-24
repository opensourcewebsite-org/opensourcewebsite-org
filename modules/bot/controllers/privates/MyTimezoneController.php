<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\helpers\PaginationButtons;
use yii\data\Pagination;
use app\modules\bot\components\Controller as Controller;
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

        if ($timezone) {
            if (array_key_exists($timezone, $timezones)) {
                $user->timezone = $timezone;
                $user->save();
            }
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'timezone' => $timezones[$user->timezone],
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
        $update = $this->getUpdate();
        $user = $this->getUser();

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

        $timezones = array_slice($timezones, $pagination->offset, $pagination->limit);

        $paginationButtons = PaginationButtons::build($pagination, function ($page) {
            return self::createRoute('list', [
                'page' => $page,
            ]);
        });
        $buttons = [];

        if ($timezones) {
            foreach ($timezones as $timezone => $fullName) {
                $buttons[][] = [
                    'text' => $fullName,
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
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('list'),
                $buttons
            )
            ->build();
    }
}
