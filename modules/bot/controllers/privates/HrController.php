<?php
namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\response\SendMessageCommand;
use app\modules\bot\components\Controller;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\modules\bot\components\response\EditMessageTextCommand;
use app\modules\bot\components\response\AnswerCallbackQueryCommand;

class HrController extends Controller
{
	public function actionIndex()
	{
		$update = $this->getUpdate();

		if ($update->getMessage())
		{
			return [
				new SendMessageCommand(
					$this->getTelegramChat()->chat_id,
					$this->render('index', [
						'isNotificationsEnabled' => true,
					]),
					[
						'parseMode' => $this->textFormat,
						'replyMarkup' => new InlineKeyboardMarkup([
							[
								[
									'text' => Yii::t('bot', 'Вакансии'),
									'callback_data' => '/vacancies',
								],
								[
									'text' => Yii::t('bot', 'Резюме'),
									'callback_data' => '/cvs',
								],
							],
							[
								[
									'text' => Yii::t('bot', 'Компании'),
									'callback_data' => '/companies',
								],
							],
							[
								[
									'text' => Yii::t('bot', '🔔'),
									'callback_data' => '/hrnotifications'
								],
							],
						]),
					]
				),
			];
		}
		elseif ($update->getCallbackQuery())
		{
			return [
				new AnswerCallbackQueryCommand(
					$update->getCallbackQuery()->getMessage()->getMessageId()
				),
				new EditMessageTextCommand(
					$this->getTelegramChat()->chat_id,
					$update->getCallbackQuery()->getMessage()->getMessageId(),
					$this->render('index', [
						'isNotificationsEnabled' => true,
					]),
					[
						'parseMode' => $this->textFormat,
						'replyMarkup' => new InlineKeyboardMarkup([
							[
								[
									'text' => Yii::t('bot', 'Вакансии'),
									'callback_data' => '/vacancies',
								],
								[
									'text' => Yii::t('bot', 'Резюме'),
									'callback_data' => '/cvs',
								],
							],
							[
								[
									'text' => Yii::t('bot', 'Компании'),
									'callback_data' => '/companies',
								],
							],
							[
								[
									'text' => Yii::t('bot', '🔔'),
									'callback_data' => '/hrnotifications'
								],
							],
						]),
					]
				),
			];
		}
	}
}
