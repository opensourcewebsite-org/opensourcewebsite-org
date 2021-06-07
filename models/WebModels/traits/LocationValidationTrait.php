<?php
namespace app\models\WebModels\traits;

use app\modules\bot\components\helpers\LocationParser;
use app\modules\bot\validators\LocationLatValidator;
use app\modules\bot\validators\LocationLonValidator;
use Yii;
use yii\web\JsExpression;

trait LocationValidationTrait {


    public function rules(): array
    {
        $mainValidators = parent::rules();
        $formName = strtolower($this->formName());
        $webSpecificValidators = [
            [
                'location',
                'required',
                'when' => function ($model) {
                    /** @var self $model */
                    if ( !$model->remote_on ) {
                        return true;
                    }
                    return false;
                },
                'whenClient' => new JsExpression("
                    function(attribute, value) {
                        return $('#offline-work-checkbox').prop('checked');
                    }
                ")
            ],
            [
                'location',
                function($attribute) {
                    [$lat, $lon] = (new LocationParser($this->$attribute))->parse();
                    if (!(new LocationLatValidator())->validateLat($lat) ||
                        !(new LocationLonValidator())->validateLon($lon)
                    ) {
                        $this->addError($attribute, Yii::t('app', 'Incorrect Location!'));
                    }
                }
            ],
        ];
        $ret = array_merge($mainValidators, $webSpecificValidators);
        return $ret;
    }
}
