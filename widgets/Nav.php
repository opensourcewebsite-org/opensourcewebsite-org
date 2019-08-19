<?php

namespace app\widgets;

use Yii;

/**
 * Nav renders a nav HTML component.
 */
class Nav extends \yii\bootstrap\Nav
{

    /**
     * Initializes the widget.
     */
    public function init()
    {
        if ($this->route === null && Yii::$app->controller !== null) {
            $this->route = Yii::$app->controller->getRoute();
        }
        if ($this->params === null) {
            $this->params = Yii::$app->request->getQueryParams();
        }
        if ($this->dropDownCaret === null) {
            $this->dropDownCaret = '<span class="caret"></span>';
        }
    }
}