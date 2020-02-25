<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\models\User;
use app\models\MergeAccountsRequest;
use app\models\ChangeEmailRequest;
use \app\modules\bot\components\response\SendMessageCommand;
use \app\modules\bot\components\response\AnswerCallbackQueryCommand;
use \app\modules\bot\components\response\EditMessageTextCommand;
use \app\modules\bot\components\response\EditMessageReplyMarkupCommand;
use app\modules\bot\components\Controller;

/**
 * Class My_emailController
 *
 * @package app\modules\bot\controllers
 */
class My_emailController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $telegramUser = $this->getTelegramUser();
        $user = $this->getUser();
        $update = $this->getUpdate();

        $email = null;
        if (isset($user->email)) {
            $email = $user->email;
        } else {
            $this->getState()->setName('/set_email');
        }

        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('index', [
                    'email' => $email,
                ]),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        (isset($email) ? [
                            [
                                'callback_data' => '/change_email',
                                'text' => '✏️',
                            ]
                        ] : []),
                        [
                            [
                                'callback_data' => '/my_profile',
                                'text' => '🔙',
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }

    public function actionCreate()
    {
        $telegramUser = $this->getTelegramUser();
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

        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('create', [
                    'changeRequest' => $changeRequest,
                    'mergeRequest' => $mergeRequest,
                    'error' => $error
                ]),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => (!$mergeRequest
                        ? null
                        : new InlineKeyboardMarkup([
                            [
                                [
                                    'callback_data' => '/merge_accounts',
                                    'text' => Yii::t('bot', 'Yes'),
                                ],
                                [
                                    'callback_data' => '/change_email',
                                    'text' => Yii::t('bot', 'No'),
                                ]
                            ]
                        ])),
                ]
            ),
        ];
    }

    public function actionUpdate()
    {
        $telegramUser = $this->getTelegramUser();
        $update = $this->getUpdate();
        $user = $this->getUser();

        MergeAccountsRequest::deleteAll("user_id = {$user->id}");
        ChangeEmailRequest::deleteAll("user_id = {$user->id}");

        $this->getState()->setName('/set_email');

        return [
            new EditMessageReplyMarkupCommand(
                $this->getTelegramChat()->chat_id,
                $update->getCallbackQuery()->getMessage()->getMessageId()
            ),
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('update'),
                [
                    'parseMode' => $this->textFormat,
                ]
            ),
            new AnswerCallbackQueryCommand(
                $update->getCallbackQuery()->getId()
            ),
        ];
    }

    public function actionMergeAccounts()
    {
        $update = $this->getUpdate();
        $telegramUser = $this->getTelegramUser();
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
                    return [
                        new EditMessageTextCommand(
                            $this->getTelegramChat()->chat_id,
                            $update->getCallbackQuery()->getMessage()->getMessageId(),
                            $this->render('merge-accounts'),
                            [
                                'parseMode' => $this->textFormat,
                                'replyMarkup' => new InlineKeyboardMarkup([
                                    [
                                        [
                                            'text' => Yii::t('bot', 'Discard Request'),
                                            'callback_data' => '/discard_merge_request ' . $mergeAccountsRequest->id,
                                        ],
                                    ],
                                ]),
                            ]
                        ),
                        new AnswerCallbackQueryCommand(
                            $update->getCallbackQuery()->getId()
                        ),
                    ];
                } else {
                }
            } else {
            }
        } else {
            return [
                new AnswerCallbackQueryCommand(
                    $update->getCallbackQuery()->getId(),
                    [
                        'showAlert' => true,
                        'text' => Yii::t('bot', 'This request has expired'),
                    ]
                ),
            ];
        }
    }

    public function actionDiscardMergeRequest($mergeAccountsRequestId)
    {
        $update = $this->getUpdate();

        $mergeAccountsRequest = MergeAccountsRequest::findOne($mergeAccountsRequestId);
        if (isset($mergeAccountsRequest)) {
            $deleted = $mergeAccountsRequest->delete();
        }
        return [
            new EditMessageReplyMarkupCommand(
                $this->getTelegramChat()->chat_id,
                $update->getCallbackQuery()->getMessage()->getMessageId()
            ),
            new AnswerCallbackQueryCommand(
                $update->getCallbackQuery()->getId(),
                [
                    'text' => Yii::t('bot', $deleted
                        ? 'Request was successfully discarded'
                        : 'Nothing to discard'),
                    'showAlert' => true,
                ]
            ),
        ];
    }
}
