<?php

namespace app\modules\bot\controllers\privates;

use app\models\Language;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\Chat;
use app\modules\bot\models\User;
use Yii;
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
    public function actionIndex()
    {
        return $this->actionSet();
    }

    public function actionSet($code = null, $page = 1, $chatId = null)
    {

        if ($code && $chatId) {
            $language = Language::findOne([
                'code' => $code,
            ]);

            if ($language) {
                $group = Chat::find()
                    ->where([
                        'or',
                        ['type' => 'supergroup'],
                        ['type' => 'group'],
                    ])
                    ->andWhere(['id' => $chatId])
                    ->one();

                $group->language_id = $language->id;

                if ($group->save()) {
                    Yii::$app->language = $language->code;
                }

                return $this->run('start/index');
            }
        }

        if ($this->getUpdate()->getMessage()) {
            if ($text = $this->getUpdate()->getMessage()->getText()) {
                if (strlen($text) <= 3) {
                    $language = Language::find()
                        ->orFilterWhere(['like', 'code', $text, false])
                        ->one();
                } else {
                    $language = Language::find()
                        ->orFilterWhere(['like', 'name', $text . '%', false])
                        ->orFilterWhere(['like', 'name_ascii', $text . '%', false])
                        ->one();
                }

                if ($language) {
                    $group = Chat::find()
                        ->where([
                            'or',
                            ['type' => 'supergroup'],
                            ['type' => 'group'],
                        ])
                        ->andWhere(['id' => $chatId])
                        ->one();

                    $group->language_id = $language->id;

                    if ($group->save()) {
                        Yii::$app->language = $language->code;
                    }

                    return $this->run('start/index');
                }
            }
        }

        $this->getState()->setInputRoute(self::createRoute('set'));

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

        $buttons = [];

        $languages = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        if ($languages) {
            foreach ($languages as $language) {
                $buttons[][] = [
                    'callback_data' => self::createRoute('set', [
                        'code' => $language->code,
                        'chatId' => $chatId,
                    ]),
                    'text' => strtoupper($language->code) . ' - ' . $language->name,
                ];
            }

            $paginationButtons = PaginationButtons::build($pagination, function ($page) use ($chatId) {
                return self::createRoute('set', [
                    'page' => $page,
                    'chatId' => $chatId,
                ]);
            });

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

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
            ]

        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set'),
                $buttons
            )
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
                // Установка языка для группы
                $group->language_id = $language->id;

                if ($group->save()) {
                    Yii::$app->language = $language->code;
                }
                return $this->run('start/index');
            }
        }
    }

}
