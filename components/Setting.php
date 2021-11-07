<?php

namespace app\components;

use yii\base\BaseObject;
use app\models\Setting as SettingModel;

// Use Yii::$app->settings->{SETTING_NAME}
class Setting extends BaseObject
{
    /**
     * PHP getter magic method.
     *
     * @param string $name property name
     * @return mixed property value
     * @see getAttribute()
     */
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        $setting = SettingModel::find()
            ->where([
                'key' => $name,
            ])
            ->one();

        if (!isset($setting)) {
            $setting = new SettingModel();

            $setting->setAttributes([
                'key' => $name,
                'value' => $setting->getDefault($name),
                'updated_at' => time(),
            ]);

            $setting->save();
        }

        return $setting->value;
    }
}
