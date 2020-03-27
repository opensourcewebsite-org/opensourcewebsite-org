<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\response\ResponseBuilder;
use Yii;
use app\modules\bot\components\response\commands\SendMessageCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

/**
 * Class DefaultController
 *
 * @package app\modules\bot\controllers
 */
class DefaultController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->sendMessage(
                $this->render('/menu/index')
            )
            ->build();
    }

    /**
     * @return array
     */
    public function actionCommandNotFound()
	{
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->sendMessage(
                $this->render('command-not-found'),
                [
                    [
                        [
                            'url' => 'https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/DONATE.md',
                            'text' => '👼 ' . Yii::t('bot', 'Donate'),
                        ],
                        [
                            'url' => 'https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/CONTRIBUTING.md',
                            'text' => '👨‍🚀 ' . Yii::t('bot', 'Contribution'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => '/menu',
                            'text' => Yii::t('bot', 'Menu'),
                        ],
                    ],
                ]
            )
            ->build();
    }
}
