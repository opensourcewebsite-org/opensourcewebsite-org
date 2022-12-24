<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\User;
use yii\helpers\ArrayHelper;

/**
 * Class PublicGroupRefreshController
 *
 * @package app\modules\bot\controllers\privates
 */
class PublicGroupRefreshController extends Controller
{
    /**
     * @param string|int|null $chatId
     * @param int $page
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionIndex($chatId = null, $page = 1)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat) || !$chat->isGroup() || !$chat->username) {
            return $this->run('public-group/index');
        }

        try {
            $botApiChat = $this->getBotApi()->getChat($chat->getChatId());
        } catch (\Exception $e) {
            Yii::warning($e);

            if (in_array($e->getCode(), [400, 403])) {
                // Chat has been removed in Telegram => remove chat from db
                // TODO add confirm for owner
                //$chat->delete();

                return $this->run('public-group/index', [
                    'page' => $page,
                ]);
            }

            throw $e;
        }
        // Update chat information
        $chat->setAttributes([
            'type' => $botApiChat->getType(),
            'title' => $botApiChat->getTitle(),
            'username' => $botApiChat->getUsername(),
            'description' => $botApiChat->getDescription(),
        ]);

        if (!$chat->save()) {
            Yii::warning($chat->getErrors());

            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        return $this->run('public-group/index', [
            'page' => $page,
        ]);
    }
}
