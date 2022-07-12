<?php

namespace app\modules\bot\components\crud\services;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\crud\CrudController;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\ActiveRecord;
use phpDocumentor\Reflection\Types\Boolean;

/**
 * Class ModelRelationService
 *
 * @package app\modules\bot\components\crud\services
 */
class CreateRecordService
{


    /** @var Controller */
    public $controller;
    
    /**
     * 
     * @param Model $model
     * @return unknown
     */
    private function beforeCreateRecord(Model $model)
    {
        // area for before save validations.
        
        return $model->beforeSave();
    }
    
    /**
     * 
     * @param Model $model
     * @param Boolean $validate
     * @return unknown
     */
    public function createRecord(Model $model,Boolean $validate = true)
    {   
       $this->beforeCreateRecord($model);
       $response = $model->save($validate);
       $this->afterCreateRecord($model);    
       return $response;
    }
    
    /**
     * 
     * @param Model $model
     * @return unknown
     */
    private function afterCreateRecord(Model $model)
    {
        // area for after save validations.
        
        return $model->afterSave();
    }
}
