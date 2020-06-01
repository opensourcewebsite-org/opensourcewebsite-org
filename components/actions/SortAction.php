<?php


namespace app\components\actions;

use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\web\NotFoundHttpException;

class SortAction extends Action
{
    const MOVE_NEXT = 'moveNext';
    const MOVE_PREV = 'movePrev';

    /**
     * @var ActiveRecord
     */
    public $modelClass;
    public $method;
    public $returnUrl = ['index'];

    /**
     * @param $id
     *
     * @return \yii\web\Response
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\NotFoundHttpException
     */
    public function run($id)
    {
        $model = $this->findModel($id);
        $model->{$this->method}();

        return $this->controller->redirect($this->returnUrl);
    }

    /**
     * @param $id
     *
     * @return \yii\db\ActiveRecord
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (!class_exists($this->modelClass)) {
            throw new InvalidConfigException('Model class not found');
        }

        if (!$model = $this->modelClass::findOne($id)) {
            throw new NotFoundHttpException();
        }

        return $model;
    }
}
