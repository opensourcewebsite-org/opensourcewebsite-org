<?php

declare(strict_types=1);

namespace app\widgets\ContactWidget;

use app\models\User;
use app\widgets\base\Widget;

class ContactWidget extends Widget
{
    public User $user;

    public ?string $name = 'contactWidget';

    public function run(): string
    {
        return $this->render('view', ['user' => $this->user, 'options' => $this->options]);
    }
}
