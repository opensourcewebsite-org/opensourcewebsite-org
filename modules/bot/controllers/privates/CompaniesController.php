<?php
namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\response\SendMessageCommand;
use app\modules\bot\components\Controller;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\modules\bot\components\response\EditMessageTextCommand;
use app\modules\bot\components\response\AnswerCallbackQueryCommand;
use app\modules\bot\components\response\EditMessageReplyMarkupCommand;
use app\modules\bot\components\response\ResponseBuilder;
use app\models\Company;
use app\modules\bot\components\Emoji;

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
                ],
            ];
        }

        return (new ResponseBuilder($update))
            ->answerCallbackQuery()
            ->editMessageTextOrSendMessage(
                $this->render('index'),
                $this->textFormat,
                new InlineKeyboardMarkup(array_merge($keyboards, [
                    [
                        [
                            'text' => Emoji::BACK,
                            'callback_data' => '/hr',
                        ],
                        [
                            'text' => Emoji::ADD,
                            'callback_data' => '/create_company',
                        ],
                    ]
                ])),
                new InlineKeyboardMarkup(array_merge($keyboards, [
                    [
                        [
                            'text' => Emoji::ADD,
                            'callback_data' => '/create_company',
                        ],
                    ]
                ]))
            )
            ->build();
	}

	public function actionCreate()
	{
		$update = $this->getUpdate();
		$state = $this->getState();

        $state->setName('/set_company_name');

        return (new ResponseBuilder($update))
            ->answerCallbackQuery()
            ->removeInlineKeyboardMarkup()
            ->sendMessage(
                $this->render('set-name'),
                $this->textFormat,
                null
            )
            ->build();
	}

    public function actionUpdate($id)
    {
		$update = $this->getUpdate();
		$state = $this->getState();

        $company = Company::findOne($id);

        $state->setName('/set_company_name');
        $state->setIntermediateField('id', $id);

        return (new ResponseBuilder($update))
            ->answerCallbackQuery()
            ->removeInlineKeyboardMarkup()
            ->sendMessage(
                $this->render('set-name'),
                $this->textFormat,
                null
            )
            ->build();
    }

    public function actionSetName()
    {
        $update = $this->getUpdate();
        $state = $this->getState();

        $text = $update->getMessage()->getText();
        $state->setName('/set_company_url');
        $state->setIntermediateField('name', $text);

        return (new ResponseBuilder($update))
            ->answerCallbackQuery()
            ->sendMessage(
                $this->render('set-url'),
                $this->textFormat,
                new InlineKeyboardMarkup([
                    [
                        [
                            'text' => $this->render('skip'),
                            'callback_data' => '/set_company_address',
                        ],
                    ],
                ])
            )
            ->build();
    }

    public function actionSetUrl()
    {
        $update = $this->getUpdate();
        $state = $this->getState();

        $text = $update->getMessage()->getText();
        $state->setName('/set_company_address');
        $state->setIntermediateField('url', $text);

        return (new ResponseBuilder($update))
            ->answerCallbackQuery()
            ->sendMessage(
                $this->render('set-address'),
                $this->textFormat,
                new InlineKeyboardMarkup([
                    [
                        [
                            'text' => $this->render('skip'),
                            'callback_data' => '/set_company_address',
                        ],
                    ],
                ])
            )
            ->build();
    }

    public function actionSetAddress()
    {
        $update = $this->getUpdate();
        $state = $this->getState();

        $text = $update->getMessage()->getText();
        $state->setName('/set_company_description');
        $state->setIntermediateField('address', $text);

        return (new ResponseBuilder($update))
            ->answerCallbackQuery()
            ->sendMessage(
                $this->render('set-description'),
                $this->textFormat,
                new InlineKeyboardMarkup([
                    [
                        [
                            'text' => $this->render('skip'),
                            'callback_data' => '/set_company_description',
                        ],
                    ],
                ])
            )
            ->build();
    }

    public function actionSetDescription()
    {
        $update = $this->getUpdate();
        $user = $this->getUser();
        $state = $this->getState();

        $description = $update->getMessage()->getText();
        $name = $state->getIntermediateField('name', '');
        $url = $state->getIntermediateField('url', null);
        $address = $state->getIntermediateField('address', null);
        $id = $state->getIntermediateField('id', null);
        $state->setName(null);

        $company = ($id == null) ? new Company() : Company::findOne($id);
        $company->setAttributes([
            'name' => $name,
            'url' => $url,
            'address' => $address,
            'description' => $description,
        ]);
        $company->save();

        if (is_null($id)) {
            $user->link('companies', $company);
        }

        return $this->actionShow($company->id);
    }

    public function actionShow($id)
    {
        $user = $this->getUser();
        $update = $this->getUpdate();

        $company = $user->getCompanies()->where(['id' => $id])->one();
        if ($company != null) {
            return (new ResponseBuilder($update))
                ->answerCallbackQuery()
                ->editMessageTextOrSendMessage(
                    $this->render('show', [
                        'name' => $company->name,
                        'url' => $company->url,
                        'address' => $company->address,
                        'description' => $company->description,
                    ]),
                    $this->textFormat,
                    new InlineKeyboardMarkup([
                        [
                            [
                                'text' => $this->render('vacancies'),
                                'callback_data' => '/vacancies',
                            ],
                        ],
                        [
                            [
                                'text' => Emoji::BACK,
                                'callback_data' => '/companies',
                            ],
                            [
                                'text' => Emoji::EDIT,
                                'callback_data' => '/update_company ' . $id,
                            ]
                        ],
                    ]),
                    new InlineKeyboardMarkup([
                        [
                            [
                                'text' => $this->render('vacancies'),
                                'callback_data' => '/vacancies',
                            ],
                        ],
                        [
                            [
                                'text' => Emoji::BACK,
                                'callback_data' => '/companies',
                            ],
                            [
                                'text' => Emoji::EDIT,
                                'callback_data' => '/update_company ' . $id,
                            ]
                        ],
                    ])
                )
                ->build();
        } else {

        }
    }
}
