<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;

/**
 * Class ReverseController
 *
 * @package app\modules\bot\controllers
 */
class ReverseController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex($message = '')
    {
        //TODO add reverse for $$message
        return $message ? $message : '';

        //return $this->render('index');
    }
}
