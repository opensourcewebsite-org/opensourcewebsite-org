<?php

namespace app\modules\bot\controllers\groups;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use app\models\User as GlobalUser;

/**
 * Class MyRankController
 *
 * @package app\modules\bot\controllers\groups
 */
class MyRankController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $user = $this->getUser();

        $rank = $user->getRank();
        $totalRank = GlobalUser::getTotalRank();

        $params = [
            'ranking' => [$rank, $totalRank],
        ];

        return $this->getResponseBuilder()
        ->sendMessage(
            $this->render('index', $params),
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
