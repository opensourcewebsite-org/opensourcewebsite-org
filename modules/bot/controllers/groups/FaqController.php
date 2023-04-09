<?php

namespace app\modules\bot\controllers\groups;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\MessageText;
use app\modules\bot\models\ChatFaqQuestion;
use app\modules\bot\models\ChatPhrase;
use app\modules\bot\models\ChatSetting;
use Yii;

/**
 * Class FaqController
 *
 * @package app\modules\bot\controllers\groups
 */
class FaqController extends Controller
{
    public function actionIndex()
    {
        return [];
    }

    /**
     * Action shows answer
     *
     * @param int $questionId ChatFaqQuestion id
     *
     * @return array
     */
    public function actionShowAnswer($questionId = null)
    {
        $chat = $this->getTelegramChat();

        if ($chat->faq_status == ChatSetting::STATUS_ON) {
            $question = $chat->getQuestionPhrases()
                ->where([
                    'id' => $questionId,
                ])
                ->andWhere([
                    'not', ['answer' => null],
                ])
                ->one();

            if (isset($question)) {
                return $this->getResponseBuilder()
                    ->sendMessage(
                        $this->render('show-answer', [
                            'question' => $question,
                        ]),
                        [],
                        [
                            'disablePreview' => true,
                            'disableNotification' => true,
                            'replyToMessageId' => $this->getMessage()->getMessageId(),
                        ]
                    )
                    ->send();
            }
        }

        return [];
    }
}
