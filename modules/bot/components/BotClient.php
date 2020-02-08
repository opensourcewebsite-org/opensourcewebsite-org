<?php

namespace app\modules\bot\components;

use app\modules\bot\Module;
use yii\base\Component;
use app\modules\bot\models\BotClient as BotClientModel;

/**
 * Class BotClient
 *
 * @package app\modules\bot\components
 */
class BotClient extends Component
{
    /**
     * @var null|BotClient
     */
    protected $_model = null;

    /**
     * @return null|BotClientModel
     */
    public function getModel()
    {
        if (is_null($this->_model)) {
            $botApi = Module::getInstance()->botApi;
            if ($botApi->getMessage()) {
                $clientData = $botApi->getMessage()->getFrom();

                $this->_model = BotClientModel::find()
                    ->where(['provider_user_id' => $clientData->getId()])
                    ->one();
            }
        }

        return $this->_model;
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        $model = $this->getModel();

        return ($model) ? $model->provider_user_id : null;
    }

    /**
     * @return int|null
     */
    public function getProviderId()
    {
        $model = $this->getModel();

        return ($model) ? $model->id : null;
    }
}
