<?php

namespace app\modules\bot\controllers\groups;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;

/**
 * Class MyFakeArtController
 *
 * @package app\modules\bot\controllers\groups
 */
class MyFakeArtController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $user = $this->getTelegramUser();

        return $this->getResponseBuilder()
            ->sendPhoto(
                'https://thisartworkdoesnotexist.com/?v=' . time(), //$user->getProviderUserId(),
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
