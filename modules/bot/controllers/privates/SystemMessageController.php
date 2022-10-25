<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller;
use Yii;

/**
 * Class SystemMessageController
 *
 * @package app\modules\bot\controllers\privates
 */
class SystemMessageController extends Controller
{
    // TODO block/unblock bot by user
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
