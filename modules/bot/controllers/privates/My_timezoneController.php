<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use \app\modules\bot\components\response\EditMessageTextCommand;
use \app\modules\bot\components\response\AnswerCallbackQueryCommand;
use \app\modules\bot\components\response\SendMessageCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use \app\models\User;
use \app\models\Timezone;
use app\modules\bot\components\Controller as Controller;
use yii\data\Pagination;
use app\modules\bot\helpers\PaginationButtons;

/**
 * Class My_timezoneController
 *
 * @package app\modules\bot\controllers
 */
class My_timezoneController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $update = $this->getUpdate();
        $user = $this->getTelegramUser();

        $timezone = Timezone::findOne($user->timezone_code);
        $timezone_name = $timezone->getFullName();

        return [
            new EditMessageTextCommand(
                $this->getTelegramChat()->chat_id,
                $update->getCallbackQuery()->getMessage()->getMessageId(),
                $this->render('index', compact('timezone_name')),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/my_profile',
                                'text' => '🔙',
                            ],
                            [
                                'callback_data' => '/my_timezone__update',
                                'text' => '✏️',
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }

    public function actionUpdate($page = 1)
    {
        $update = $this->getUpdate();
        $user = $this->getUser();

        $timezoneQuery = Timezone::find();
        $buttons = [];

        $pagination = new Pagination([
            'totalCount' => $timezoneQuery->count(),
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
        ]);

        $pagination->pageSizeParam = false;
        $pagination->validatePage = true;

        $timezones = $timezoneQuery->orderBy('offset ASC')
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        foreach ($timezones as $timezone) {
            $buttons[][] = [
                'text' => $timezone->getFullName(),
                'callback_data' => '/my_timezone_create_' . $timezone->code
            ];
        }

        $paginationButtons = PaginationButtons::build('/my_timezone_update_', $pagination);

        $buttons[] = $paginationButtons;
        $buttons[][] = [
            'callback_data' => '/my_timezone',
            'text' => '🔙',
        ];

        return [
            new EditMessageTextCommand(
                $this->getTelegramChat()->chat_id,
                $update->getCallbackQuery()->getMessage()->getMessageId(),
                $text = $this->render('update'),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup($buttons),
                ]
            ),
            new AnswerCallbackQueryCommand(
                $update->getCallbackQuery()->getId()
            ),
        ];
    }

    public function actionCreate($timezoneCode = Timezone::TIMEZONE_UTC_CODE)
    {
        $telegramUser = $this->getTelegramUser();

        $telegramUser->timezone_code = $timezoneCode;
        $telegramUser->save();

        return $this->actionIndex();
    }
}
