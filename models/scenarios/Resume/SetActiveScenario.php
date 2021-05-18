<?php
declare(strict_types=1);
namespace app\models\scenarios\Resume;

use Yii;
use app\models\Resume;

final class SetActiveScenario {

    private Resume $resume;

    private array $errors = [];

    public function __construct(Resume $resume)
    {
        $this->resume = $resume;
    }

    public function run(): bool
    {
        if ($this->validateLanguages() && $this->validateLocation() && $this->validateRadius()) {
            $this->resume->setActive();
            return true;
        }
        return false;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getResume(): Resume
    {
        return $this->resume;
    }

    private function validateLanguages(): bool
    {
        if (!$this->resume->getLanguages()->count()) {
            $this->errors['languages'] = Yii::t('app', 'You must have at least one language set in your profile');
            return false;
        }
        return true;
    }

    private function validateLocation(): bool
    {
        if (!$this->resume->isRemote()) {
            if (!($this->resume->location_lon && $this->resume->location_lat)) {
                $this->errors['location'] = Yii::t('app', 'Location should be set');
                return false;
            }
        }
        return true;
    }

    private function validateRadius(): bool
    {
        if (!$this->resume->isRemote()) {
            if (!$this->resume->search_radius) {
                $this->errors['location'] = Yii::t('app', 'Search Radius should be set');
                return false;
            }
        }
        return true;
    }
}

