<?php

namespace app\modules\bot\controllers\groups;

use app\helpers\Number;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\ChatTip;
use app\modules\bot\models\ChatTipQueue;
use app\modules\bot\models\User;
use Yii;

/**
 * Class TipQueueController
 *
 * @package app\modules\bot\controllers\groups
 */
class TipQueueController extends Controller
{
    /**
     * @param int $queueId ChatTipQueue->id
     *
     * @return array
     */
    public function actionTipMessage($queueId = null)
    {
        $chatTipQueue = ChatTipQueue::findOne($queueId);

        if (!isset($chatTipQueue)) {
            return [];
        }

        if ($chatTipQueue->message_id) {
            // edit message
            return $this->getResponseBuilder()
                ->editMessage(
                    $chatTipQueue->message_id,
                    $this->render('tip-message', [
                        'chatTipQueue' => $chatTipQueue,
                    ]),
                    [
                        [
                            [
                                'callback_data' => self::createRoute('take-tip', [
                                    'queueId' => $chatTipQueue->id,
                                ]),
                                'text' => Emoji::GIFT,
                            ],
                        ],
                    ],
                    [
                        'disablePreview' => true,
                        'disableNotification' => true,
                    ]
                )
                ->build();
        }

        // send message
        $response = $this->getResponseBuilder()
            ->sendMessage(
                $this->render('tip-message', [
                    'chatTipQueue' => $chatTipQueue,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('take-tip', [
                                'queueId' => $chatTipQueue->id,
                            ]),
                            'text' => Emoji::GIFT,
                        ],
                    ],
                ],
                [
                    'disablePreview' => true,
                    'disableNotification' => true,
                ]
            )
            ->send();

        if ($response) {
            // add message_id to $chatTipQueue record
            $chatTipQueue->message_id = $response->getMessageId();
            $chatTipQueue->save();

            return $response;
        }

        return [];
    }

    public function actionTakeTip($queueId = null)
    {
        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }
}
