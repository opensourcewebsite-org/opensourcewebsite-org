<?php

namespace app\modules\bot\controllers\groups;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use Yii;

/**
 * Class BasicIncomeController
 *
 * @package app\modules\bot\controllers\groups
 */
class BasicIncomeController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        return $this->getResponseBuilder()
            ->sendMessage(
                $this->render('index'),
                [],
                [
                    'disablePreview' => true,
                    'disableNotification' => true,
                    'replyToMessageId' => $this->getMessage()->getMessageId(),
                ]
            )
            ->build();
    }
}
