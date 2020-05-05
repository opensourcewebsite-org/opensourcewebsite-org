<?php
/**
* ErrorAction class is used to extend yii\web\ErrorAction for more capabilities while output error description.
* For example, when users`s browser doesn`t support cookies, it`s preferably to warn user enable it, but not output "Bad Request"
*/
namespace app\actions;

use Yii;
use yii\web\BadRequestHttpException;

class ErrorAction extends \yii\web\ErrorAction
{

    /**
     * Renders a view that represents the exception.
     * @return string
     */
    protected function renderHtmlResponse()
    {
        $customResult = null;

        if ($this->isCookiesDisabledReason()) {
            $customResult = $this->controller->render('error/cookies-disabled', $this->getViewRenderParams());
        }

        return isset($customResult) ? $customResult : $this->controller->render($this->view ?: $this->id, $this->getViewRenderParams());
    }

    /**
    * Returns true if error caused by disabled cookies in users`s browser
    * @return boolean
    */
    private function isCookiesDisabledReason()
    {
        $cookiesDisabled = false;

        $badRequestException = new BadRequestHttpException();
        $badRequestExceptionCode = $badRequestException->statusCode;
        if ($this->getExceptionCode() == $badRequestExceptionCode) {
            if ($this->controller->enableCsrfValidation && Yii::$app->request->enableCsrfCookie && !Yii::$app->request->getCookies()->count) {
                $cookiesDisabled = true;
            }
        }

        return $cookiesDisabled;
    }
}
