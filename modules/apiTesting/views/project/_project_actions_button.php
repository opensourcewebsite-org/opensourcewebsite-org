<?php
/**
 * @var $model \app\modules\apiTesting\models\ApiTestProject
 *
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
            ['label' => 'Edit', 'url' => ['/apiTesting/project/update', 'id' => $model->id]],
            ['label' => 'Team', 'url' => ['/apiTesting/team', 'id' => $model->id]],
            ['label' => 'Servers', 'url' => ['/apiTesting/server', 'id' => $model->id]],
        ],
    ]);
    ?>
</div>
