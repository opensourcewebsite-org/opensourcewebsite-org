<?php

namespace app\modules\bot\components\actions\privates\wordlist;

use app\modules\bot\components\actions\BaseAction;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatMemberPhrase;
use Yii;

class SelectAction extends BaseAction
{
    /**
    * @param int|null $id ChatPhrase->id
    * @param int|null $page
    * @return array
    */
    public function run($id = null, $page = 1)
    {
        $this->getState()->setName(null);

        $phrase = $this->wordModelClass::findOne($id);

        if (!isset($phrase)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chat = $phrase->chat;
        $chatMember = $chat->getChatMemberByUserId();

        if (!isset($chatMember)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chatMemberPhrase = ChatMemberPhrase::findOne([
            'member_id' => $chatMember->id,
            'phrase_id' => $phrase->id,
        ]);

        if (!$chatMemberPhrase) {
            $chatMemberPhrase = new ChatMemberPhrase();
            $chatMemberPhrase->member_id = $chatMember->id;
            $chatMemberPhrase->phrase_id = $phrase->id;
            $chatMemberPhrase->save();
        } else {
            $chatMemberPhrase->delete();
        }

        return $this->controller->run($this->listActionId, [
            'chatId' => $chat->id,
            'page' => $page,
        ]);
    }
}
