<?php

namespace app\modules\bot\controllers\privates;

use \app\modules\bot\components\response\SendMessageCommand;
use app\modules\bot\components\Controller;

/**
 * Class My_profileController
 *
 * @package app\modules\bot\controllers
 */
class My_profileController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $update = $this->getUpdate();

        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('index', [
                    'profile' => $update->getMessage()->getFrom(),
                ]),
                [
                    'parseMode' => $this->textFormat,
                ]
            ),
        ];
    }
}
