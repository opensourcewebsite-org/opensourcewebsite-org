<?php
use yii\bootstrap4\Tabs;

/**
 * @var \yii\db\ActiveRecord $model
 */
?>

<?= Tabs::widget([
    'items' => [
        [
            'label' => 'Project',
            'url' => [$model->isNewRecord ? '/apiTesting/project/create' : '/apiTesting/project/update', 'id' => $model->id],
            'active' => Yii::$app->controller->id == "project"
        ],
        [
            'label' => 'Team',
            'url' => ['/apiTesting/team/index', 'id' => $model->id],
            'disabled' => $model->isNewRecord,
            'active' => Yii::$app->controller->id == "team"
        ]
    ]
]);
?>
