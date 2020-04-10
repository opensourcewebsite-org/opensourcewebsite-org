<?php

namespace app\modules\bot\controllers\privates;

use app\models\Timezone;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\components\Controller;
use yii\data\Pagination;

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

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'timezone' => '(UTC ' . $user->timezone->getUTCOffset() . ') ' . $user->timezone->location,
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
        $query = Timezone::find()->orderBy('offset, location');
        $pagination = new Pagination([
            'totalCount' => $query->count(),
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);
        $paginationButtons = PaginationButtons::build($pagination, function ($page) {
            return self::createRoute('list', [
                'page' => $page,
            ]);
        });
        $timezones = $query
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();
        $timezoneRows = array_map(function (Timezone $timezone) {
            return [
                [
                    'text' => '(UTC ' . $timezone->getUTCOffset() . ') ' . $timezone->location,
                    'callback_data' => self::createRoute('index', [
                        'timezoneId' => $timezone->id,
                    ]),
                ]
            ];
        }, $timezones);

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $text = $this->render('list'),
                array_merge($timezoneRows, [ $paginationButtons ], [
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
