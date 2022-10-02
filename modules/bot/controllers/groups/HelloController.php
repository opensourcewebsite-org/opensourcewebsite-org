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

        if ($chat->basic_commands_status == ChatSetting::STATUS_ON) {
            $commands = [
                '/basic_income',
                '/deposit_income',
                '/my_id',
                '/my_rank',
                '/my_stellar',
                '/fake_face',
                '/fake_cat',
                '/fake_art',
                '/fake_horse',
                '/chat_id',
                '/id',
            ];

            if ($chat->faq_status == ChatSetting::STATUS_ON) {
                $commands[] = '/faq';
            }

            if ($chat->stellar_status == ChatSetting::STATUS_ON) {
                $commands[] = '/stellar';
            }

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
