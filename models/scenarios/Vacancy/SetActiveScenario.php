<?php
declare(strict_types=1);

namespace app\models\scenarios\Vacancy;

use Yii;
use app\models\Vacancy;

final class SetActiveScenario
{
    private Vacancy $vacancy;
    private array $errors = [];

    public function __construct(Vacancy $vacancy)
    {
        $this->vacancy = $vacancy;
    }

    public function run(): bool
    {
        if ($this->validateLanguages() && $this->validateLocation()) {
            $this->vacancy->setActive();
            return true;
        }

        return false;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getVacancy(): Vacancy
    {
        return $this->vacancy;
    }

    private function validateLanguages(): bool
    {
        if (!$this->vacancy->getLanguagesWithLevels()->count()) {
            $this->errors['languages'] = Yii::t('app', 'You must have at least one language for Vacancy');
            return false;
        }

        return true;
    }

    private function validateLocation(): bool
    {
        if (!$this->vacancy->isRemote()) {
            if (!($this->vacancy->location_lon && $this->vacancy->location_lat)) {
                $this->errors['location'] = Yii::t('app', 'Location should be set');
                return false;
            }
        }

        return true;
    }
}
