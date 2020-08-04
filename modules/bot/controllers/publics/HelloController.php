<?php

namespace app\modules\bot\controllers\publics;

use Yii;
use app\modules\bot\components\Controller;

/**
 * Class HelloController
 *
 * @package app\modules\bot\controllers\publics
 */
class HelloController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        return $this->getResponseBuilder()
            ->sendMessage(
                $this->render('index'),
                [
                        [
                            [
                                'url' => 'https://t.me/opensourcewebsite_bot',
                                'text' => Yii::t('bot', 'Bot'),
                            ],
                        ],
                        [
                            [
                                'url' => 'https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/DONATE.md',
                                'text' => '👼 ' . Yii::t('bot', 'Donate'),
                            ],
                            [
                                'url' => 'https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/CONTRIBUTING.md',
                                'text' => '👨‍🚀 ' . Yii::t('bot', 'Contribute'),
                            ],
                        ],
                ],
                true,
                [
                    'replyToMessageId' => $this->getMessage()->getMessageId(),
                ]
            )
            ->build();
    }
}
