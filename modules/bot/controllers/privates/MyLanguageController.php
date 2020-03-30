<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\models\Language;
use app\modules\bot\helpers\PaginationButtons;
use yii\data\Pagination;
use \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use \app\modules\bot\components\response\EditMessageTextCommand;
use \app\modules\bot\components\response\AnswerCallbackQueryCommand;
use \app\modules\bot\components\response\SendMessageCommand;
use app\modules\bot\components\Controller as Controller;

/**
 * Class MyLanguageController
 *
 * @package app\modules\bot\controllers
 */
class MyLanguageController extends Controller
{
    /**
     * @param null|string $language
     *
     * @return array
     */
    public function actionIndex($language = null)
    {
        $telegramUser = $this->getTelegramUser();
        $update = $this->getUpdate();

        $languageModel = null;
        if ($language) {
            $languageModel = Language::findOne(['code' => $language]);
            if ($languageModel) {
                if ($telegramUser) {
                    $telegramUser->language_code = $language;
                    if ($telegramUser->save()) {
                        Yii::$app->language = $languageModel->code;
                    }
                }
            }
        }

        $currentCode = Yii::$app->language;
        $currentName = $languageModel ? $languageModel->name : Language::findOne(['code' => $currentCode])->name;

        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('index', compact('languageModel', 'currentCode', 'currentName')),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => MenuController::createRoute(),
                                'text' => '🔙',
                            ],
                            [
                                'callback_data' => MyLanguageController::createRoute('list'),
                                'text' => '✏️',
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }

    /**
     * @param int $page
     *
     * @return array
     */
    public function actionList($page = 1)
    {
        $update = $this->getUpdate();

        $languageQuery = Language::find()->orderBy('code ASC');
        $pagination = new Pagination([
            'totalCount' => $languageQuery->count(),
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
        ]);

        $pagination->pageSizeParam = false;
        $pagination->validatePage = true;

        $languages = $languageQuery->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $paginationButtons = PaginationButtons::build($pagination, function ($page) {
            return self::createRoute('list', [
                'page' => $page,
            ]);
        });
        $buttons = [];

        if ($languages) {
            foreach ($languages as $language) {
                $buttons[][] = [
                    'callback_data' => self::createRoute('index', [
                        'language' => $language->code,
                    ]),
                    'text' => strtoupper($language->code) . ' - ' . $language->name
                ];
            }

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }

            $buttons[][] = [
                'callback_data' => self::createRoute(),
                'text' => '🔙'
            ];
        }

        return [
            new EditMessageTextCommand(
                $this->getTelegramChat()->chat_id,
                $update->getCallbackQuery()->getMessage()->getMessageId(),
                $this->render('list'),
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
