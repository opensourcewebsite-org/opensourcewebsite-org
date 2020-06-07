<?php
/**
 * @var \app\modules\apiTesting\models\ApiTestProject $project
 */
use \yii\bootstrap4\Dropdown;

?>
<div class="dropdown">
    <a href="#" data-toggle="dropdown" class="btn btn-flat float-right dropdown-toggle">
        <b class="fas fa-cog"></b>
    </a>
    <?php
    echo Dropdown::widget([
        'items' => [
            ['label' => 'Projects', 'url' => ['/apiTesting/project']],
            ['label' => 'Team', 'url' => ['/apiTesting/team/index', 'id' => $project->id]],
            ['label' => 'Servers', 'url' => ['/apiTesting/server/index', 'id' => $project->id]],
            ['label' => 'Requests', 'url' => ['/apiTesting/project/testing', 'id' => $project->id]],
            ['label' => 'Jobs', 'url' => ['/apiTesting/job/index', 'id' => $project->id]],
            ['label' => 'Runner', 'url' => ['/apiTesting/runner/index', 'id' => $project->id]],
            ['label' => 'Labels', 'url' => ['/apiTesting/label/index', 'id' => $project->id]],
        ],
    ]);
    ?>
</div>
