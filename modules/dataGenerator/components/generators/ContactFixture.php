<?php

namespace app\modules\dataGenerator\components\generators;

use Yii;
use app\models\Contact;
use app\models\queries\ContactQuery;
use app\models\User;
use yii\db\ActiveRecord;
use yii\helpers\Console;
use yii\validators\NumberValidator;

class ContactFixture extends ARGenerator
{
    /**
     * @return Contact|null
     * @throws ARGeneratorException
     */
    protected function factoryModel(): ?ActiveRecord
    {
        if (!$users = $this->getRandomUsers2()) {
            return null;
        }

        $model = new Contact();

        $model->user_id = $users[0];
        $model->link_user_id = $users[1];
        $model->name = $this->faker->name;
        $model->is_real = (int)$this->faker->boolean();
        $model->relation = $this->faker->numberBetween(0, 2);

        $model->vote_delegation_priority = $this->faker
            ->optional(0.5, 0)
            ->numberBetween(0, 10);

        $model->debt_redistribution_priority = $this->faker
            ->optional(0.5, 0)
            ->numberBetween(0, 10);

        return $model;
    }

    /**
     * @return array
     * @throws ARGeneratorException
     */
    protected function getRandomUsers2(): array
    {
        $usersCount = User::find()->active()->count();

        /** @var array $usersFrom users, who can has additional Contact */
        $usersFrom = User::find()
            ->select('user.id, count(contact.id) as n_contact')
            ->active()
            ->joinWith(['counterContacts' => static function (ContactQuery $query) {
                $query->user('andOnCondition');
            }])
            ->groupBy('user.id')
            ->having('n_contact < :nUser', [':nUser' => $usersCount - 1])
            ->orderBy('n_contact')
            ->limit(30)
            ->column();

        if (empty($usersFrom)) {
            $message = "\n" . self::classNameModel() . ': creation skipped. Either no active User, or all Users have full set of Contacts.' . "\n";
            Yii::$app->controller->stdout($message, Console::BG_GREY);

            return [];
        }

        $userIdFrom = $this->faker->randomElement($usersFrom);

        /** @var array $usersTo user, with whom $userIdFrom has no contact yet */
        $usersTo = User::find()
            ->select('user.id')
            ->active()
            ->joinWith(['contacts' => static function (ContactQuery $query) use ($userIdFrom) {
                $query->userOwner($userIdFrom, 'andOnCondition');
            }])
            ->andWhere('contact.id IS NULL AND user.id <> :userIdFrom', [':userIdFrom' => $userIdFrom])
            ->limit(30)
            ->column();

        if (empty($usersTo)) {
            throw new ARGeneratorException("Expected to find \$userIdTo. \$userIdFrom='$userIdFrom'");
        }

        $userIdTo = $this->faker->randomElement($usersTo);

        return [$userIdFrom, $userIdTo];
    }
}
