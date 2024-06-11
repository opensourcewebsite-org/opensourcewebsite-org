<?php

namespace app\modules\api\controllers;

use app\modules\api\components\Controller;
use Yii;

/**
 * Error controller for the `api` module
 */
class ErrorController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        $exception = Yii::$app->errorHandler->exception;

        if ($exception !== null) {
            return $this->render('index', ['exception' => $exception]);
        }

        return $this->render('index');
    }
}
