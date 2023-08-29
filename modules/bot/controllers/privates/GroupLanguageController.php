<?php

namespace app\modules\bot\controllers\privates;

use app\models\Language;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\Chat;
use app\modules\bot\models\User;
use Yii;
use yii\base\BaseObject;
use yii\data\Pagination;

/**
 * Class GroupLanguageController
 * @package app\modules\bot\controllers\privates
 */
class GroupLanguageController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($chatId = null)
    {
        return $this->actionList($chatId);
    }

    /**
     * @param null $chatId
     * @param int $page
     *
     * @return array|false|void
     */
    public function actionList($chatId = null, $page = 1)
    {

        $chat = Chat::findOne($chatId);

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setInputRoute(self::createRoute('input', [
            'chatId' => $chatId,
        ]));

        $query = Language::find()
            ->orderBy([
                'code' => SORT_ASC,
            ]);

        $pagination = new Pagination([
            'totalCount' => $query->count(),
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $languages = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $buttons = [];

        if ($languages) {
            foreach ($languages as $language) {
                $buttons[][] = [
                    'callback_data' => self::createRoute('select', [
                        'code' => $language->code,
                        'chatId' => $chatId,
                    ]),
                    'text' => strtoupper($language->code) . ' - ' . $language->name,
                ];
            }

            $paginationButtons = PaginationButtons::build($pagination, function ($page) use ($chatId) {
                return self::createRoute('list', [
                    'page' => $page,
                    'chatId' => $chatId,
                ]);
            });

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $telegramUser = $this->getUpdate()->getFrom();
        $userLanguageCode = $telegramUser->getLanguageCode();
        $userLanguage = Language::findOne(['code' => $userLanguageCode]);
        $shouldShowResetButton = $userLanguage && $userLanguage->id !== $chat->language_id && $chat->language_id !== null;

        $buttons[] = [
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => GroupLanguageController::createRoute('current', [
                    'chatId' => $chatId,
                ]),
                'text' => Emoji::DELETE . " " . Yii::t('bot', 'Reset'),
                'visible' => $shouldShowResetButton,
            ]

        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('list'),
                $buttons
            )
            ->build();
    }

    /**
     * @param null $chatId
     * @param null $code
     *
     * @return mixed|void|null
     */
    public function actionSelect($chatId = null, $code = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if (!$code) {
            return $this->actionList();
        }

        $language = Language::findOne([
            'code' => $code,
        ]);

        if (!$language) {
            return $this->actionList();
        }

        $chat->language_id = $language->id;

        if ($chat->validate('language_id') && $chat->save(false)) {
            return $this->run('group/view', [
                'chatId' => $chatId,
            ]);
        }

        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }

    /**
     * @param null $chatId
     *
     * @return mixed|void|null
     */
    public function actionInput($chatId = null)
    {
        if ($text = $this->getUpdate()->getMessage()->getText()) {
            if (strlen($text) <= 3) {
                $language = Language::find()
                    ->orFilterWhere(['like', 'code', $text, false])
                    ->one();
            } else {
                $language = Language::find()
                    ->orFilterWhere(['like', 'name', $text . '%', false])
                    ->one();
            }

            if (isset($language)) {
                return $this->actionSelect($chatId, $language->code);
            }
        }

        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }
    /**
     * Get the current language code of the Telegram user.
     * @return string|null
     */
    public function actionCurrent($chatId)
    {
        $group = Chat::find()
            ->where([
                'or',
                ['type' => 'supergroup'],
                ['type' => 'group'],
            ])
            ->andWhere(['id' => $chatId])
            ->one();

        if ($group) {
            $telegramUser = $this->getUpdate()->getFrom();
            $userLanguageCode = $telegramUser->getLanguageCode();
            $language = Language::findOne(['code' => $userLanguageCode]);

            if ($language) {
                $group->language_id = $language->id;

                if ($group->save()) {
                    Yii::$app->language = $language->code;
                }
                return $this->run('start/index');
            }
        }
    }

}
