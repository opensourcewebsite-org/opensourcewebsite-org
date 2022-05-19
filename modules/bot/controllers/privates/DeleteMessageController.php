<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;

/**
 * Class DeleteMessageController
 *
 * @package app\modules\bot\controllers\privates
 */
class DeleteMessageController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        if ($this->getUpdate()->getCallbackQuery()) {
            if ($this->getMessage()->canDelete()) {
                return $this->getResponseBuilder()
                    ->deleteMessage()
                    ->build();
            } else {
                return $this->getResponseBuilder()
                    ->answerCallbackQuery(
                        $this->render('index'),
                        true
                    )
                    ->build();
            }
        }

        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }
}
