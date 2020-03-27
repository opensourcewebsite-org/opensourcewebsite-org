<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use \app\modules\bot\components\response\commands\EditMessageTextCommand;
use \app\modules\bot\components\response\commands\AnswerCallbackQueryCommand;
use \app\modules\bot\components\response\commands\SendMessageCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\modules\bot\components\helpers\PaginationButtons;
use yii\data\Pagination;
use app\modules\bot\components\Controller as Controller;
use app\components\helpers\TimeHelper;

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
    public function actionIndex($timezone = null)
    {
        $update = $this->getUpdate();
        $user = $this->getUser();
        $timezones = TimeHelper::timezonesList();

        if ($timezone) {
            if (array_key_exists($timezone, $timezones)) {
                $user->timezone = $timezone;
                $user->save();
            }
        }

        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('index', [
                    'timezone' => $timezones[$user->timezone],
                ]),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/my_profile',
                                'text' => '🔙',
                            ],
                            [
                                'callback_data' => '/my_timezone__list',
                                'text' => '✏️',
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }

    public function actionList($page = 22)
    {
        $update = $this->getUpdate();
        $user = $this->getUser();

        $timezones = TimeHelper::timezonesList();

        $pagination = new Pagination([
            'totalCount' => count($timezones),
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
        ]);

        $pagination->pageSizeParam = false;
        $pagination->validatePage = true;

        $timezones = array_slice($timezones, $pagination->offset, $pagination->limit);

        $paginationButtons = PaginationButtons::build('/my_timezone__list ', $pagination);
        $buttons = [];

        Yii::warning($buttons);

        if ($timezones) {
            foreach ($timezones as $timezone => $fullName) {
                $buttons[][] = [
                    'text' => $fullName,
                    'callback_data' => '/my_timezone ' . $timezone,
                ];
            }

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }

            $buttons[][] = [
                'callback_data' => '/my_timezone',
                'text' => '🔙',
            ];
        }

        Yii::warning($buttons);

        return [
            new EditMessageTextCommand(
                $this->getTelegramChat()->chat_id,
                $update->getCallbackQuery()->getMessage()->getMessageId(),
                $text = $this->render('list'),
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
}
