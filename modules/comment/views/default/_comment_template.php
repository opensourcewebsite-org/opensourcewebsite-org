<?php

use yii\helpers\Html;
use app\modules\comment\models\MoqupComment;

/**
 * @var $model
 * @var $item
 * @var $level int
 * @var $related string
 * @var $material string
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

$label = Html::tag('div', Html::encode($item->message), ['id' => 'textMessage' . $item->id]) ;

//if ($level < 2) {

    $showReplies = $this->render('_show_replies', [
        'related' => $related,
        'inside' => $item->id,
        'model' => $model,
        'material' => $material,
        'count' => $item->count,
    ]);

    /*$showReplies = Html::tag(
        'a',
        "replies ({$item->count})",
        [
            'class'         => 'text-muted show-reply',
            'data-toggle'   => 'collapse',
            'data-inside'   => $item->id,
            'data-related'  => $related,
            'data-model'    => $model,
            'data-route'    => Url::to(['/comment/default/index']),
            'data-material' => $material,
            'href'          => '#collapseReply' . $item->id,
        ]
    ) . Html::tag('div', '', ['id' => 'collapseReply' . $item->id]);*/
//}

$showControlBtns = '';
if (Yii::$app->user->id == $item->user_id) {
    $showControlBtns =
        Html::tag('span',
            Html::tag('a', '<i class="fas fa-edit mx-1"></i>',
                [
                    'href' => '',
                    'data-toggle' => 'modal',
                    'data-target' => '#updateModal' . $item->id
                ]
            ) .
            $this->render('_drop_btn', [
                'model' => $model,
                'item' => $item,
                'material'  => $material,
                'related'  => $related,
            ]),
            ['class' => 'float-right']
        ) . $this->render('_update_form', [
            'model'  => $item,
            'related'  => $related,
            'modelClass'  => $model,
            'material'  => $material,
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

$template = $user . $showControlBtns . $label .
    ($level < 2 ? $replyBtn : '') .
    ($level < 2 ? Html::tag('div', '<br />' . $this->render('_reply_form', [
            'parent' => $item->id,
            'related'  => $related,
            'modelClass'  => $model,
            'material'  => $material,
        ]), [
            'id'          => 'collapse' . $item->id,
            'class'       => 'collapse',
            'data-parent' => '#accordion',
        ]) . '<br />' : '') .
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
        'id' => 'comment' . $item->id,
        'class' => 'card-comment',
    ]
);
