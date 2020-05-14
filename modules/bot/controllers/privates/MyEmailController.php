<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\MessageText;

use Yii;
use app\models\User;
use app\models\MergeAccountsRequest;
use app\models\ChangeEmailRequest;
use app\modules\bot\components\Controller;

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

        $email = null;
        if (isset($user->email)) {
            $email = $user->email;
        } else {
            $this->getState()->setName(self::createRoute('create'));
        }

        return $this->getResponseBuilder()($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'email' => $email,
                ]),
                [
                    (isset($email) ? [
                        [
                            'callback_data' => self::createRoute('update'),
                            'text' => Emoji::EDIT,
                        ]
                    ] : []),
                    [
                        [
                            'callback_data' => MyProfileController::createRoute(),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionCreate()
    {
        $update = $this->getUpdate();
        $user = $this->getUser();

        $email = $update->getMessage()->getText();

        $changeRequest = false;
        $mergeRequest = false;
        $error = null;

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
                    $this->getState()->setName(null);

                    $changeRequest = true;
                }
            }
            if (!$changeRequest) {
                $error = Yii::t('bot', 'This email is invalid');
            }
        }

        return $this->getResponseBuilder()($this->getUpdate())
            ->sendMessage(
                $this->render('create', [
                    'changeRequest' => $changeRequest,
                    'mergeRequest' => $mergeRequest,
                    'error' => $error
                ]),
                (!$mergeRequest
                    ? []
                    : [
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
                    ])
            )
            ->build();
    }

    public function actionUpdate()
    {
        $user = $this->getUser();

        MergeAccountsRequest::deleteAll("user_id = {$user->id}");
        ChangeEmailRequest::deleteAll("user_id = {$user->id}");

        $this->getState()->setName(self::createRoute('create'));

        return $this->getResponseBuilder()($this->getUpdate())
            ->removeInlineKeyboardMarkup()
            ->sendMessage(
                $this->render('update')
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
                    return $this->getResponseBuilder()($this->getUpdate())
                        ->editMessageTextOrSendMessage(
                            $this->render('merge-accounts'),
                            [
                                [
                                    [
                                        'text' => Yii::t('bot', 'Discard Request'),
                                        'callback_data' => self::createRoute('discard-merge-request', [
                                            'mergeAccountsRequestId' => $mergeAccountsRequest->id,
                                        ]),
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
            return $this->getResponseBuilder()($this->getUpdate())
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
            $deleted = $mergeAccountsRequest->delete();
        }

        return $this->getResponseBuilder()($this->getUpdate())
            ->removeInlineKeyboardMarkup()
            ->answerCallbackQuery(
                new MessageText(Yii::t('bot', $deleted
                    ? 'Request was successfully discarded'
                    : 'Nothing to discard')),
                true
            )
            ->build();
    }
}
