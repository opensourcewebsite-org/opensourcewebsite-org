<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use \app\modules\bot\components\response\EditMessageTextCommand;
use \app\modules\bot\components\response\AnswerCallbackQueryCommand;
use \app\modules\bot\components\response\SendMessageCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use \app\models\User;
use app\modules\bot\helpers\PaginationButtons;
use yii\data\Pagination;
use app\modules\bot\components\Controller as Controller;

/**
 * Class MyCitizenshipController
 *
 * @package app\modules\bot\controllers
 */
class MyCitizenshipController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($сitizenship = null)
    {
        $update = $this->getUpdate();
        $user = $this->getUser();

        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('index'),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/my_сitizenship',
                                'text' => 'Country 1',
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/my_сitizenship',
                                'text' => 'Country 2',
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/my_сitizenship',
                                'text' => '<',
                            ],
                            [
                                'callback_data' => '/my_сitizenship',
                                'text' => '1/3',
                            ],
                            [
                                'callback_data' => '/my_сitizenship',
                                'text' => '>',
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/my_profile',
                                'text' => '🔙',
                            ],
                            [
                                'callback_data' => '/my_сitizenship__add',
                                'text' => '➕',
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }
}
