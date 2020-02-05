<?php

namespace app\modules\group_bot;

use yii\base\Action;
use app\modules\group_bot\BotHandler;
use app\modules\bot\telegram\BotApiClient;
use app\modules\bot\models\Bot;

/**
 * Class GroupWebHookAction
 *
 * @package app\modules\group_bot
 */
class GroupWebHookAction extends Action {
	

	/**
     * @param string $token
     */
	public function run($token = '')
	{

		if (Bot::find()->where(['token' => $token])->exists()) {

			$bot = Bot::find()->where(['token' => $token])->one();
			if ($bot->getStatus() == Bot::BOT_STATUS_DISABLED) {
				return;
			}

			$output = json_decode(file_get_contents("php://input"), true);

			$botHandler = new BotHandler($output, $token);
			$botApiClient = new BotApiClient($token, $output);

			if ($botApiClient->getCallbackQuery() !== null) {
				$botHandler->handleCallbackQuery();
			} else if ($botApiClient->getMessage() !== null) {
				$botHandler->handleInputMessage();
			}
		}
	}
}

?>