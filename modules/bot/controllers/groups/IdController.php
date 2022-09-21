<?php

namespace app\modules\bot\controllers\groups;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\models\User;
use Yii;

/**
 * Class IdController
 *
 * @package app\modules\bot\controllers\groups
 */
class IdController extends Controller
{
    /**
     * @param string|null $message
     * @param int|null $id
     * @return array
     */
    public function actionIndex($message = null, $id = null)
    {
        if (!$id && $message) {
            if ((int)$message[0] > 0) {
                if (preg_match('/(?:^(?:[0-9]+))/i', $message, $matches)) {
                    $id = $matches[0];
                }
            } else {
                if ($message[0] == '@') {
                    if (preg_match('/(?:^@(?:[A-Za-z0-9][_]{0,1})*[A-Za-z0-9]+)/i', $message, $matches)) {
                        $id = ltrim($matches[0], '@');
                    }
                } else {
                    if (preg_match('/(?:(?:[A-Za-z0-9][_]{0,1})*[A-Za-z0-9]+)/i', $message, $matches)) {
                        $id = $matches[0];
                    }
                }
            }
        }

        if (!$id) {
            return [];
        }

        $viewUser = User::find()
            ->andWhere([
                'or',
                ['provider_user_name' => $id],
                ['provider_user_id' => $id],
            ])
            ->human()
            ->one();

        if (!$viewUser) {
            return [];
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'user' => $viewUser,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('index', [
                                'id' => $id,
                            ]),
                            'text' => Emoji::REFRESH,
                        ],
                    ],
                ],
                [
                    'disablePreview' => true,
                    'disableNotification' => true,
                    'replyToMessageId' => $this->getMessage()->getMessageId(),
                ]
            )
            ->build();
    }
}
