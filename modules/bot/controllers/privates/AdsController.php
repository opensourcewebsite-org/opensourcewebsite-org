<?php
namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\Controller;
use app\modules\bot\components\response\ResponseBuilder;

class AdsController extends Controller
{
	public function actionIndex()
	{
	    return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('index')
            )
            ->build();
	}
}
