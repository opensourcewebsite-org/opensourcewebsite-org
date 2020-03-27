<?php

namespace app\modules\bot\components;

use app\modules\bot\components\helpers\MessageText;
use app\modules\bot\components\response\ResponseBuilder;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
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
            $state->setName(self::createRoute("set_$property", [ $id ]));
            return ResponseBuilder::fromUpdate($update)
                ->answerCallbackQuery()
                ->sendMessage(
                    $this->render("set-$property")
                )
                ->build();
        }

        $propertyValue = $update->getMessage()->getText();
        $state->setIntermediateField($property, $propertyValue);

        $nextProperty = static::$properties[$currentPropertyIndex + 1];
        $isEndOfProperties = !isset($nextProperty);

        if (!$isCreateAction || $isEndOfProperties) {
            $result = $this->savePropertiesToModel($id);
            $state->reset();
            return $result;
        }

        $state->setName(self::createRoute("set_$nextProperty"));

        return ResponseBuilder::fromUpdate($update)
            ->answerCallbackQuery()
            ->sendMessage(
                $this->render("set-$nextProperty"),
                [
                    [
                        [
                            'text' => Yii::t('bot', 'Skip'),
                            'callback_data' => self::createRoute("set_$nextProperty"),
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
            ->sendMessage(
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
