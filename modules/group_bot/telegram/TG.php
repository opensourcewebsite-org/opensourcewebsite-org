<?php

namespace app\modules\group_bot\telegram;

class TG {
	public static function sendMessageHTML($id, $text) {
		file_get_contents("https://reddle.ru/telegram_api/sendMessage.php?chat_id=$id&text=" . urlencode($text));
	}

	public static function editMessageTextHTML($id, $mid, $reply) {
		file_get_contents("https://reddle.ru/telegram_api/sendMessage.php?chat_id=$id&message_id=$mid&text=" . urlencode($reply));
	}

	public static function deleteMessage($id, $mid) {
		file_get_contents("https://reddle.ru/telegram_api/sendMessage.php?action=deleteMessage&chat_id=$id&mid=$mid");
	}

	public static function send_or_edit_messageHTML($id, $mid, $reply, $delete = FALSE) {
		if ($delete) {
			if ($mid === null) {
				self::deleteMessage($id, $mid);
			}

			self::sendMessageHTML($id, $reply);
		} else {
			if ($mid === null) {
				self::sendMessageHTML($id, $reply);
			} else {
				self::editMessageTextHTML($id, $mid, $reply);
			}
		}
	}

	public static function is_callback_query($output) {
		return isset($output['callback_query']);
	}

	public static function is_input_message($output) {
		return isset($output['message']);
	}
}

?>