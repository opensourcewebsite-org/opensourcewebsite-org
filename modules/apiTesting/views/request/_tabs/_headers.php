<?php
/**
 * @var $form \yii\widgets\ActiveForm
 * @var $model \app\modules\apiTesting\models\ApiTestRequest
 */

use app\components\helpers\Icon;
use unclead\multipleinput\MultipleInput;

?>

<?= $form->field($model, 'headers')->widget(MultipleInput::className(), [
    'min' => 0, // should be at least 2 rows
    'allowEmptyList' => true,
    'enableGuessTitle' => true,
    'addButtonPosition' => MultipleInput::POS_HEADER, // show add button in the header
    'iconMap' => [
        'glyphicons' => [
            'drag-handle' => 'glyphicon glyphicon-menu-hamburger',
            'remove' => 'glyphicon glyphicon-remove',
            'add' => 'glyphicon glyphicon-plus',
            'clone' => 'glyphicon glyphicon-duplicate',
        ],
        'fa' => [
            'drag-handle' => 'fa fa-bars',
            'remove' => 'fa fa-trash',
            'add' => 'fa fa-plus',
            'clone' => 'fa fa-files-o',
        ],
        'my-amazing-icons' => [
            'drag-handle' => 'my my-bars',
            'remove' => 'my my-times',
            'add' => 'my my-plus',
            'clone' => 'my my-files',
        ]
    ],
    'iconSource' => 'fa',
    'columns' => [
        [
            'name' => 'key',
            'title' => 'Key',
            'options' => [
                'placeholder' => 'Key'
            ]
        ],
        [
            'name' => 'value',
            'title' => 'Value',
            'options' => [
                'placeholder' => 'Value'
            ]
        ],
        [
            'name' => 'description',
            'title' => 'Description',
            'options' => [
                'placeholder' => 'Description (optional)'
            ]
        ]
    ],
    'addButtonOptions' => [
        'label' => Icon::ADD,
        'class' => 'btn btn-outline-success',
    ]
])->label(false);
?>
