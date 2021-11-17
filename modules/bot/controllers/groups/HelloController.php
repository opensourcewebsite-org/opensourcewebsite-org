<?php

namespace app\modules\bot\controllers\groups;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use app\modules\bot\models\ChatSetting;

/**
 * Class HelloController
 *
 * @package app\modules\bot\controllers\groups
 */
class HelloController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $commands = [
            '/basic_income',
            '/my_id',
            '/my_rank',
            '/my_stellar',
            '/my_fake_face',
            '/my_fake_cat',
            '/my_fake_art',
        ];

        $chat = $this->getTelegramChat();

        if ($chat->faq_status == ChatSetting::STATUS_ON) {
            $commands[] = '/faq';
        }

        if ($chat->stellar_status == ChatSetting::STATUS_ON) {
            $commands[] = '/stellar';
        }

        sort($commands);

        return $this->getResponseBuilder()
            ->sendMessage(
                $this->render('index', [
                    'commands' => $commands,
                ]),
                [
                    [
                        [
                            'url' => ExternalLink::getBotLink(),
                            'text' => Yii::t('bot', 'BOT'),
                        ],
                    ],
                    [
                        [
                            'url' => ExternalLink::getBotToAddGroupLink(),
                            'text' => Yii::t('bot', 'Add the bot to your group'),
                        ],
                    ],
                    [
                        [
                            'url' => 'https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/DONATE.md',
                            'text' => Emoji::DONATE . ' ' . Yii::t('bot', 'Donate'),
                        ],
                        [
                            'url' => 'https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/CONTRIBUTING.md',
                            'text' => Emoji::CONTRIBUTE . ' ' . Yii::t('bot', 'Contribute'),
                        ],
                    ],
                ],
                [
                    'disablePreview' => true,
                    'disableNotification' => true,
                    'replyToMessageId' => $this->getMessage()->getMessageId(),
                ]
            )
            ->build();
    }
}
