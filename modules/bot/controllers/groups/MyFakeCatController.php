<?php

namespace app\modules\bot\controllers\groups;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use Yii;

/**
 * Class MyFakeCatController
 *
 * @package app\modules\bot\controllers\groups
 */
class MyFakeCatController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $domain = 'thiscatdoesnotexist.com';
        $url = 'https://' . $domain . '/?v=' . time();

        return $this->getResponseBuilder()
            ->editPhotoOrSendPhoto(
                $url,
                $this->render('index', [
                    'domain' => $domain,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute(),
                            'text' => Emoji::REFRESH,
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
