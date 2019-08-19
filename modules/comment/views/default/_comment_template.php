<?php

use yii\helpers\Html;
use app\modules\comment\models\MoqupComment;

/**
 * @var $model
 * @var $item
 * @var $level int
 * @var $related string
 * @var $material string
 * @var $mainForm bool
 */

$user = Html::tag(
    'span',
    MoqupComment::showUserName($item->user) .
    Html::tag(
        'span',
        Yii::$app->formatter->asRelativeTime($item->created_at),
        ['class' => 'text-muted ml-2']
    ),
    ['class' => 'username']
);

$label = Html::tag('div', Html::encode($item->message), ['id' => 'textMessage' . $item->id]);

$showReplies = $this->render('_show_replies', [
    'related'  => $related,
    'inside'   => $item->id,
    'model'    => $model,
    'material' => $material,
    'count'    => $item->count,
]);


$showControlBtns = '';
if (Yii::$app->user->id == $item->user_id) {
    $showControlBtns =
        Html::tag(
            'span',
            Html::tag(
                'a',
                '<i class="fas fa-edit mx-1"></i>',
                [
                    'href'        => '',
                    'data-toggle' => 'modal',
                    'data-target' => '#updateModal' . $item->id,
                ]
            ) .
            $this->render('_drop_btn', [
                'model'    => $model,
                'item'     => $item,
                'material' => $material,
                'related'  => $related,
                'level'    => $level,
            ]),
            ['class' => 'float-right']
        ) . $this->render('_update_form', [
            'model'      => $item,
            'related'    => $related,
            'modelClass' => $model,
            'material'   => $material,
        ]);
}


$replyBtn = Html::tag(
    'a',
    'REPLY',
    [
        'href'        => '',
        'class'       => 'text-secondary',
        'data-toggle' => 'collapse',
        'data-target' => '#collapse' . $item->id,
    ]
);

$replyBtnInside = '';
if ($level > 1) {
    $replyBtnInside = Html::tag(
        'a',
        'REPLY',
        [
            'href'           => '',
            'class'          => 'replyBtnInside text-secondary',
            'data-parent_id' => $item->parent_id,
            'data-id'        => $item->id,
            'data-toggle'    => 'collapse',
            'data-target'    => '#collapse' . $item->id,
        ]
    ) . Html::tag(
        'div',
        '<br />' . Html::tag(
            'div',
            '',
            [
                'id' => 'replyBtnInside_' . $item->parent_id . '_' . $item->id,
            ]
        ),
        [
            'id'          => 'collapse' . $item->id,
            'class'       => 'collapse',
            'data-parent' => '#accordion',
        ]
    );
}

$optionReplyForm = [
    'parent'     => $item->id,
    'related'    => $related,
    'modelClass' => $model,
    'material'   => $material,
    'mainForm'   => false,
];

$template = $user . $showControlBtns . $label .
    ($level < 2 ? $replyBtn : '') . $replyBtnInside .
    ($level < 2 ? Html::tag(
        'div',
        '<br />' .
        (($mainForm)
            ? $this->renderAjax(
                '_reply_form',
                $optionReplyForm
            )
            : $this->render(
                '_reply_form',
                $optionReplyForm
            )),
        [
            'id'          => 'collapse' . $item->id,
            'class'       => 'collapse',
            'data-parent' => '#accordion',
        ]
    ) . '<br />' : '') .
    $showReplies;

echo Html::tag(
    'div',
    Html::img(
        'https://secure.gravatar.com/avatar/b4284e48ee666373b3e7adee3cbd0958?r=g&amp;s=20',
        ['class' => 'img-fluid img-circle img-sm']
    ) .
    Html::tag(
        'div',
        $template,
        ['class' => 'comment-text']
    ),
    [
        'id'    => 'comment' . $item->id,
        'class' => 'card-comment',
    ]
);
