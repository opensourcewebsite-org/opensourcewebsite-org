<?php

namespace app\modules\bot\controllers\privates;

use app\models\Gender;
use app\models\User;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use Yii;
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
    public function actionIndex()
    {
        $this->getState()->setName(null);

        $globalUser = $this->getUser();

        if (!$globalUser->gender_id) {
            return $this->actionList();
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
                            'callback_data' => self::createRoute('list'),
                            'text' => Emoji::EDIT,
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionList($page = 1)
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
                    'callback_data' => self::createRoute('select', [
                        'id' => $gender->id,
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
                $this->render('list'),
                $buttons
            )
            ->build();
    }

    public function actionSelect($id = null)
    {
        $globalUser = $this->getUser();

        if (!$id) {
            return $this->actionList();
        }

        $gender = Gender::findOne($id);

        if ($gender) {
            $globalUser->gender_id = $gender->id;
            $globalUser->save();
        }

        return $this->actionIndex();
    }
}
