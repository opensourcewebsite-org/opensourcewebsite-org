<?php

use yii\bootstrap4\Tabs;

/**
 * @var $model \app\modules\apiTesting\models\ApiTestRequest
 */
?>
<?= Tabs::widget([
    'items' => [
        [
            'label' => 'Headers',
            'content' => $this->render('_tabs/_headers', [
                'model' => $model,
                'form' => $form
            ]),
            'active' => true
        ],
        [
            'label' => 'Body',
            'content' => $this->render('_tabs/_body', [
                'model' => $model,
                'form' => $form
            ]),
            'active' => false
        ],
        [
            'label' => 'Response',
            'content' => $this->render('_tabs/_test', [
                'model' => $model,
                'form' => $form
            ]),
            'active' => false
        ]
    ]
]); ?>
