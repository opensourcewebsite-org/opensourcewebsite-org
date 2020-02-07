<?php

namespace app\modules\bot\controllers;

use Yii;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\models\User;
use app\models\PasswordResetRequestForm;
use app\modules\bot\models\BotClient;

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
        $botClient = $this->module->botClient;
        if (isset($botClient->user_id))
        {
            $user = User::findOne($botClient->user_id);
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

        return [
            [
                'type' => 'message',
                'text' => $this->render('index', [
                            'email' => $email,
                            'isEmailConfirmed' => $isEmailConfirmed,
                        ]),
                'replyMarkup' => new InlineKeyboardMarkup([
                            [
                                [
                                    'callback_data' => '/change_email',
                                    'text' => Yii::t('bot', 'Change Email')
                                ]
                            ]
                        ])
            ]
        ];
    }

    public function actionCreate()
    {
        $botClient = $this->module->botClient;
        $email = $this->module->update->getMessage()->getText();

        $user = User::findOne($botClient->user_id);
        if (isset($user))
        {
            $user->email = $email;
            if ($user->save())
            {
                $resetRequest = true;
            }
        }
        else
        {
            $user = User::findOne(['email' => $email]);
            if (isset($user))
            {
                $error = Yii::t('bot', 'A user with the same email already exists');
            }
            else
            {
                $user = new User();
                $user->email = $email;
                $user->password = 123;
                $user->generateAuthKey();

                if ($user->save())
                {
                    $passwordResetRequest = new PasswordResetRequestForm();
                    $passwordResetRequest->email = $email;

                    if ($passwordResetRequest->sendEmail())
                    {
                        $botClient->user_id = $user->id;
                        $botClient->setState();
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
        }

        return [
            [
                'type' => 'message',
                'text' => $this->render('create', [
                            'resetRequest' => $resetRequest,
                            'error' => $error
                        ]),
            ]
        ];
    }

    public function actionUpdate()
    {
        $botClient = $this->module->botClient;
        $botClient->setState([
            'state' => '/set_email'
        ]);
        $botClient->save();
        return [
            [
                'type' => 'message',
                'text' => $this->render('update'),
            ]
        ];
    }
}
