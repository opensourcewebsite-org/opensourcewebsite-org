<?php

namespace app\modules\bot\controllers\groups;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use app\modules\bot\models\ChatSetting;
use Yii;

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
        $chat = $this->getTelegramChat();

        if ($chat->isBasicCommandsOn()) {
            $commands = [
                '/my_id',
                '/my_rank',
                '/chat_id',
                '/id',
            ];

            sort($commands);
        } else {
            $commands = [];
        }

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
                            'text' => Yii::t('bot', 'Add the bot to group or channel'),
                        ],
                    ],
                    [
                        [
                            'url' => ExternalLink::getGithubDonationLink(),
                            'text' => Emoji::DONATE . ' ' . Yii::t('bot', 'Donate'),
                        ],
                        [
                            'url' => ExternalLink::getGithubContributionLink(),
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
