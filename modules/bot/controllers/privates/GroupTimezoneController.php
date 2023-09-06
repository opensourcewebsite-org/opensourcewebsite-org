<?php

namespace app\modules\bot\controllers\privates;

use app\components\helpers\TimeHelper;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\filters\GroupActiveAdministratorAccessFilter;
use Yii;
use yii\data\Pagination;

/**
 * Class GroupTimezoneController
 *
 * @package app\modules\bot\controllers\privates
 */
class GroupTimezoneController extends Controller
{
    public function behaviors()
    {
        return [
            'groupActiveAdministratorAccess' => [
                'class' => GroupActiveAdministratorAccessFilter::class,
            ],
        ];
    }

    /**
     * @param int $id Chat->id
     * @return array
     */
    public function actionIndex($id = null)
    {
        return $this->actionList($id);
    }

    /**
     * @param int $id Chat->id
     * @return array
     */
    public function actionList($id = null, $page = 2)
    {
        $chat = Yii::$app->cache->get('chat');

        $this->getState()->setInputRoute(self::createRoute('input'));

        $timezones = TimeHelper::getTimezoneNames();

        $pagination = new Pagination([
            'totalCount' => count($timezones),
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $timezones = array_slice($timezones, $pagination->offset, $pagination->limit, true);

        $buttons = [];

        foreach ($timezones as $timezone => $name) {
            $buttons[][] = [
                'text' => $name,
                'callback_data' => self::createRoute('select', [
                    'id' => $chat->id,
                    'timezone' => $timezone,
                ]),
            ];
        }

        $paginationButtons = PaginationButtons::build($pagination, function ($page) use ($chat) {
            return self::createRoute('list', [
                'id' => $chat->id,
                'page' => $page,
            ]);
        });

        if ($paginationButtons) {
            $buttons[] = $paginationButtons;
        }

        $buttons[][] = [
            'callback_data' => GroupController::createRoute('view', [
                'chatId' => $chat->id,
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

    /**
     * @param int $id Chat->id
     * @return array
     */
    public function actionSelect($id = null, $timezone = null)
    {
        $chat = Yii::$app->cache->get('chat');

        if ($timezone !== null) {
            $chat->timezone = $timezone;

            if ($chat->validate('timezone') && $chat->save(false)) {
                return $this->run('group/view', [
                    'chatId' => $chat->id,
                ]);
            }
        }

        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }

    /**
     * @param int $id Chat->id
     * @return array
     */
    public function actionInput($id = null)
    {
        $chat = Yii::$app->cache->get('chat');

        // TODO add text input to set timezone (Examples: 07, 06:30, -07, -06:30)
    }
}
