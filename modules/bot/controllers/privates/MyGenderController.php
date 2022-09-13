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
        if (!$this->globalUser->gender_id) {
            return $this->actionSet();
        }

        $this->getState()->setName(null);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'gender' => $this->globalUser->gender,
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
     * @param int|null $id Gender->id
     * @param int $page
     * @return array
     */
    public function actionSet($id = null, $page = 1)
    {
        if ($id) {
            $gender = Gender::findOne($id);

            if ($gender) {
                $this->globalUser->gender_id = $gender->id;
                $this->globalUser->save();

                return $this->actionIndex();
            }
        }

        $this->getState()->setName(null);

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

        $buttons = [];

        $genders = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        if ($genders) {
            foreach ($genders as $gender) {
                $buttons[][] = [
                    'callback_data' => self::createRoute('set', [
                        'id' => $gender->id,
                    ]),
                    'text' => Yii::t('bot', $gender->name),
                ];
            }

            $paginationButtons = PaginationButtons::build($pagination, function ($page) {
                return self::createRoute('set', [
                    'page' => $page,
                ]);
            });

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $buttons[] = [
            [
                'callback_data' => ($this->globalUser->gender_id ? self::createRoute() : MyProfileController::createRoute()),
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
