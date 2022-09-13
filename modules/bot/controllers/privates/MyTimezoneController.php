<?php

namespace app\modules\bot\controllers\privates;

use app\components\helpers\TimeHelper;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use Yii;
use yii\data\Pagination;

/**
 * Class MyTimezoneController
 *
 * @package app\modules\bot\controllers\privates
 */
class MyTimezoneController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $this->getState()->setName(null);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'timezone' => TimeHelper::getNameByOffset($this->globalUser->timezone),
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
                    ],
                ]
            )
            ->build();
    }

    /**
     * @param string|null $timezone
     * @param int $page
     * @return array
     */
    public function actionSet($timezone = null, $page = 2)
    {
        if ($timezone) {
            $this->globalUser->timezone = $timezone;

            if ($this->globalUser->validate('timezone')) {
                $this->globalUser->save(false);

                return $this->actionIndex();
            }
        }

        // TODO add text input to set timezone (Examples: 07, 06:30, -07, -06:30)

        $this->getState()->setName(self::createRoute('set'));

        $timezones = TimeHelper::getTimezoneNames();

        $pagination = new Pagination([
            'totalCount' => count($timezones),
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $paginationButtons = PaginationButtons::build($pagination, function ($page) {
            return self::createRoute('set', [
                'page' => $page,
            ]);
        });

        $buttons = [];

        $timezones = array_slice($timezones, $pagination->offset, $pagination->limit, true);

        foreach ($timezones as $timezone => $name) {
            $buttons[][] = [
                'callback_data' => self::createRoute('set', [
                    'timezone' => $timezone,
                ]),
                'text' => $name,
            ];
        }

        if ($paginationButtons) {
            $buttons[] = $paginationButtons;
        }

        $buttons[] = [
            [
                'callback_data' => self::createRoute(),
                'text' => Emoji::BACK,
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set'),
                $buttons
            )
            ->build();
    }
}
