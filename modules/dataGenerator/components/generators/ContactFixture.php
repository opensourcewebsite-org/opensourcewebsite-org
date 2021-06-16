<?php

namespace app\modules\dataGenerator\components\generators;

use Yii;
use app\models\Contact;
use app\models\queries\ContactQuery;
use app\models\User;
use Faker\Provider\en_US\Person;
use yii\db\ActiveRecord;
use yii\helpers\Console;
use yii\validators\NumberValidator;

class ContactFixture extends ARGenerator
{
    protected function providers(): array
    {
        return [Person::class];
    }

    /**
     * @return Contact|null
     * @throws ARGeneratorException
     */
    protected function factoryModel(): ?ActiveRecord
    {
        $users = $this->findUsers();

        if (empty($users)) {
            return null;
        }

        $model = new Contact();

        $model->user_id = $users[0];
        $model->link_user_id = $users[1];
        $model->name = self::getFaker()->name;
        $model->is_real = (int)$this->faker->boolean();
        $this->setDRP($model);

        return $model;
    }

    /**
     * @return array
     * @throws ARGeneratorException
     */
    private function findUsers(): array
    {
        $userQty = User::find()->active()->count();

        /** @var array $usersFrom users, who can has additional Contact */
        $usersFrom = User::find()
            ->select('user.id, count(contact.id) as n_contact')
            ->joinWith(['contactsFromMe' => static function (ContactQuery $query) {
                $query->virtual(false, 'andOnCondition');
            }])
            ->active()
            ->groupBy('user.id')
            ->having('n_contact < :nUser', [':nUser' => $userQty - 1])
            ->orderBy('n_contact')
            ->limit(30)
            ->column();

        if (empty($usersFrom)) {
            $class = self::classNameModel();
            $message = "\n$class: creation skipped. ";
            $message .= "Either no active User, or all Users have full set of Contacts.\n";
            $message .= "\nIt's not error - few iterations later new User will be generated.\n";
            Yii::$app->controller->stdout($message, Console::BG_GREY);

            return [];
        }

        $userIdFrom = self::getFaker()->randomElement($usersFrom);

        /** @var array $usersTo user, with whom $userIdFrom has no contact yet */
        $usersTo = User::find()
            ->select('user.id')
            ->joinWith(['contactsToMe' => static function (ContactQuery $query) use ($userIdFrom) {
                $query->userOwner($userIdFrom, 'andOnCondition');
            }])
            ->active()
            ->andWhere('contact.id IS NULL AND user.id <> :userIdFrom', [':userIdFrom' => $userIdFrom])
            ->limit(30)
            ->column();

        if (empty($usersTo)) {
            throw new ARGeneratorException("Expected to find \$userIdTo. \$userIdFrom='$userIdFrom'");
        }
        $userIdTo = self::getFaker()->randomElement($usersTo);

        return [$userIdFrom, $userIdTo];
    }

    private function setDRP(Contact $model): void
    {
        $min = 0;
        $max = 255;

        foreach ($model->activeValidators as $validator) {
            if (
                in_array('debt_redistribution_priority', $validator->attributes, true) &&
                $validator instanceof NumberValidator
            ) {
                $min = $validator->min;
                $max = $validator->max;
                break;
            }
        }

        $model->debt_redistribution_priority = self::getFaker()
            ->optional(0.5, Contact::DEBT_REDISTRIBUTION_PRIORITY_DENY)
            ->numberBetween($min, $max);
    }
}
