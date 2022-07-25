<?php

declare(strict_types=1);

namespace app\models\scenarios\Contact;

use app\models\Contact;
use app\models\ContactHasGroup;
use app\components\helpers\ArrayHelper;

class UpdateGroupsByIdsScenario
{
    private Contact $model;

    public function __construct(Contact $model)
    {
        $this->model = $model;
    }

    public function run()
    {
        $currentIds = $this->model->getGroupIds();

        $toDeleteIds = array_diff($currentIds, $this->model->groupIds);
        $toAddIds = array_diff($this->model->groupIds, $currentIds);

        foreach ($toAddIds as $id) {
            (new ContactHasGroup([
                'contact_id' => $this->model->id,
                'contact_group_id' => $id,
                ])
            )
            ->save();
        }

        if ($toDeleteIds) {
            ContactHasGroup::deleteAll([
                'and',
                ['contact_id' => $this->model->id],
                ['in', 'contact_group_id', $toDeleteIds],
            ]);
        }
    }
}
