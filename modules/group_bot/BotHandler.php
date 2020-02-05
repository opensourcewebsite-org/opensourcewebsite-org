<?php

namespace app\modules\group_bot;

use app\models\GroupUser;
use app\models\GroupChat;
use app\models\GroupGoword;
use app\models\GroupStopword;
use app\modules\group_bot\models\CallbackQuery;
use app\modules\group_bot\models\InputMessage;
use Yii;

/**
 * Class BotHandler
 *
 * @package app\modules\group_bot
 */
class BotHandler {
	private $_output;
	private $_token;
	private $_botApi;

	const BACK = "Back";
	const MAIN = "To main menu";
	const START_COMMAND = "/start";
	const CHAT_PRIVATE = "private";
	const ENABLE_COMMAND = "/enable@group_ro_bot";
	const DISABLE_COMMAND = "/disable@group_ro_bot";
	const ME = 295605654;
	const TG_ID_KEY = 'tg_id';
	const ID_KEY = 'id';
	const HTML = 'html';

	/**
     * @param array $output
     * @param string $token
     */
	public function __construct($output, $token)
	{
		$this->_token = $token;
		$this->_output = $output;

		$this->_botApi = new \TelegramBot\Api\BotApi($token);

        if (isset(Yii::$app->params['telegramProxy'])) {
            $this->_botApi->setProxy(Yii::$app->params['telegramProxy']);
        }
	}

	private function configure($id) {
		$groupUser = GroupUser::find($id)->one();

		if ($groupUser->getLanguageCode() !== null) {
			\Yii::$app->language = $groupUser->getLanguageCode();
		}
	}

	/**
	* Handle Telegram Request Message
	*/
	public function handleInputMessage()
	{
		$inputMessage = new InputMessage($this->_output);

		$id = $inputMessage->getChatId();
		$mid = $inputMessage->getMessageId();
		$text = $inputMessage->getText();
		$type = $inputMessage->getChatType();

		$this->configure($id);

		if ($text == self::START_COMMAND) {
			$this->start($id, $text);
			return;
		}

		if ($type != self::CHAT_PRIVATE) {

			if ($text == self::ENABLE_COMMAND) {

				if (GroupChat::find()->where([GroupChat::TG_ID_KEY => $id])->exists()) {
					$groupChat = GroupChat::find()->where([GroupChat::TG_ID_KEY => $id])->one();
					$groupChat->setEnabled(true);

					$groupChat->save();
				} else {

					$ownerId = $inputMessage->getFromId();
					$tgId = $id;
					$title = $inputMessage->getChatTitle();
					$mode = GroupChat::MODE_GO;
					$enabled = true;

					$groupChat = new GroupChat();

					$groupChat->setOwnerId($ownerId);
					$groupChat->setTgId($id);
					$groupChat->setTitle($title);
					$groupChat->setMode($mode);
					$groupChat->setEnabled($enabled);

					$groupChat->save();	
				}

				$this->_botApi->sendMessage($id, "Бот активирован ✅", self::HTML);
				return;
			}

			if ($text == self::DISABLE_COMMAND) {
				if (GroupChat::find([GroupChat::TG_ID_KEY => $id])->exists()) {
					$groupChat = GroupChat::find()->where([GroupChat::TG_ID_KEY => $id])->one();
					$groupChat->setEnabled(false);

					$groupChat->save();
				}

				$this->_botApi->sendMessage($id, "Бот остановлен ✅", self::HTML);
				return;
			}



			if (GroupChat::find()->where([GroupChat::TG_ID_KEY => $id])->exists() && $text !== null) {
				$groupChat = GroupChat::find()->where([GroupChat::TG_ID_KEY => $id])->one();

				if ($groupChat->getEnabled()) {
					$this->processGroupMessage($id, $mid, $text);
				}
			}
		}

		$user = GroupUser::find()->where([self::ID_KEY => $id])->one();
		$flag = $user->getFlag();
		
		if ($flag == 1) {
			$chatId = $user->getChatId();

			$gowordText = $text;

			$goword = new GroupGoword();
			$goword->setChatId($chatId);
			$goword->setText($gowordText);

			$goword->save();

			$this->_botApi->deleteMessage($id, $mid);

			$user->setFlag(0);
			$this->sendChat($id, $chatId);
			$user->save();
		}

		if ($flag == 2) {
			$chatId = $user->getChatId();

			$stopword_text = $text;

			$stopword = new GroupStopword();
			$stopword->setChatId($chatId);
			$stopword->setText($stopwordText);

			$stopword->save();

			$this->_botApi->deleteMessage($id, $mid);

			$user->setFlag(0);
			$user->save();

			$this->sendChat($id, $chatId);
		}
	}

	/**
     * @param int $id
     * @param int $mid
     * @param string $text
     */
	private function processGroupMessage($id, $mid, $text)
	{

		$chat = Groupchat::find()->where([GroupChat::TG_ID_KEY => $id])->one();

		$chatId = $chat->getId();
		$mode = $chat->getMode();

		if ($mode == GroupChat::MODE_GO) {
			$gowords = GroupGoword::find()->where(['chat_id' => $chatId])->all();

			$go = false;
			foreach ($gowords as $goword) {
				if ($goword->accept($text)) {
					$go = true;
					break;
				}
			}

			if (!$go) {
				$this->_botApi->deleteMessage($id, $mid);
			}
		} else {
			$stopwords = GroupStopword::find()->where(['chat_id' => $chatId])->all();

			$stop = false;
			foreach ($stopwords as $stopword) {
				if ($stopword->reject($text)) {
					$stop = true;
					break;
				}
			}

			if ($stop) {
				$this->_botApi->deleteMessage($id, $mid);
			}
		}
	}

	/**
	* Handle Telegram Request CallbackQuery
	*/
	public function handleCallbackQuery()
	{
		$callbackQuery = new CallbackQuery($this->_output);

		$id = $callbackQuery->getChatId();
		$mid = $callbackQuery->getMid();
		$data = $callbackQuery->getData();

		$this->configure($id);

		if (preg_match('/^switch_language_code$/', $data)) {
			$groupUser = GroupUser::find($id)->one();

			$groupUser->setLanguageCode($groupUser->getLanguageCode() == GroupUser::LANGUAGE_CODE_RUS ? GroupUser::LANGUAGE_CODE_ENG : GroupUser::LANGUAGE_CODE_RUS);
			$groupUser->save();

			$this->configure($id);

			$this->sendMain($id, $mid);
		}

		if (preg_match('/^choose_language/', $data)) {
			$slices = explode(":", $data);

			$languageCode = $slices[1];

			$groupUser = GroupUser::find($id)->one();
			$groupUser->setLanguageCode($languageCode);
			$groupUser->save();

			$this->sendMain($id, $mid);
		}

		if (preg_match('/^remove_chat/', $data)) {
			$slices = explode(":", $data);

			$chatId = $slices[1];

			GroupChat::find($chatId)->one()->delete();

			$this->sendChats($id, $mid);
		}

		if (preg_match('/^go_to_instruction$/', $data)) {
			$this->sendInstruction($id, $mid);
		}

		if (preg_match('/^remove_stopword/', $data)) {
			$slices = explode(":", $data);

			$stopwordId = $slices[1];
			$chatId = $slices[2];

			GroupStopword::find($stopwordId)->one()->delete();

			$this->sendChat($id, $chatId, $mid);
		}

		if (preg_match('/^remove_goword/', $data)) {
			$slices = explode(":", $data);

			$gowordId = $slices[1];
			$chatId = $slices[2];

			GroupGoword::find($gowordId)->one()->delete();

			$this->sendChat($id, $chatId, $mid);
		}

		if (preg_match('/^go_to_add_stopword/', $data)) {
			$slices = explode(":", $data);

			$chatId = $slices[1];

			$this->sendAddStopword($id, $chatId, $mid);
		}

		if (preg_match('/^go_to_add_goword/', $data)) {
			$slices = explode(":", $data);

			$chatId = $slices[1];

			$this->sendAddGoword($id, $chatId, $mid);
		}

		if (preg_match('/^manage_mode/', $data)) {
			$slices = explode(":", $data);

			$mode = $slices[1];
			$chatId = $slices[2];

			$chat = GroupChat::find()->where(['_id' => $chatId])->one();

			if ($chat->getMode() != $mode) {
				$chat->setMode($mode);
				$chat->save();

				$this->sendChat($id, $chatId, $mid);
			}
		}

		if (preg_match('/^go_to_chats$/', $data)) {
			$this->sendChats($id, $mid);
			exit();
		}

		if (preg_match('/^go_to_chat/', $data)) {
			$slices = explode(":", $data);

			$chatId = $slices[1];

			$this->sendChat($id, $chatId, $mid);
		}

		if (preg_match('/^go_to_main$/', $data)) {
			$this->sendMain($id, $mid);
		}
	}

	/**
     * @param int $id
     * @param string $text
     */
	public function start($id, $text)
	{
		if (!GroupUser::find()->where([self::ID_KEY => $id])->exists()) {
			$groupUser = new GroupUser();

			$groupUser->id = $id;
			$groupUser->save();
		}

		$this->sendMain($id);
	}

	/**
     * @return string
     */
	private function mainReply()
	{
		return \Yii::t('bot', 'Hello') . '! ✋
' . \Yii::t('bot', 'I am bot, who filter messages in groups!');
	}

	/**
     * @param int $id
     * @param int $mid
     * @param string $text
     *
     * @return InlineKeyboardMarkup
     */
	private function mainButtons($id)
	{
		$languageCode = GroupUser::find($id)->one()->getLanguageCode();

		return new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup([
			[['text' => '🚀 ' . \Yii::t('bot', 'Groups'), 'callback_data' => 'go_to_chats']],
			[['text' => '📕 ' . \Yii::t('bot', 'Instruction'), 'callback_data' => "go_to_instruction"]],
			[['text' => ($languageCode == GroupUser::LANGUAGE_CODE_RUS ? "🇬🇧 EN" : "🇷🇺 RU"), 'callback_data' => 'switch_language_code']],
		]);
	}

	/**
     * @param int $id
     * @param int $mid
     */
	private function sendMain($id, $mid = null)
	{
		if (GroupUser::find($id)->one()->getLanguageCode() === null) {
			$this->sendLanguageChoice($id, $mid);
		} else {
			$this->sendOrEditMessageHTML($id, $mid, $this->mainReply(), $this->mainButtons($id));
		}
	}

	private function languageChoiceReply() {
		return "Выберите язык / Choose language";
	}

	private function languageChoiceButtons() {
		return new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup([
			[['text' => '🇷🇺 Русский', 'callback_data' => 'choose_language:' . GroupUser::LANGUAGE_CODE_RUS],
			['text' => '🇬🇧 English', 'callback_data' => 'choose_language:' . GroupUser::LANGUAGE_CODE_ENG]],
		]);
	}

	private function sendLanguageChoice($id, $mid = null) {
		$this->sendOrEditMessageHTML($id, $mid, $this->languageChoiceReply(), $this->languageChoiceButtons());
	}

	/**
     * @return string
     */
	private function instructionReply()
	{
		return '<b>📕 ' . \Yii::t('bot', 'Instruction') . '</b>

' . \Yii::t('bot', 'To activate bot in group') . '
1) ' . \Yii::t('bot', 'Add bot to the group') . '
2) ' . \Yii::t('bot', 'Make bot an admin') . '
3) ' . \Yii::t('bot', 'Type a command in the group') . ' ' . self::ENABLE_COMMAND . '
4) ' . \Yii::t('bot', 'Configure bot in menu') . ' ' . '"' . '🚀 ' . \Yii::t('bot', 'Groups') . '"

❗️ ' . \Yii::t('bot', 'To stop bot type a command') . ' ' . self::DISABLE_COMMAND;
	}

	/**
     * @return InlineKeyboardMarkup
     */
	private function instructionButtons()
	{
		return new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup([
			[['text' => '◀️ ' . \Yii::t('bot', self::BACK), "callback_data" => "go_to_main"]],
		]);
	}

	/**
     * @param int $id
     * @param int $mid
     */
	private function sendInstruction($id, $mid = null)
	{
		$this->sendOrEditMessageHTML($id, $mid, $this->instructionReply(), $this->instructionButtons());
	}

	/**
     * @return string
     */
	private function chatsReply()
	{
		return '🚀 <b>' . \Yii::t('bot', 'Groups') . '</b>';
	}

	/**
     * @param int $id
     *
     * @return InlineKeyboardMarkup
     */
	private function chatsButtons($id)
	{
		$chats = GroupChat::find()->where(['owner_id' => $id])->all();

		foreach ($chats as $chat) {
			$buttons[] = [
				['text' => "⚡️ " . $chat->getTitle(), 'callback_data' => 'go_to_chat:' . $chat->getId()],
				['text' => '❌ ' . \Yii::t('bot', 'Remove'), 'callback_data' => 'remove_chat:' . $chat->getId()],
			];
		}

		$buttons[] = [['text' => '◀️ ' . \Yii::t('bot', self::BACK), "callback_data" => "go_to_main"]];

		return new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($buttons);
	}

	/**
     * @param int $id
     * @param int $mid
     */
	private function sendChats($id, $mid = null)
	{
		$this->sendOrEditMessageHTML($id, $mid, $this->chatsReply(), $this->chatsButtons($id));
	}

	/**
     * @return string
     */
	private function addGowordReply()
	{
		return \Yii::t('bot', 'Type a phrase');
	}

	/**
     * @param int $chatId
     *
     * @return InlineKeyboardMarkup
     */
	private function addGowordButtons($chatId)
	{
		return new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup([
			[['text' => '◀️ ' . \Yii::t('bot', self::BACK), "callback_data" => "go_to_chat:$chatId"]],
		]);
	}

	/**
     * @param int $id
     * @param int $chatId
     * @param int $mid
     */
	private function sendAddGoword($id, $chatId, $mid = null)
	{
		$user = GroupUser::find()->where([self::ID_KEY => $id])->one();
		$user->setChatId($chatId);
		$user->setFlag(1);
		$user->save();

		$this->sendOrEditMessageHTML($id, $mid, $this->addGowordReply(), $this->addGowordButtons($chatId), true);
	}

	/**
     * @return string
     */
	private function addStopwordReply()
	{
		return "Отправьте фразу";
	}

	/**
     * @param int $chatId
     *
     * @return InlineKeyboardMarkup
     */
	private function addStopwordButtons($chatId)
	{
		return new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup([
			[['text' => '◀️ ' . \Yii::t('bot', self::BACK), "callback_data" => "go_to_chat:$chatId"]],
		]);
	}

	/**
     * @param int $id
     * @param int $chatId
     * @param int $mid
     */
	private function sendAddStopword($id, $chatId, $mid = null)
	{
		$user = GroupUser::find()->where([self::ID_KEY => $id])->one();
		$user->setChatId($chatId);
		$user->setFlag(2);
		$user->save();

		$this->sendOrEditMessageHTML($id, $mid, $this->addStopwordReply(), $this->addStopwordButtons($chatId), true);
	}

	/**
     * @param int $id
     * @param int $mid
     * @param string $reply
     * @param InlineKeyboardMarkup $replyMarkup
     * @param bool $delete
     */
	private function sendOrEditMessageHTML($id, $mid, $reply, $replyMarkup, $delete = false)
	{
		if ($delete) {
			if ($mid !== null) {
				$this->_botApi->deleteMessage($id, $mid);
			}

			$this->_botApi->sendMessage($id, $reply, self::HTML, false, null, $replyMarkup);
		} else {
			if ($mid === null) {
				$this->_botApi->sendMessage($id, $reply, self::HTML, false, null, $replyMarkup);
			} else {
				$this->_botApi->editMessageText($id, $mid, $reply, self::HTML, false, $replyMarkup);
			}
		}
	}

	/**
	 * @param int $chatId
	 *
     * @return string
     */
	private function chatReply($chatId)
	{
		$chat = GroupChat::find($chatId)->one();

		$title = $chat->getTitle();
		$mode = $chat->getMode();
		$enabled = $chat->getEnabled();

		return "⚡️ " . $title . "

<b>" . \Yii::t('bot', 'Mode') . ":</b> " . ($mode == GroupChat::MODE_GO ? \Yii::t('bot', 'Go phrases')  : \Yii::t('bot', 'Stop phrases')) . "

<b>" . ($enabled == true ? "✅ " . \Yii::t('bot', 'Launched') : '❗️ ' . \Yii::t('bot', 'Paused')) . "</b>";
	}

	/**
     * @param int $chatId
     *
     * @return InlineKeyboardMarkup
     */
	private function chatButtons($chatId)
	{
		$chat = GroupChat::find()->where(['_id' => $chatId])->one();

		$mode = $chat->getMode();

		$go = ($mode == GroupChat::MODE_GO ? "✅" : "🅾️") . " " . \Yii::t('bot', 'Go phrases');
		$stop = ($mode == GroupChat::MODE_STOP ? "✅" : "🅾️") . " " . \Yii::t('bot', 'Stop phrases');

		$phrases = $mode == GroupChat::MODE_GO ? GroupGoword::find()->where(['chat_id' => $chatId])->all() : GroupStopword::find()->where(['chat_id' => $chatId])->all();

		$buttons = [];

		$buttons[] = [['text' => $go, "callback_data" => "manage_mode:" . GroupChat::MODE_GO . ":$chatId"], ['text' => $stop, 'callback_data' => "manage_mode:" . GroupChat::MODE_STOP . ":$chatId"]];

		foreach ($phrases as $phrase) {
			$callbackData = ($mode == GroupChat::MODE_GO ? "remove_goword:" . $phrase->getId() : "remove_stopword:" . $phrase->getId()) . ":" . $chatId;

			$buttons[] = [['text' => "⚡️ " . $phrase->getText(), "callback_data" => $callbackData]];
		}

		$callbackData = $mode == GroupChat::MODE_GO ? "go_to_add_goword:$chatId" : "go_to_add_stopword:$chatId";

		$buttons[] = [['text' => '✅ ' . \Yii::t('bot', 'Add phrase'), 'callback_data' => $callbackData]];
		$buttons[] = [['text' => '◀️ ' . \Yii::t('bot', self::BACK), "callback_data" => "go_to_chats"],
					['text' => '⏪ ' . \Yii::t('bot', self::MAIN), "callback_data" => "go_to_main"]];

		return new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($buttons);
	}

	/**
     * @param int $id
     * @param int $chatId
     * @param int $mid
     */
	private function sendChat($id, $chatId, $mid = null)
	{
		$user = GroupUser::find($id)->one();
		$user->setFlag(0);
		$user->save();

		$this->sendOrEditMessageHTML($id, $mid, $this->chatReply($chatId), $this->chatButtons($chatId));
	}
}

?>