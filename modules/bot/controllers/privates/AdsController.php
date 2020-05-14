<?php
namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller;


class AdsController extends Controller
{
    public function actionIndex()
    {
        return $this->getResponseBuilder()($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('index')
            )
            ->build();
    }
}
