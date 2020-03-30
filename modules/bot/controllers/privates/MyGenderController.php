<?php

namespace app\modules\bot\controllers\privates;

use app\models\Gender;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\response\ResponseBuilder;
use Yii;
use \app\models\User;
use app\modules\bot\components\Controller;

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
    public function actionIndex($gender = null)
    {
        $user = $this->getUser();

        if ($gender) {
            if ($gender == 'male') {
                $user->gender_id = Gender::findOne([ 'type' => User::MALE])->id;
            } elseif ($gender == 'female') {
                $user->gender_id = Gender::findOne([ 'type' => User::FEMALE])->id;
            }
            $user->save();
        }

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'gender' => isset($user->gender) ? $user->gender->type : null,
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

    public function actionUpdate()
    {
        $user = $this->getUser();

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $text = $this->render('update'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('index', [
                                'gender' => 'male',
                            ]),
                            'text' => Yii::t('bot', 'Male'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('index', [
                                'gender' => 'female',
                            ]),
                            'text' => Yii::t('bot', 'Female'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute(),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }
}
