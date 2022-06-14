<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\models\Gender;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use app\models\User;
use yii\data\Pagination;

/**
 * Class MyGenderController
 *
 * @package app\modules\bot\controllers\privates
 */
class MyGenderController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($genderId = null)
    {
        $globalUser = $this->getUser();

        if (isset($genderId)) {
            $gender = Gender::findOne($genderId);

            if (isset($gender)) {
                $globalUser->gender_id = $gender->id;
                $globalUser->save();
            }
        }

        if (!$globalUser->gender_id) {
            return $this->actionSelect();
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'gender' => $globalUser->gender->name,
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
                            'callback_data' => self::createRoute('select'),
                            'text' => Emoji::EDIT,
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionSelect($page = 1)
    {
        $globalUser = $this->getUser();

        $query = Gender::find();

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
            return self::createRoute('update', [
                'page' => $page,
            ]);
        });

        $buttons = [];

        $genders = $query
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        if ($genders) {
            foreach ($genders as $gender) {
                $buttons[][] = [
                    'callback_data' => self::createRoute('index', [
                        'genderId' => $gender->id,
                    ]),
                    'text' => Yii::t('bot', $gender->name),
                ];
            }

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $buttons[] = [
            [
                'callback_data' => ($globalUser->gender_id ? self::createRoute() : MyProfileController::createRoute()),
                'text' => Emoji::BACK,
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('select'),
                $buttons
            )
            ->build();
    }
}
