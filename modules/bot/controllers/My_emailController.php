<?php

namespace app\modules\bot\controllers;

use Yii;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\models\User;
use app\models\MergeAccountsRequest;
use app\models\PasswordResetRequestForm;
use app\modules\bot\models\BotClient;
use \app\modules\bot\components\response\SendMessageCommand;
use \app\modules\bot\components\response\AnswerCallbackQueryCommand;
use \app\modules\bot\components\response\EditMessageTextCommand;

/**
 * Class My_emailController
 *
 * @package app\modules\bot\controllers
 */
class My_emailController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        $botClient = $this->getBotClient();
        $user = $this->getUser();
        $update = $this->getUpdate();

        if (isset($user->email))
        {
            $email = $user->email;
            
            $tokenLifeTime = Yii::$app->params['user.passwordResetTokenExpire'];
            $mergeAccountsRequest = MergeAccountsRequest::findOne(['user_to_merge_id' => $user->id]);
            if (isset($mergeAccountsRequest))
            {
                $mergeAccountsRequestId = $mergeAccountsRequest->id;
            }
        }
        else
        {
            $botClient->state->setName('/set_email');
            $botClient->save();
        }

        return [
            new SendMessageCommand(
                $update->getMessage()->getChat()->getId(),
                $this->render('index', [
                    'email' => $email,
                    'hasMergeAccountsRequest' => isset($mergeAccountsRequestId),
                ]),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => (!isset($email)
                        ? NULL
                        : new InlineKeyboardMarkup([
                            [
                                [
                                    'callback_data' => '/change_email',
                                    'text' => $this->render('change-email', [
                                        'hasMergeAccountsRequest' => isset($mergeAccountsRequestId),
                                    ]),
                                ],
                            ],
                        ])),
                ]
            ),
        ];
    }

    public function actionCreate()
    {
        $botClient = $this->getBotClient();
        $update = $this->getUpdate();
        $user = $this->getUser();

        $email = $update->getMessage()->getText();

        $userWithSameEmail = User::findOne(['email' => $email]);
        if (isset($userWithSameEmail))
        {
            if ($userWithSameEmail->id != $user->id)
            {
                $mergeRequest = true;
                $botClient->state->setName('waiting_for_merge');
                $botClient->state->email = $email;
                $botClient->save();
            }
            else
            {

            }
        }
        else
        {
            $user->email = $email;
            $user->is_email_confirmed = false;

            if ($user->save())
            {
                $passwordResetRequest = new PasswordResetRequestForm();
                $passwordResetRequest->email = $email;

                if ($passwordResetRequest->sendEmail())
                {
                    $botClient->user_id = $user->id;
                    $botClient->state->setName(NULL);
                    $botClient->save();

                    $resetRequest = true;

                }
                else
                {
                    $error = Yii::t('bot', '');
                }
            }
            else
            {
                $error = Yii::t('bot', 'Given email is invalid');
            }
        }

        return [
            new SendMessageCommand(
                $update->getMessage()->getChat()->getId(),
                $this->render('create', [
                    'resetRequest' => $resetRequest,
                    'mergeRequest' => $mergeRequest,
                    'error' => $error
                ]),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => (!$mergeRequest
                        ? NULL
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
        $botClient = $this->getBotClient();
        $update = $this->getUpdate();
        $user = $this->getUser();

        MergeAccountsRequest::deleteAll('user_id = ' . $user->id);

        $botClient->state->setName('/set_email');
        $botClient->save();

        return [
            new EditMessageTextCommand(
                $update->getCallbackQuery()->getMessage()->getChat()->getId(),
                $update->getCallbackQuery()->getMessage()->getMessageId(),
                $update->getCallbackQuery()->getMessage()->getText(),
                [
                    'parseMode' => $this->textFormat,
                ]
            ),
            new SendMessageCommand(
                $update->getCallbackQuery()->getMessage()->getChat()->getId(),
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
        $botClient = $this->getBotClient();
        $user = $this->getUser();
        $state = $botClient->state;
        $stateName = $state->getName();

        if ($stateName == 'waiting_for_merge')
        {
            $userToMerge = User::findOne(['email' => $state->email]);
            if ($userToMerge)
            {
                $mergeAccountsRequest = new MergeAccountsRequest();
                $mergeAccountsRequest->setAttributes([
                    'user_to_merge_id' => $user->id,
                    'user_id' => $userToMerge->id,
                    'token' => Yii::$app->security->generateRandomString(),
                ]);
                if ($mergeAccountsRequest->sendEmail())
                {
                    return [
                        new EditMessageTextCommand(
                            $update->getCallbackQuery()->getMessage()->getChat()->getId(),
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
                }
                else
                {

                }
            }
            else
            {

            }
        }
        else
        {
            return [
                new AnswerCallbackQueryCommand(
                    $update->getCallbackQuery()->getId(),
                    [
                        'showAlert' => TRUE,
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
        if (isset($mergeAccountsRequest))
        {
            $deleted = $mergeAccountsRequest->delete();
        }
        return [
            new EditMessageTextCommand(
                $update->getCallbackQuery()->getMessage()->getChat()->getId(),
                $update->getCallbackQuery()->getMessage()->getMessageId(),
                $update->getCallbackQuery()->getMessage()->getText(),
                [
                    'parseMode' => $this->textFormat,
                ]
            ),
            new AnswerCallbackQueryCommand(
                $update->getCallbackQuery()->getId(),
                [
                    'text' => Yii::t('bot', $deleted
                        ? 'Request was successfully discarded'
                        : 'Nothing to discard'),
                    'showAlert' => TRUE,
                ]
            ),
        ];
    }
}
