<?php

namespace app\modules\bot\controllers\privates;

use app\components\helpers\TimeHelper;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;
use Yii;
use yii\data\Pagination;

/**
 * Class GroupTimezoneController
 *
 * @package app\modules\bot\controllers\privates
 */
class GroupTimezoneController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($chatId = null)
    {
        return $this->actionList($chatId);
    }

    public function actionList($chatId = null, $page = 2)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(self::createRoute('input'));

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
        $timezones = array_slice($timezones, $pagination->offset, $pagination->limit, true);

        $paginationButtons = PaginationButtons::build($pagination, function ($page) use ($chatId) {
            return self::createRoute('list', [
                'chatId' => $chatId,
                'page' => $page,
            ]);
        });

        $buttons = [];

        foreach ($timezones as $timezone => $name) {
            $buttons[][] = [
                'text' => $name,
                'callback_data' => self::createRoute('select', [
                    'chatId' => $chatId,
                    'timezone' => $timezone,
                ]),
            ];
        }

        if ($paginationButtons) {
            $buttons[] = $paginationButtons;
        }

        $buttons[][] = [
            'callback_data' => GroupController::createRoute('view', [
                'chatId' => $chatId,
            ]),
            'text' => Emoji::BACK,
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('list'),
                $buttons
            )
            ->build();
    }

    public function actionSelect($chatId = null, $timezone = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if (!$timezone) {
            return $this->actionList($chatId);
        }

        $chat->timezone = $timezone;

        if ($chat->validate('timezone') && $chat->save()) {
            return $this->run('group/view', [
                'chatId' => $chatId,
            ]);
        }

        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }

    public function actionInput()
    {
        // TODO add text input to set timezone (Examples: 07, 06:30, -07, -06:30)
    }
}
