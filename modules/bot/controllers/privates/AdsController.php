<?php
namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\response\commands\SendMessageCommand;
use app\modules\bot\components\Controller;

class AdsController extends Controller
{
	public function actionIndex()
	{
		return [
			new SendMessageCommand(
				$this->getTelegramChat()->chat_id,
				$this->render('index')
			),
		];
	}
}
