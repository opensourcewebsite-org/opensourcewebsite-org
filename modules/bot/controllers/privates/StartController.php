<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\response\ResponseBuilder;
use Yii;
use app\modules\bot\components\Controller;

/**
 * Class StartController
 *
 * @package app\controllers\bot
 */
class StartController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('index'),
                [
                    [
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                        [
                            'callback_data' => LanguageController::createRoute(),
                            'text' => Emoji::LANGUAGE,
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
                            'text' => Yii::t('common', 'Source Code'),
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
                ]
            )
            ->build();
    }
}
