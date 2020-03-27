<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\helpers\Emoji;
use Yii;
use app\modules\bot\components\response\commands\SendMessageCommand;
use app\modules\bot\components\response\commands\AnswerCallbackQueryCommand;
use app\modules\bot\components\response\commands\EditMessageReplyMarkupCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\models\User;
use app\modules\bot\components\Controller;

/**
 * Class MyBirthdayController
 *
 * @package app\modules\bot\controllers
 */
class MyBirthdayController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $user = $this->getUser();

        $birthday = $user->birthday;

        if (isset($birthday)) {
            try {
                $birthday = (new \DateTime($birthday))->format(User::DATE_FORMAT);
            } catch (\Exception $e) {
            }
        }

        if (!isset($birthday)) {
            $this->getState()->setName(self::createRoute('create'));
        }

        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('index', compact('birthday')),
                [
                    'replyMarkup' => new InlineKeyboardMarkup([
                        (isset($birthday) ?
                            [
                                [
                                    'callback_data' => self::createRoute('update'),
                                    'text' => Emoji::EDIT,
                                ]
                            ]
                            : null),
                        [
                            [
                                'callback_data' => MyProfileController::createRoute(),
                                'text' => Emoji::BACK,
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }

    public function actionCreate()
    {
        $update = $this->getUpdate();
        $user = $this->getUser();

        $text = $update->getMessage()->getText();
        if ($this->validateDate($text, User::DATE_FORMAT)) {
            $user->birthday = Yii::$app->formatter->format($text, 'date');
            $user->save();
            $this->getState()->setName(null);
            return $this->actionIndex();
        }

        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('update'),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => self::createRoute(),
                                'text' => Emoji::BACK,
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }

    public function actionUpdate()
    {
        $update = $this->getUpdate();

        $this->getState()->setName(self::createRoute('create'));

        return [
            new EditMessageReplyMarkupCommand(
                $this->getTelegramChat()->chat_id,
                $update->getCallbackQuery()->getMessage()->getMessageId()
            ),
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('update'),
                [
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => self::createRoute(),
                                'text' => Emoji::BACK,
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

    private function validateDate($date, $format)
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}
