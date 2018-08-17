<?php

/* @var $this yii\web\View */

use yii\helpers\Html;

?>

<p>
    OpenSourceWebsite (OSW) <?= Yii::t('app', 'is an open source website for the world\'s largest online social community. The Website is managed by users. The Website is updated after each code change. So everyone can influence how the Website works.') ?>
</p>

<p>
    <?= Yii::t('app', 'The Website will look like and what functions it will have depends only on the users\' decisions. This Website supports and develops ideas of E-democracy (also known as digital democracy or Internet democracy).') ?>
</p>

<p>
    <?= Yii::t('app', 'We want the Web to be even better. We want more people using it for more things. We want it to continue to drive creativity, education and economic growth. And we want to empower people to help shape the Web as they move more of their lives online.') ?>
</p>

<p>
    <?php if (!Yii::$app->user->isGuest && !Yii::$app->user->identity->is_email_confirmed) : ?>
    <?php echo Html::a('Resend Confirmation Email', ['site/resend-confirmation-email'], ['class' => 'btn btn-primary']); ?>
    <?php endif; ?>
</p>

<h4>Technologies</h4>

<ul>
    <li><?= Html::a('PHP 7', 'https://en.wikipedia.org/wiki/PHP') ?></li>
    <li><?= Html::a('Yii 2 PHP Framework', 'https://en.wikipedia.org/wiki/Yii') ?></li>
    <li><?= Html::a('JavaScript', 'https://en.wikipedia.org/wiki/JavaScript') ?></li>
    <li><?= Html::a('Vue.js 2 JavaScript Framework', 'https://en.wikipedia.org/wiki/Vue.js') ?></li>
    <li><?= Html::a('HTML 5', 'https://en.wikipedia.org/wiki/HTML') ?></li>
    <li><?= Html::a('CSS 3', 'https://en.wikipedia.org/wiki/Cascading_Style_Sheets') ?></li>
    <li><?= Html::a('MySQL 5', 'https://en.wikipedia.org/wiki/MySQL') ?></li>
    <li><?= Html::a('Let\'s Encrypt', 'https://en.wikipedia.org/wiki/Let%27s_Encrypt') ?></li>
    <li><?= Html::a('Font Awesome 5', 'https://en.wikipedia.org/wiki/Font_Awesome') ?></li>
    <li><?= Html::a('AdminLTE Control Panel Template', 'https://adminlte.io') ?></li>
    <li><?= Html::a('Git', 'https://en.wikipedia.org/wiki/Git') ?></li>
</ul>

<h4>Web services</h4>

<ul>
    <li><?= Html::a('GitHub.com', 'https://github.com/opensourcewebsite-org/opensourcewebsite-org') ?></li>
    <li><?= Html::a('Moqups.com', 'https://app.moqups.com/opensourcewebsite/LMtjCISodJ') ?></li>
    <li><?= Html::a('Cloudflare.com', 'https://cloudflare.com') ?></li>
    <li><?= Html::a('DigitalOcean.com', 'https://m.do.co/c/4d16b1d56809') ?></li>
    <li><?= Html::a('SonarCloud.io', 'https://sonarcloud.io/dashboard?id=opensourcewebsite-org') ?></li>
    <li><?= Html::a('Travis-CI.org', 'https://travis-ci.org/opensourcewebsite-org/opensourcewebsite-org') ?></li>
    <li><?= Html::a('CodeClimate.com', 'https://codeclimate.com/github/opensourcewebsite-org/opensourcewebsite-org') ?></li>
    <li><?= Html::a('Semaphore.com', 'https://semaphoreci.com/opensourcewebsite-org/opensourcewebsite-org') ?></li>
    <li><?= Html::a('Codebeat.co', 'https://codebeat.co/projects/github-com-opensourcewebsite-org-opensourcewebsite-org-master') ?></li>
    <li><?= Html::a('Codacy.com', 'https://app.codacy.com/project/opensourcewebsite-org/opensourcewebsite-org/dashboard') ?></li>
    <li><?= Html::a('Coveralls.io', 'https://coveralls.io/github/opensourcewebsite-org/opensourcewebsite-org') ?></li>
    <li><?= Html::a('Codecov.io', 'https://codecov.io/gh/opensourcewebsite-org/opensourcewebsite-org') ?></li>
</ul>
