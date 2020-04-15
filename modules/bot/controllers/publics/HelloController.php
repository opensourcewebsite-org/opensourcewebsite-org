<?php

namespace app\modules\bot\controllers\publics;

use app\modules\bot\components\response\commands\SendMessageCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use Yii;
use app\modules\bot\components\Controller as Controller;

/**
 * Class HelloController
 *
 * @package app\controllers\bot
 */
class HelloController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('index'),
                [
                    'parseMode' => $this->textFormat,
                    'replyToMessageId' => $this->getUpdate()->getMessage()->getMessageId(),
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'url' => 'https://t.me/opensourcewebsite_bot',
                                'text' => Yii::t('bot', 'Bot'),
                            ],
                        ],
                        [
                            [
                                'url' => 'https://opensourcewebsite.org',
                                'text' => Yii::t('bot', 'Website'),
                            ],
                        ],
                        [
                            [
                                'url' => 'https://join.slack.com/t/opensourcewebsite/shared_invite/enQtNDE0MDc2OTcxMDExLWJmMjFjOGUxNjFiZTg2OTc0ZDdkNTdhNDIzZDE2ODJiMGMzY2M5Yjg3NzEyNGMxNjIwZWE0YTFhNTE3MjhiYjY',
                                'text' => Yii::t('bot', 'Slack'),
                            ],
                        ],
                        [
                            [
                                'url' => 'https://discord.gg/94WpSPJ',
                                'text' => Yii::t('bot', 'Discord'),
                            ],
                        ],
                        [
                            [
                                'url' => 'https://gitter.im/opensourcewebsite-org',
                                'text' => Yii::t('bot', 'Gitter'),
                            ],
                        ],
                        [
                            [
                                'url' => 'https://github.com/opensourcewebsite-org/opensourcewebsite-org',
                                'text' => Yii::t('bot', 'Source Code'),
                            ],
                        ],
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
                    ]),
                ]
            ),
        ];
    }
}
