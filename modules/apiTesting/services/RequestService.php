<?php

namespace app\modules\apiTesting\services;

use app\modules\apiTesting\models\ApiTestRequest;
use app\modules\apiTesting\models\ApiTestRequestHeaders;
use app\modules\apiTesting\models\ApiTestRequestLabel;
use yii\base\Component;

class RequestService extends Component
{
    public function save(ApiTestRequest $model)
    {
        $model->updated_by = \Yii::$app->user->id;
        return $model->save();
    }

    public function update(ApiTestRequest $model)
    {
        $model->updated_by = \Yii::$app->user->id;
        $this->updateHeaders($model);
        $this->updateLabels($model);
        return $model->save();
    }

    private function updateHeaders(ApiTestRequest $model)
    {
        $this->flushHeaders($model);
        $model->headers = [];
        $this->saveHeaders($model);
    }

    private function saveHeaders(ApiTestRequest $model)
    {
        foreach ($model->headers as $header) {
            $headerModel = new ApiTestRequestHeaders();
            $headerModel->load($header, '');
            $model->link('apiTestRequestHeaders', $headerModel);
        }
    }

    private function flushHeaders(ApiTestRequest $model)
    {
        foreach ($model->apiTestRequestHeaders as $header) {
            $header->delete();
        }
    }

    private function updateLabels(ApiTestRequest $model)
    {
        $this->flushLabels($model);
        $this->saveLabels($model);
    }

    private function saveLabels(ApiTestRequest $model)
    {
        foreach ($model->labelIds as $labelId) {
            $requestLabelModel = new ApiTestRequestLabel([
                'request_id' => $model->id,
                'label_id' => $labelId
            ]);
            $requestLabelModel->save();
        }
    }

    private function flushLabels(ApiTestRequest $model)
    {
        foreach ($model->apiTestRequestLabels as $label) {
            $label->delete();
        }
    }
}
