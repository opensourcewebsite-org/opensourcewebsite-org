<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;

/**
 * Class MessageController
 *
 * @package app\modules\bot\controllers\privates
 */
class MessageController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }
}
