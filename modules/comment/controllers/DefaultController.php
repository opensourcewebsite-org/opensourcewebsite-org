<?php

namespace app\modules\comment\controllers;

use yii\web\Controller;
use yii\helpers\Html;
use Yii;

/**
 * Default controller for the `admin` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     *
     * @return string
     */
    public function actionIndex($parent_id, $material, $related, $model)
    {
        //TODO check access
        $model = $model::find()->where([
            'parent_id' => (int)$parent_id,
            $related    => $material,
        ])->with('user')->all();


        $html = '<br />';
        foreach ($model as $item) {
            $html .= $this->renderPartial('_comment_template', [
                'item'     => $item,
                'related'  => $related,
                'material' => $material,
                'level'    => 2,
            ]);
        }

        return $html;
    }
}
