<?php

namespace app\modules\bot\controllers;

use app\models\Language;
use app\modules\bot\helpers\PaginationButtons;
use yii\data\Pagination;
use Yii;
use \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use \app\modules\bot\components\response\EditMessageTextCommand;
use \app\modules\bot\components\response\AnswerCallbackQueryCommand;
use \app\modules\bot\components\response\SendMessageCommand;

/**
 * Class My_languageController
 *
 * @package app\modules\bot\controllers
 */
class My_languageController extends Controller
{
    /**
     * @param null|string $language
     *
     * @return string
     */
    public function actionIndex($language = null)
    {
        $botClient = $this->getBotClient();
        $update = $this->getUpdate();

        $languageModel = null;
        if ($language) {
            $languageModel = Language::findOne(['code' => $language]);
            if ($languageModel) {
                if ($botClient) {
                    $botClient->language_code = $language;
                    if ($botClient->save()) {
                        Yii::$app->language = $languageModel->code;
                    }
                }
            }
        }

        $currentCode = Yii::$app->language;
        $currentName = $languageModel ? $languageModel->name : Language::findOne(['code' => $currentCode])->name;

        return [
            new SendMessageCommand(
                $update->getMessage()->getChat()->getId(),
                $this->render('index', compact('languageModel', 'currentCode', 'currentName')),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/language_list',
                                'text' => Yii::t('bot', 'Change Language')
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
     * @return string
     * @throws \TelegramBot\Api\InvalidArgumentException
     */
    public function actionLanguageList($page = 1)
    {
        $update = $this->getUpdate();

        $languageQuery = Language::find()->orderBy('code ASC');
        $countQuery = clone $languageQuery;
        $pagination = new Pagination([
            'totalCount' => $countQuery->count(),
            'params' => [
                'pageSize' => 20,
                'page' => $page,
            ],
        ]);

        $pagination->pageSizeParam = false;
        $pagination->validatePage = true;

        $languages = $languageQuery->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        return [
            new EditMessageTextCommand(
                $update->getCallbackQuery()->getMessage()->getChat()->getId(),
                $update->getCallbackQuery()->getMessage()->getMessageId(),
                $this->render('language-list', compact('languages', 'pagination')), [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => PaginationButtons::build('/language_list_<page>', $pagination),
                ]
            ),
            new AnswerCallbackQueryCommand(
                $update->getCallbackQuery()->getId()
            ),
        ];
    }
}
