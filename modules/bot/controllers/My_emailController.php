<?php

namespace app\modules\bot\controllers;

use Yii;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\models\User;
use app\models\PasswordResetRequestForm;
use app\modules\bot\models\BotClient;
use \app\modules\bot\components\response\SendMessageCommandSender;
use \app\modules\bot\components\response\AnswerCallbackQueryCommandSender;
use \app\modules\bot\components\response\commands\SendMessageCommand;
use \app\modules\bot\components\response\commands\AnswerCallbackQueryCommand;

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
        }
        else
        {
            $botClient->setState([
                'state' => '/set_email'
            ]);
            $botClient->save();
        }

        $text = $this->render('index', [
            'email' => $email,
            'isEmailConfirmed' => $isEmailConfirmed,
        ]);

        return [
            new SendMessageCommandSender(
                new SendMessageCommand([
                    'chatId' => $update->getMessage()->getChat()->getId(),
                    'parseMode' => 'html',
                    'text' => $this->prepareText($text),
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/change_email',
                                'text' => Yii::t('bot', 'Change Email'),
                            ],
                        ],
                    ]),
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
            $error = Yii::t('bot', 'A user with the same email already exists');
        }
        else
        {
            $user->email = $email;

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
                $error = Yii::t('bot', 'Given email is invalid: ' . json_encode($user->getErrors()));
            }
        }

        $text = $this->render('create', [
            'resetRequest' => $resetRequest,
            'error' => $error
        ]);

        return [
            new SendMessageCommandSender(
                new SendMessageCommand([
                    'chatId' => $update->getMessage()->getChat()->getId(),
                    'parseMode' => 'html',
                    'text' => $this->prepareText($text),
                ])
            ),
        ];
    }

    public function actionUpdate()
    {
        $botClient = $this->getBotClient();
        $update = $this->getUpdate();

        $botClient->setState([
            'state' => '/set_email'
        ]);
        $botClient->save();

        $text = $this->render('update');

        return [
            new SendMessageCommandSender(
                new SendMessageCommand([
                    'chatId' => $update->getCallbackQuery()->getMessage()->getChat()->getId(),
                    'parseMode' => 'html',
                    'text' => $this->prepareText($text),
                ])
            ),
            new AnswerCallbackQueryCommandSender(
                new AnswerCallbackQueryCommand([
                    'callbackQueryId' => $update->getCallbackQuery()->getId(),
                ])
            ),
        ];
    }
}
