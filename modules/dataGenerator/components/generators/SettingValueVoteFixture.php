<?php

declare(strict_types=1);

namespace app\modules\dataGenerator\components\generators;

use Yii;
use app\models\matchers\ModelLinker;
use app\models\SettingValueVote;
use app\models\SettingValue;
use app\models\Setting;
use app\models\User;
use yii\db\ActiveRecord;
use yii\helpers\Console;

class SettingValueVoteFixture extends ARGenerator
{
    protected function factoryModel(): ?ActiveRecord
    {
        if ($user = $this->getRandomUser()) {
            return null;
        }

        if (!($settingValue = $this->getRandomSettingValue())) {
            return null;
        }

        $settingValueVote = $settingValue->setVoteByUserId($user->id);

        return $settingValueVote;
    }

    private function getRandomSettingValue(): ?SettingValue
    {
        /** @var SettingValue|null $settingValue */
        $settingValue = SettingValue::find()
            ->orderByRandAlt(1)
            ->one();

        if (!$settingValue) {
            $message = "\n" . self::classNameModel() . ': creation skipped. There is no SettingValues.' . "\n";
            Yii::$app->controller->stdout($message, Console::BG_GREY);
        }

        return $settingValue;
    }
}
