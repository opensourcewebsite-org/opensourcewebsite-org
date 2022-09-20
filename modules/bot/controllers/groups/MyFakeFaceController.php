<?php

namespace app\modules\bot\controllers\groups;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use Yii;

/**
 * Class MyFakeFaceController
 *
 * @package app\modules\bot\controllers\groups
 */
class MyFakeFaceController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $user = $this->getTelegramUser();

        return $this->getResponseBuilder()
            ->sendPhoto(
                'https://thispersondoesnotexist.com/image?v=' . time(), //$user->getProviderUserId(),
                $this->render('index', [
                    'user' => $user,
                ]),
                [],
                [
                    'disablePreview' => true,
                    'disableNotification' => true,
                    //'replyToMessageId' => $this->getMessage()->getMessageId(),
                ]
            )
            ->build();
    }
}
