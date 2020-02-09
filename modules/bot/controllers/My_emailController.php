<?php

namespace app\modules\bot\controllers;

use Yii;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\models\User;
use app\models\MergeAccountsRequest;
use app\models\PasswordResetRequestForm;
use app\modules\bot\models\BotClient;
use \app\modules\bot\components\response\SendMessageCommandSender;
use \app\modules\bot\components\response\EditMessageTextCommandSender;
use \app\modules\bot\components\response\AnswerCallbackQueryCommandSender;
use \app\modules\bot\components\response\commands\SendMessageCommand;
use \app\modules\bot\components\response\commands\AnswerCallbackQueryCommand;
use \app\modules\bot\components\response\commands\EditMessageTextCommand;

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
            
            $isEmailConfirmed = $user->is_email_confirmed;

            $tokenLifeTime = Yii::$app->params['user.passwordResetTokenExpire'];
            $mergeAccountsRequest = MergeAccountsRequest::findOne(['user_to_merge_id' => $user->id]);
            var_dump($mergeAccountsRequest);
            if (isset($mergeAccountsRequest))
            {
                $mergeAccountsRequestId = $mergeAccountsRequest->id;
            }
        }
        else
        {
            $botClient->setState([
                'state' => '/set_email'
            ]);
            $botClient->save();
        }

        return [
            new SendMessageCommandSender(
                new SendMessageCommand([
                    'chatId' => $update->getMessage()->getChat()->getId(),
                    'parseMode' => $this->textFormat,
                    'text' => $this->render('index', [
                        'email' => $email,
                        'isEmailConfirmed' => $isEmailConfirmed,
                        'hasMergeAccountsRequest' => isset($mergeAccountsRequestId),
                    ]),
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
                ])
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
                $botClient->setState([
                    'state' => 'waiting_for_merge',
                    'email' => $email,
                ]);
                $botClient->save();
            }
            else
            {

            }
        }
        else
        {
            if (!isset($user))
            {
                $user = User::createWithRandomPassword();
            }

            $user->email = $email;
            $user->is_email_confirmed = false;

            if ($user->save())
            {
                $passwordResetRequest = new PasswordResetRequestForm();
                $passwordResetRequest->email = $email;

                if ($passwordResetRequest->sendEmail())
                {
                    $botClient->user_id = $user->id;
                    $botClient->resetState();
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
            new SendMessageCommandSender(
                new SendMessageCommand([
                    'chatId' => $update->getMessage()->getChat()->getId(),
                    'parseMode' => $this->textFormat,
                    'text' => $this->render('create', [
                        'resetRequest' => $resetRequest,
                        'mergeRequest' => $mergeRequest,
                        'error' => $error
                    ]),
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
                ])
            ),
        ];
    }

    public function actionUpdate()
    {
        $botClient = $this->getBotClient();
        $update = $this->getUpdate();
        $user = $this->getUser();

        MergeAccountsRequest::deleteAll('user_id = ' . $user->id);

        $botClient->setState([
            'state' => '/set_email'
        ]);
        $botClient->save();

        return [
            new EditMessageTextCommandSender(
                new EditMessageTextCommand([
                    'chatId' => $update->getCallbackQuery()->getMessage()->getChat()->getId(),
                    'messageId' => $update->getCallbackQuery()->getMessage()->getMessageId(),
                    'parseMode' => $this->textFormat,
                    'text' => $update->getCallbackQuery()->getMessage()->getText(),
                ])
            ),
            new SendMessageCommandSender(
                new SendMessageCommand([
                    'chatId' => $update->getCallbackQuery()->getMessage()->getChat()->getId(),
                    'parseMode' => $this->textFormat,
                    'text' => $this->render('update'),
                ])
            ),
            new AnswerCallbackQueryCommandSender(
                new AnswerCallbackQueryCommand([
                    'callbackQueryId' => $update->getCallbackQuery()->getId(),
                ])
            ),
        ];
    }

    public function actionMergeAccounts()
    {
        $update = $this->getUpdate();
        $botClient = $this->getBotClient();
        $user = $this->getUser();
        $state = $botClient->getState();
        $stateName = $state->state;

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
                        new EditMessageTextCommandSender(
                            new EditMessageTextCommand([
                                'chatId' => $update->getCallbackQuery()->getMessage()->getChat()->getId(),
                                'messageId' => $update->getCallbackQuery()->getMessage()->getMessageId(),
                                'parseMode' => $this->textFormat,
                                'text' => $this->render('merge-accounts'),
                                'replyMarkup' => new InlineKeyboardMarkup([
                                    [
                                        [
                                            'text' => Yii::t('bot', 'Discard Request'),
                                            'callback_data' => '/discard_merge_request ' . $mergeAccountsRequest->id,
                                        ],
                                    ],
                                ]),
                            ])
                        ),
                        new AnswerCallbackQueryCommandSender(
                            new AnswerCallbackQueryCommand([
                                'callbackQueryId' => $update->getCallbackQuery()->getId(),
                            ])
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
                new AnswerCallbackQueryCommandSender(
                    new AnswerCallbackQueryCommand([
                        'callbackQueryId' => $update->getCallbackQuery()->getId(),
                        'showAlert' => true,
                        'text' => Yii::t('bot', 'This request has expired'),
                    ])
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
            new EditMessageTextCommandSender(
                new EditMessageTextCommand([
                    'chatId' => $update->getCallbackQuery()->getMessage()->getChat()->getId(),
                    'messageId' => $update->getCallbackQuery()->getMessage()->getMessageId(),
                    'parseMode' => $this->textFormat,
                    'text' => $update->getCallbackQuery()->getMessage()->getText(),
                ])
            ),
            new AnswerCallbackQueryCommandSender(
                new AnswerCallbackQueryCommand([
                    'callbackQueryId' => $update->getCallbackQuery()->getId(),
                    'text' => Yii::t('bot', $deleted
                        ? 'Request was successfully discarded'
                        : 'Nothing to discard'),
                    'showAlert' => TRUE,
                ])
            ),
        ];
    }
}
