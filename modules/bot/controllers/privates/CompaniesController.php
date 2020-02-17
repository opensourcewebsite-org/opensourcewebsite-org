<?php
namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\response\SendMessageCommand;
use app\modules\bot\components\Controller;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\modules\bot\components\response\EditMessageTextCommand;
use app\modules\bot\components\response\AnswerCallbackQueryCommand;
use app\modules\bot\components\response\EditMessageReplyMarkupCommand;
use app\models\Company;

class CompaniesController extends Controller
{
	public function actionIndex()
	{
		$update = $this->getUpdate();
        $user = $this->getUser();

        $keyboards = [];
        $companies = $user->getCompanies()->all();
        foreach ($companies as $company) {
            $keyboards[] = [
                [
                    'text' => $company->name,
                    'callback_data' => '/company ' . $company->id,
                ]
            ];
        }
        if ($update->getCallbackQuery()) {
            $keyboards[] = [
                [
                    'text' => '⬅️',
                    'callback_data' => '/hr',
                ],
                [
                    'text' => '➕',
                    'callback_data' => '/create_company',
                ],
            ];
        }
        else {
            $keyboards[] = [
                [
                    'text' => '➕',
                    'callback_data' => '/create_company',
                ],
            ];
        }

		if ($update->getMessage()) {
			return [
				new SendMessageCommand(
					$this->getTelegramChat()->chat_id,
					$this->render('index'),
					[
						'parseMode' => $this->textFormat,
						'replyMarkup' => new InlineKeyboardMarkup($keyboards),
					]
				),
			];
		} elseif ($update->getCallbackQuery()) {
			return [
				new AnswerCallbackQueryCommand(
					$update->getCallbackQuery()->getMessage()->getMessageId()
				),
				new EditMessageTextCommand(
					$this->getTelegramChat()->chat_id,
					$update->getCallbackQuery()->getMessage()->getMessageId(),
					$this->render('index'),
					[
						'parseMode' => $this->textFormat,
                        'replyMarkup' => new InlineKeyboardMarkup($keyboards),
					]
				),
			];
		}
	}

	public function actionCreate()
	{
		$update = $this->getUpdate();
		$state = $this->getState();

        $state->setName('/set_company_name');

		return [
			new AnswerCallbackQueryCommand(
				$update->getCallbackQuery()->getId()
			),
            new EditMessageReplyMarkupCommand(
                $this->getTelegramChat()->chat_id,
                $update->getCallbackQuery()->getMessage()->getMessageId()
            ),
			new SendMessageCommand(
				$this->getTelegramChat()->chat_id,
				$this->render('set-name'),
				[
					'parseMode' => $this->textFormat,
				]
			),
		];
	}

    public function actionSetName()
    {
        $update = $this->getUpdate();
        $state = $this->getState();

        $text = $update->getMessage()->getText();
        $state->setName('/set_company_url');
        $state->setIntermediateField('name', $text);

        return [
			new SendMessageCommand(
				$this->getTelegramChat()->chat_id,
				$this->render('set-url'),
				[
					'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'text' => $this->render('skip'),
                                'callback_data' => '/set_company_address',
                            ],
                        ],
                    ]),
				]
			),
        ];
    }

    public function actionSetUrl()
    {
        $update = $this->getUpdate();
        $state = $this->getState();

        $text = $update->getMessage()->getText();
        $state->setName('/set_company_address');
        $state->setIntermediateField('url', $text);

        return [
			new SendMessageCommand(
				$this->getTelegramChat()->chat_id,
				$this->render('set-address'),
				[
					'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'text' => $this->render('skip'),
                                'callback_data' => '/set_company_address',
                            ],
                        ],
                    ]),
				]
			),
        ];
    }

    public function actionSetAddress()
    {
        $update = $this->getUpdate();
        $state = $this->getState();

        $text = $update->getMessage()->getText();
        $state->setName('/set_company_description');
        $state->setIntermediateField('address', $text);

        return [
			new SendMessageCommand(
				$this->getTelegramChat()->chat_id,
				$this->render('set-description'),
				[
					'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'text' => $this->render('skip'),
                                'callback_data' => '/set_company_description',
                            ],
                        ],
                    ]),
				]
			),
        ];
    }

    public function actionSetDescription()
    {
        $update = $this->getUpdate();
        $user = $this->getUser();
        $state = $this->getState();

        $description = $update->getMessage()->getText();
        $name = $state->getIntermediateField('name');
        $url = $state->getIntermediateField('url');
        $address = $state->getIntermediateField('address');
        $state->setName(null);

        $company = new Company();
        $company->setAttributes([
            'name' => $name,
            'url' => $url,
            'address' => $address,
            'description' => $description,
        ]);
        $company->save();

        $user->link('companies', $company);

        return $this->actionShow($company->id);
    }

    public function actionShow($id)
    {
        $user = $this->getUser();
        $company = $user->getCompanies()->where(['id' => $id])->one();
        if ($company != null) {

            return [
    			new SendMessageCommand(
    				$this->getTelegramChat()->chat_id,
    				$this->render('show', [
                        'name' => $company->name,
                        'url' => $company->url,
                        'address' => $company->address,
                        'description' => $company->description,
                    ]),
    				[
    					'parseMode' => $this->textFormat,
                        'replyMarkup' => new InlineKeyboardMarkup([
                            [
                                [
                                    'text' => $this->render('vacancies'),
                                    'callback_data' => '/vacancies',
                                ],
                            ],
                        ]),
    				]
    			),
            ];
        } else {

        }
    }
}
