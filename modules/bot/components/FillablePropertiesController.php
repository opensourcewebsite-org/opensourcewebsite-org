<?php

namespace app\modules\bot\components;

use app\modules\bot\components\helpers\MessageText;
use app\modules\bot\components\response\ResponseBuilder;
use phpDocumentor\Reflection\Types\Static_;
use Yii;
use yii\base\InvalidRouteException;
use yii\db\ActiveRecord;

abstract class FillablePropertiesController extends Controller
{
    protected static $properties = [];

    /**
     * @param $property
     * @param null $id
     * @return array
     * @throws InvalidRouteException
     */
    public function actionSetProperty($property, $id = null)
    {
        $currentPropertyIndex = array_search($property, static::$properties);
        if ($currentPropertyIndex === false) {
            throw new InvalidRouteException("Invalid property '$property'");
        }

        $isCreateAction = is_null($id);
        $update = $this->getUpdate();
        $state = $this->getState();

        if (is_null($update->getMessage())) {
            $state->setName(self::createRoute("set-property", [
                'id' => $id,
                'property' => $property,
            ]));
            return ResponseBuilder::fromUpdate($update)
                ->answerCallbackQuery()
                ->editMessageTextOrSendMessage(
                    $this->render("set-$property")
                )
                ->build();
        }

        $propertyValue = $update->getMessage()->getText();
        $state->setIntermediateField($property, $propertyValue);

        $isEndOfProperties = count(static::$properties) == $currentPropertyIndex + 1;

        if (!$isCreateAction || $isEndOfProperties) {
            $result = $this->savePropertiesToModel($id);
            $state->reset();
            return $result;
        }

        $nextProperty = static::$properties[$currentPropertyIndex + 1];
        $state->setName(self::createRoute("set-property", [
            'property' => $nextProperty,
        ]));

        return ResponseBuilder::fromUpdate($update)
            ->answerCallbackQuery()
            ->editMessageTextOrSendMessage(
                $this->render("set-$nextProperty"),
                [
                    [
                        [
                            'text' => Yii::t('bot', 'Skip'),
                            'callback_data' => self::createRoute("set-property", [
                                'property' => $nextProperty,
                            ]),
                        ],
                    ],
                ]
            )
            ->build();
    }

    protected function savePropertiesToModel($id = null)
    {
        $update = $this->getUpdate();
        $state = $this->getState();

        $model = $this->getModel($id);

        foreach (static::$properties as $property) {
            $propertyValue = $state->getIntermediateField($property, null);
            if (!is_null($propertyValue)) {
                $model->{$property} = $propertyValue;
            }
        }

        if ($model->save()) {
            return $this->afterSave($model, is_null($id));
        }

        return ResponseBuilder::fromUpdate($update)
            ->editMessageTextOrSendMessage(
                new MessageText(json_encode($model->getErrors()))
            )
            ->build();
    }

    /**
     * @param ActiveRecord $model
     * @param bool $isNew
     * @return array
     */
    abstract protected function afterSave(ActiveRecord $model, bool $isNew);

    /**
     * @param $id
     * @return ActiveRecord
     */
    abstract protected function getModel($id);
}
