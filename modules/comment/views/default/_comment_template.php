<?php

use yii\helpers\Html;
use app\modules\comment\models\MoqupComment;
use yii\helpers\Url;

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

$label = Html::encode($item->message);

if ($level < 2) {
    $showReplies = Html::tag(
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
    ) . Html::tag('div', '', ['id' => 'collapseReply' . $item->id]);
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

$template = $user . $label . '<br />' .
    ($level < 2 ? $replyBtn : '') .
    ($level < 2 ? Html::tag('div', $this->render('_reply_form', [
            'parent' => $item->id,
            'model'  => $item,
        ]), [
            'id'          => 'collapse' . $item->id,
            'class'       => 'collapse',
            'data-parent' => '#accordion',
        ]) . '<br />' : '') .
    (
    ($item->count > 0)
        ?
        $showReplies
        :
        ''
    );

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
        'class' => 'card-comment',
    ]
);
