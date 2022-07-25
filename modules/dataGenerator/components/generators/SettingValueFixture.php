<?php

declare(strict_types=1);

namespace app\modules\dataGenerator\components\generators;

use Yii;
use app\models\matchers\ModelLinker;
use app\models\SettingValue;
use app\models\Setting;
use app\models\User;
use yii\db\ActiveRecord;
use yii\helpers\Console;

class SettingValueFixture extends ARGenerator
{
    protected function factoryModel(): ?ActiveRecord
    {
        if (!($setting = $this->getRandomSetting())) {
            return null;
        }

        $model = new SettingValue();

        $model->setting_id = $setting->id;
        $model->value = $this->faker->unique()->randomNumber(2);

        return $model;
    }

    private function getRandomSetting(): ?Setting
    {
        /** @var Setting|null $setting */
        $setting = Setting::find()
            ->select('id')
            ->where([
                'not', ['value' => null],
            ])
            ->orderByRandAlt(1)
            ->one();

        if (!$setting) {
            $message = "\n" . self::classNameModel() . ': creation skipped. There is no Settings.' . "\n";
            Yii::$app->controller->stdout($message, Console::BG_GREY);
        }

        return $setting;
    }
}
