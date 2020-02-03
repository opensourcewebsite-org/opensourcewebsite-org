<?php

namespace app\modules\group_bot;

use yii\base\Action;
use app\modules\group_bot\BotHandler;
use app\modules\group_bot\telegram\TG;

class GroupWebHookAction extends Action {
	
	public function run($token = '') {
		$output = json_decode(file_get_contents("php://input"), true);

		$bot_handler = new BotHandler($output);

		if (TG::is_callback_query($output)) {
			$bot_handler->handleCallbackQuery();
		} else if (TG::is_input_message($output)) {
			$bot_handler->handleInputMessage();
		}
	}
}

?>