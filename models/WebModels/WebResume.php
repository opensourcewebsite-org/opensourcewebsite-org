<?php
declare(strict_types=1);

namespace app\models\WebModels;

use app\models\Resume;
use app\models\WebModels\traits\LocationValidationTrait;

class WebResume extends Resume
{
    use LocationValidationTrait;
}
