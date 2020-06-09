<ul class="nav nav-tabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link <?= Yii::$app->controller->action->id == 'index' ? 'active'
            : ''; ?>" href="/apiTesting/runner/index?id=1">Jobs</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= Yii::$app->controller->action->id == 'graphs' ? 'active'
            : ''; ?>" href="/apiTesting/runner/graphs?id=1">Graphs</a>
    </li>
    <li class="nav-item">
        <?=\app\modules\apiTesting\widgets\ProjectDropdownMenu::widget([
            'project' => $project
        ]); ?>
    </li>
</ul>
