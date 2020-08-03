<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\MessageText;
use app\models\User;
use app\models\MergeAccountsRequest;
use app\models\ChangeEmailRequest;

/**
 * Class MyEmailController
 *
 * @package app\modules\bot\controllers
 */
class MyEmailController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $user = $this->getUser();

        if (isset($user->email)) {
            $email = $user->email;
        } else {
            return $this->actionUpdate();
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'email' => $email,
                ]),
                [
                    [
                        [
                            'callback_data' => MyProfileController::createRoute(),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'text' => Emoji::MENU,
                            'callback_data' => MenuController::createRoute(),
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

    public function actionCreate()
    {
        $email = $this->getUpdate()->getMessage()->getText();
        $user = $this->getUser();

        $changeRequest = false;
        $mergeRequest = false;

        $userWithSameEmail = User::findOne(['email' => $email]);
        if (isset($userWithSameEmail)) {
            if ($userWithSameEmail->id != $user->id) {
                $mergeRequest = true;
                $this->getState()->setName('waiting_for_merge');
                $this->getState()->setIntermediateField('email', $email);
            } else {
                $this->getState()->setName(null);
                return $this->actionIndex();
            }
        } else {
            $changeEmailRequest = new ChangeEmailRequest();
            $changeEmailRequest->setAttributes([
                'email' => $email,
                'user_id' => $user->id,
                'token' => Yii::$app->security->generateRandomString(),
            ]);

            if ($changeEmailRequest->save()) {
                if ($changeEmailRequest->sendEmail()) {
                    $changeRequest = true;
                    $this->getState()->setName(null);
                }
            }
        }

        if ($changeRequest) {
            return $this->getResponseBuilder()
                ->editMessageTextOrSendMessage(
                    $this->render('create'),
                    [
                        [
                            [
                                'callback_data' => MyProfileController::createRoute(),
                                'text' => Emoji::BACK,
                            ],
                        ],
                    ]
                )
                ->build();
        } elseif ($mergeRequest) {
            return $this->getResponseBuilder()
                ->editMessageTextOrSendMessage(
                    $this->render('merge-request'),
                    [
                        [
                            [
                                'callback_data' => self::createRoute('merge-accounts'),
                                'text' => Yii::t('bot', 'Yes'),
                            ],
                            [
                                'callback_data' => self::createRoute('update'),
                                'text' => Yii::t('bot', 'No'),
                            ]
                        ]
                    ]
                )
                ->build();
        }
    }

    public function actionUpdate()
    {
        $this->getState()->setName(self::createRoute('create'));
        $user = $this->getUser();

        MergeAccountsRequest::deleteAll("user_id = {$user->id}");
        ChangeEmailRequest::deleteAll("user_id = {$user->id}");

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('update'),
                [
                    [
                        [
                            'callback_data' => ($user->email ? self::createRoute() : MyProfileController::createRoute()),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionMergeAccounts()
    {
        $update = $this->getUpdate();
        $user = $this->getUser();
        $state = $this->getState();
        $stateName = $state->getName();

        if ($stateName == 'waiting_for_merge') {
            $userToMerge = User::findOne(['email' => $state->getIntermediateField('email', null)]);
            if ($userToMerge) {
                $mergeAccountsRequest = new MergeAccountsRequest();
                $mergeAccountsRequest->setAttributes([
                    'user_to_merge_id' => $user->id,
                    'user_id' => $userToMerge->id,
                    'token' => Yii::$app->security->generateRandomString(),
                ]);
                // MergeAccountsRequest::sendEmail also call ActiveRecord::save method
                if ($mergeAccountsRequest->sendEmail()) {
                    return $this->getResponseBuilder()
                        ->editMessageTextOrSendMessage(
                            $this->render('merge-accounts'),
                            [
                                [
                                    [
                                        'text' => Yii::t('bot', 'Cancel request'),
                                        'callback_data' => self::createRoute('discard-merge-request', [
                                            'mergeAccountsRequestId' => $mergeAccountsRequest->id,
                                        ]),
                                    ],
                                ],
                                [
                                    [
                                        'callback_data' => MyProfileController::createRoute(),
                                        'text' => Emoji::BACK,
                                    ],
                                ],
                            ]
                        )
                        ->build();
                } else {
                }
            } else {
            }
        } else {
            return $this->getResponseBuilder()
                ->answerCallbackQuery(
                    new MessageText(Yii::t('bot', 'This request has expired')),
                    true
                )
                ->build();
        }
    }

    public function actionDiscardMergeRequest($mergeAccountsRequestId)
    {
        $update = $this->getUpdate();

        $mergeAccountsRequest = MergeAccountsRequest::findOne($mergeAccountsRequestId);
        if (isset($mergeAccountsRequest)) {
            $mergeAccountsRequest->delete();

            return $this->actionUpdate();
        }
    }
}
