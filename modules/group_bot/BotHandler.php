<?php

namespace app\modules\group_bot;

use app\models\Groupuser;
use app\models\GroupChat;
use app\models\GroupGoword;
use app\models\GroupStopword;
use app\modules\group_bot\models\CallbackQuery;
use app\modules\group_bot\models\InputMessage;
use app\modules\group_bot\telegram\TG;

class BotHandler {
	private $output;

	const BACK = "◀️ Назад";
	const MAIN = "⏪ В главное меню";

	const MODE_GO = 0;
	const MODE_STOP = 1;

	const CHAT_PRIVATE = "private";

	const ENABLE_COMMAND = "/enable@group_ro_bot";
	const DISABLE_COMMAND = "/disable@group_ro_bot";

	const ME = 295605654;

	function __construct($output) {
		$this->output = $output;
	}

	function handleInputMessage() {
		$input_message = new InputMessage($this->output);

		$id = $input_message->get_chat_id();
		$mid = $input_message->get_message_id();
		$text = $input_message->get_text();
		$type = $input_message->get_chat_type();

		if ($text == '/start') {
			$this->start($id, $text);
			return;
		}

		if ($type != self::CHAT_PRIVATE) {

			if ($text == self::ENABLE_COMMAND) {

				if (Groupchat::find(['tg_id' => $id])->exists()) {
					$groupchat = Groupchat::find()->where(['tg_id' => $id])->one();
					$groupchat->setEnabled(TRUE);

					$groupchat->save();
				} else {

					$owner_id = $input_message->get_from_id();
					$tg_id = $id;
					$title = $input_message->get_chat_title();
					$mode = self::MODE_GO;
					$enabled = 1;

					$group_chat = new GroupChat();

					$group_chat->owner_id = $owner_id;
					$group_chat->tg_id = $id;
					$group_chat->title = $title;
					$group_chat->mode = $mode;
					$group_chat->enabled = $enabled;

					$group_chat->save();	
				}

				TG::sendMessageHTML($id, "Бот активирован ✅");
				return;
			}

			if ($text == self::DISABLE_COMMAND) {
				if (Groupchat::find(['tg_id' => $id])->exists()) {
					$groupchat = Groupchat::find()->where(['tg_id' => $id])->one();
					$groupchat->setEnabled(FALSE);

					$groupchat->save();
				}

				TG::sendMessageHTML($id, "Бот остановлен ✅");
			}

			if (Groupchat::find()->where(['tg_id' => $id])->exists() && $text !== null) {
				$this->process_group_message($id, $mid, $text);
			}
			

			return;
		}

		$user = Groupuser::find()->where(['id' => $id])->one();
		$flag = $user->getFlag();
		
		if ($flag == 1) {
			$chat_id = $user->getChatId();

			$goword_text = $text;

			$goword = new GroupGoword();
			$goword->setChatId($chat_id);
			$goword->setText($goword_text);

			$goword->save();

			TG::deleteMessage($id, $mid);

			$user->setFlag(0);
			$this->send_chat($id, $chat_id);
			$user->save();
		}

		if ($flag == 2) {
			$chat_id = $user->getChatId();

			$stopword_text = $text;

			$stopword = new GroupStopword();
			$stopword->setChatId($chat_id);
			$stopword->setText($stopword_text);

			$stopword->save();

			TG::deleteMessage($id, $mid);

			$user->setFlag(0);
			$user->save();

			$this->send_chat($id, $chat_id);
		}
	}

	private function process_group_message($id, $mid, $text) {

		$chat = Groupchat::find()->where(['tg_id' => $id])->one();

		$chat_id = $chat->getId();
		$mode = $chat->getMode();

		if ($mode == self::MODE_GO) {
			$gowords = GroupGoword::find()->where(['chat_id' => $chat_id])->all();

			$go = false;
			foreach ($gowords as $goword) {
				if ($goword->accept($text)) {
					$go = true;
					break;
				}
			}

			if (!$go) {
				TG::deleteMessage($id, $mid);
			}
		} else {
			$stopwords = GroupStopword::find()->where(['chat_id' => $chat_id])->all();

			$stop = false;
			foreach ($stopwords as $stopword) {
				if ($stopword->reject($text)) {
					$stop = true;
					break;
				}
			}

			if ($stop) {
				TG::deleteMessage($id, $mid);
			}
		}
	}

	function handleCallbackQuery() {
		$callback_query = new CallbackQuery($this->output);

		$id = $callback_query->get_chat_id();
		$mid = $callback_query->get_mid();
		$data = $callback_query->get_data();

		if (preg_match('/^go_to_instruction$/', $data)) {
			$this->send_instruction($id, $mid);
		}

		if (preg_match('/^remove_stopword/', $data)) {
			$slices = explode(":", $data);

			$stopword_id = $slices[1];
			$chat_id = $slices[2];

			GroupStopword::find($stopword_id)->one()->delete();

			$this->send_chat($id, $chat_id, $mid);
		}

		if (preg_match('/^remove_goword/', $data)) {
			$slices = explode(":", $data);

			$goword_id = $slices[1];
			$chat_id = $slices[2];

			GroupGoword::find($goword_id)->one()->delete();

			$this->send_chat($id, $chat_id, $mid);
		}

		if (preg_match('/^go_to_add_stopword/', $data)) {
			$slices = explode(":", $data);

			$chat_id = $slices[1];

			$this->send_add_stopword($id, $chat_id, $mid);
		}

		if (preg_match('/^go_to_add_goword/', $data)) {
			$slices = explode(":", $data);

			$chat_id = $slices[1];

			$this->send_add_goword($id, $chat_id, $mid);
		}

		if (preg_match('/^manage_mode/', $data)) {
			$slices = explode(":", $data);

			$mode = $slices[1];
			$chat_id = $slices[2];

			$chat = GroupChat::find()->where(['_id' => $chat_id])->one();

			if ($chat->getMode() != $mode) {
				$chat->setMode($mode);
				$chat->save();

				$this->send_chat($id, $chat_id, $mid);
			}
		}

		if (preg_match('/^go_to_chats$/', $data)) {
			$this->send_chats($id, $mid);
			exit();
		}

		if (preg_match('/^go_to_chat/', $data)) {
			$slices = explode(":", $data);

			$chat_id = $slices[1];

			$this->send_chat($id, $chat_id, $mid);
		}

		if (preg_match('/^go_to_main$/', $data)) {
			$this->send_main($id, $mid);
		}
	}

	function start($id, $text) {
		if (!Groupuser::find()->where(['id' => $id])->exists()) {
			$groupuser = new Groupuser();

			$groupuser->id = $id;
			$groupuser->save();
		}

		$this->send_main($id);
	}

	private function main_reply() {
		return urlencode("Привет! ✋
Я бот, который фильтрует сообщения в группах!");
	}

	private function main_buttons() {
		return $this->get_inline_keyboard([
			[['text' => "🚀 Группы", 'callback_data' => 'go_to_chats']],
			[['text' => '📕 Инструкция', 'callback_data' => "go_to_instruction"]],
		]);
	}

	private function send_main($id, $mid = null) {
		$reply = $this->main_reply() . $this->main_buttons();

		TG::send_or_edit_messageHTML($id, $mid, $reply);
	}

	private function instruction_reply() {
		return urlencode("<b>📕 Инструкция</b>

Чтобы активировать бота в канале
1) Добавьте бота в группу
2) Назначьте его админом
3) Отправьте в группу команду " . self::ENABLE_COMMAND . "
4) Настройте бота в разделе \"🚀 Группы\"

❗️ Для остановки работы бота отправьте команду " . self::DISABLE_COMMAND);
	}

	private function instruction_buttons() {
		return $this->get_inline_keyboard([
			[['text' => self::BACK, "callback_data" => "go_to_main"]],
		]);
	}

	private function send_instruction($id, $mid = null) {
		$reply = $this->instruction_reply() . $this->instruction_buttons();

		TG::send_or_edit_messageHTML($id, $mid, $reply);
	}

	private function chats_reply() {
		return "<b>🚀 Группы</b>";
	}

	private function chats_buttons($id) {
		$chats = GroupChat::find()->where(['owner_id' => $id])->all();

		foreach ($chats as $chat) {
			$buttons[] = [['text' => "⚡️ " . $chat->getTitle(), 'callback_data' => 'go_to_chat:' . $chat->getId()]];
		}

		$buttons[] = [['text' => self::BACK, "callback_data" => "go_to_main"]];

		return $this->get_inline_keyboard($buttons);
	}

	private function send_chats($id, $mid = null) {
		$reply = $this->chats_reply() . $this->chats_buttons($id);

		TG::send_or_edit_messageHTML($id, $mid, $reply);
	}

	private function add_goword_reply() {
		return "Отправьте фразу";
	}

	private function add_goword_buttons($chat_id) {
		return $this->get_inline_keyboard([
			[['text' => self::BACK, "callback_data" => "go_to_chat:$chat_id"]],
		]);
	}

	private function send_add_goword($id, $chat_id, $mid = null) {
		$reply = $this->add_goword_reply() . $this->add_goword_buttons($chat_id);

		$user = Groupuser::find()->where(['id' => $id])->one();
		$user->setChatId($chat_id);
		$user->setFlag(1);
		$user->save();

		TG::send_or_edit_messageHTML($id, $mid, $reply, TRUE);
	}

	private function add_stopword_reply() {
		return "Отправьте фразу";
	}

	private function add_stopword_buttons($chat_id) {
		return $this->get_inline_keyboard([
			[['text' => self::BACK, "callback_data" => "go_to_chat:$chat_id"]],
		]);
	}

	private function send_add_stopword($id, $chat_id, $mid = null) {
		$reply = $this->add_stopword_reply() . $this->add_stopword_buttons($chat_id);

		$user = Groupuser::find()->where(['id' => $id])->one();
		$user->setChatId($chat_id);
		$user->setFlag(2);
		$user->save();

		TG::send_or_edit_messageHTML($id, $mid, $reply, TRUE);
	}

	private function chat_reply($chat_id) {
		$chat = GroupChat::find($chat_id)->one();

		$title = $chat->getTitle();
		$mode = $chat->getMode();

		return urlencode("⚡️ " . $title . "

<b>Режим:</b> " . ($mode == self::MODE_GO ? "Пропускные фразы" : "Стоп фразы"));
	}

	private function chat_buttons($chat_id) {

		$chat = GroupChat::find()->where(['_id' => $chat_id])->one();

		$mode = $chat->getMode();

		$go = ($mode == self::MODE_GO ? "✅" : "🅾️") . " Пропускные фразы";
		$stop = ($mode == self::MODE_STOP ? "✅" : "🅾️") . " Стоп фразы";

		$phrases = $mode == self::MODE_GO ? GroupGoword::find()->where(['chat_id' => $chat_id])->all() : GroupStopword::find()->where(['chat_id' => $chat_id])->all();

		$buttons = [];

		$buttons[] = [['text' => $go, "callback_data" => "manage_mode:" . self::MODE_GO . ":$chat_id"], ['text' => $stop, 'callback_data' => "manage_mode:" . self::MODE_STOP . ":$chat_id"]];

		foreach ($phrases as $phrase) {
			$callback_data = ($mode == self::MODE_GO ? "remove_goword:" . $phrase->getId() : "remove_stopword:" . $phrase->getId()) . ":" . $chat_id;

			$buttons[] = [['text' => "⚡️ " . $phrase->getText(), "callback_data" => $callback_data]];
		}

		$callback_data = $mode == self::MODE_GO ? "go_to_add_goword:$chat_id" : "go_to_add_stopword:$chat_id";

		$buttons[] = [['text' => '✅ Добавить фразу', 'callback_data' => $callback_data]];
		$buttons[] = [['text' => self::BACK, "callback_data" => "go_to_chats"],
					['text' => self::MAIN, "callback_data" => "go_to_main"]];

		return $this->get_inline_keyboard($buttons);
	}

	private function send_chat($id, $chat_id, $mid = null) {
		$reply = $this->chat_reply($chat_id) . $this->chat_buttons($chat_id);

		$user = Groupuser::find($id)->one();
		$user->setFlag(0);
		$user->save();

		TG::send_or_edit_messageHTML($id, $mid, $reply);
	}

	private function send_or_edit_messageHTML($id, $mid, $reply, $delete = FALSE) {
		if ($delete) {
			if ($mid !== null) {
				TG::deleteMessage($id, $mid);
			}

			$this->sendMessageHTML($id, $reply);
		} else {
			if ($mid === null) {
				TG::sendMessageHTML($id, $reply);
			} else {
				TG::editMessageTextHTML($id, $mid, $reply);
			}
		}
	}

	private function get_inline_keyboard($buttons) {
    	$keyboard = [
        	'inline_keyboard' => $buttons,
        ];

    	return "&reply_markup=" . json_encode($keyboard);
	}
}

?>