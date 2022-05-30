<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use Yii;

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
