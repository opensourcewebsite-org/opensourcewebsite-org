<?php

namespace app\modules\bot\controllers\privates;

use app\models\Gender;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\components\response\ResponseBuilder;
use Yii;
use \app\models\User;
use app\modules\bot\components\Controller;
use yii\data\Pagination;

/**
 * Class MyGenderController
 *
 * @package app\modules\bot\controllers
 */
class MyGenderController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($genderId = null)
    {
        $user = $this->getUser();

        if (isset($genderId)) {
            $gender = Gender::findOne($genderId);
            if (isset($gender)) {
                $user->gender_id = $gender->id;
                $user->save();
            }
        }

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'gender' => isset($user->gender) ? $user->gender->name : null,
                ]),
                [
                    [
                        [
                            'callback_data' => MyProfileController::createRoute(),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => self::createRoute('update'),
                            'text' => Emoji::EDIT,
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionUpdate($page = 1)
    {
        $genderQuery = Gender::find();
        $pagination = new Pagination([

        ]);
        $paginationButtons = PaginationButtons::build($pagination, function ($page) {
            return self::createRoute('update', [
                'page' => $page,
            ]);
        });
        $genderRows = array_map(function ($gender) {
            return [
                [
                    'text' => $gender->name,
                    'callback_data' => self::createRoute('index', [
                        'genderId' => $gender->id,
                    ]),
                ],
            ];
        }, $genderQuery->all());

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $text = $this->render('update'),
                array_merge($genderRows, [ $paginationButtons ], [
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
