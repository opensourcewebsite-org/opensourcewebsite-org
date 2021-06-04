<?php
declare(strict_types=1);

namespace app\modules\dataGenerator\components\generators;

use Yii;
use app\models\Company;
use app\models\CompanyUser;
use app\models\Currency;
use app\models\User;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use yii\db\ActiveRecord;
use yii\helpers\Console;

class CompanyUserFixture extends ARGenerator
{
    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    public function init()
    {
        if (!Currency::find()->exists()) {
            throw new ARGeneratorException('Impossible to create Resume - there are no Currency in DB!');
        }
        parent::init();
    }

    protected function factoryModel(): ?ActiveRecord
    {
        $user = $this->findUser();

        $companyModel = new Company([
            'name' => $companyName = $this->faker->company,
            'url' => $this->faker->url,
            'address' => $this->faker->address,
            'description' => $this->faker->realText(),
        ]);

        if (!$companyModel->save()) {
            var_dump($companyModel->errors);
            throw new ARGeneratorException("Can't save " . static::classNameModel() . "!\r\n");
        }

        $companyModel->link('users', $user, ['user_role' => CompanyUser::ROLE_OWNER]);

        return $companyModel;
    }

    /**
     * @throws ARGeneratorException
     */
    public function load(): ActiveRecord
    {
        return $this->factoryModel();
    }

    private function findUser(): ?User
    {
        /** @var User $user */
        $user = User::find()
            ->orderByRandAlt(1)
            ->one();

        if (!$user) {
            $class = self::classNameModel();
            $message = "\n$class: creation skipped. There is no Users\n";
            $message .= "It's not error - few iterations later new ExchangeOrder will be generated.\n";
            Yii::$app->controller->stdout($message, Console::BG_GREY);
        }

        return $user;
    }

    private function getRandomCurrency(): ?Currency
    {
        /** @var Currency|null $currency */
        $currency = Currency::find()
            ->select('id')
            ->where(['in', 'code', ['USD', 'EUR', 'RUB', 'ALL']])
            ->orderByRandAlt(1)
            ->one();

        return $currency;
    }

    private function printNoCurrencyError()
    {
        $class = self::classNameModel();
        $message = "\n$class: creation skipped. There is no Currencies yet.\n";
        $message .= "It's not error - few iterations later new ExchangeOrder will be generated.\n";
        Yii::$app->controller->stdout($message, Console::BG_GREY);
    }
}
