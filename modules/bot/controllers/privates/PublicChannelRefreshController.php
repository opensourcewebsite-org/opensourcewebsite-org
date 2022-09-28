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
 * Class PublicChannelpRefreshController
 *
 * @package app\modules\bot\controllers\privates
 */
class PublicChannelRefreshController extends Controller
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

        if (!isset($chat) || !$chat->isChannel() || !$chat->username) {
            return $this->run('public-channel/index');
        }

        try {
            $botApiChat = $this->getBotApi()->getChat($chat->getChatId());
        } catch (\Exception $e) {
            Yii::warning($e);

            if (in_array($e->getCode(), [400, 403])) {
                // Chat has been removed in Telegram => remove chat from db
                $chat->delete();

                return $this->run('public-channel/index', [
                    'page' => $page,
                ]);
            }

            throw $e;
        }

        if (!$this->getBotApi()->getChatMember($chat->getChatId(), explode(':', $this->getBot()->token)[0])->isActualChatMember()) {
            // Bot is not the chat member => remove chat from db
            $chat->delete();

            return $this->run('public-channel/index', [
                'page' => $page,
            ]);
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

        return $this->run('public-channel/index', [
            'page' => $page,
        ]);
    }
}
