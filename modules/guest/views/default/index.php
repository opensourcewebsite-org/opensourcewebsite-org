<?php

/* @var $this yii\web\View */

use app\components\helpers\ExternalLink;
use yii\helpers\Html;

?>
<header class="masthead">
  <div class="container">
    <div class="intro-text">
      <div class="intro-heading">Open Source Website</div>
      <div class="intro-lead-in"><?= Yii::t('app', 'Creating an Open and Democratic Online Community') ?></div>
      <?= Html::a(Yii::t('app', 'Join us'), Yii::$app->urlManager->createUrl(['site/login']), ['class' => 'btn btn-primary btn-xl text-uppercase js-scroll-trigger']) ?>
    </div>
  </div>
</header>

<section class="page-section" id="features">
  <div class="container">
    <div class="row">
      <div class="col-lg-12 text-center">
        <h2 class="section-heading text-uppercase"><?= Yii::t('app', 'Features & Plans') ?></h2>
      </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <p>
                <b>Telegram</b>
            </p>
            <ul>
                <li><?= Html::a(Yii::t('app', 'Telegram Bot'), ExternalLink::getBotLink()) ?></li>
            </ul>
        </div>
        <div class="col-md-12">
            <p>
                <b>Social</b>
            </p>
            <ul>
                <li>Social networking</li>
                <li>Dating</li>
                <li>Blogs</li>
                <li>Forums</li>
                <li>Services for digital nomads</li>
                <li>Global catalog for commercial companies, non-profit companies, for charitable foundations</li>
            </ul>
        </div>
        <div class="col-md-12">
            <p>
                <b>Personal interests and hobbies</b>
            </p>
            <ul>
                <li>Genealogy and genealogical tree</li>
                <li>Food delivery</li>
                <li>Hotel, Hospitality and B&B</li>
                <li>Medicine and Health</li>
                <li>Sport and Fitness</li>
                <li>Inventory and reviews of books and films</li>
            </ul>
        </div>
        <div class="col-md-12">
            <p>
                <b>Economics</b>
            </p>
            <ul>
                <li>International money transfers</li>
                <li>Crowdfunding</li>
                <li>Work and personnel search</li>
                <li>Freelance marketplace</li>
                <li>Sharing and collaborative consumption</li>
                <li>Real-estate marketplace</li>
                <li>Advertising platform</li>
                <li>Currencies monitoring</li>
                <li>Local exchange of fiat and electronic currencies</li>
            </ul>
        </div>
        <div class="col-md-12">
            <p>
                <b>Business</b>
            </p>
            <ul>
                <li>Business management</li>
                <li>Project management</li>
                <li>Product management</li>
                <li>CRM</li>
                <li>ERP</li>
                <li>Workplaces</li>
                <li>Working schedules</li>
                <li>Accounting</li>
            </ul>
        </div>
        <div class="col-md-12">
            <p>
                <b>Technical</b>
            </p>
            <ul>
                <li>Custom databases and storages</li>
                <li>OAuth</li>
                <li>Open Data Aggregator</li>
                <li>API documentation service</li>
                <li>API services directory</li>
            </ul>
        </div>
    </div>
  </div>
</section>

<section class="page-section" id="membership">
  <div class="container">
    <div class="row">
      <div class="col-lg-12 text-center">
        <h2 class="section-heading text-uppercase"><?= Yii::t('app', 'Membership') ?></h2>
      </div>
    </div>
      <div class="row">
        <div class="col-md-12">
          <p class="text-muted"><?= Yii::t('app', 'Rating provides') ?>:</p>
        </div>
        <div class="col-md-12">
          <ul>
              <li><?= Yii::t('bot', 'The power of user vote when voting for new features and changing existing features of the Bot and Website') ?>.</li>
              <li><?= Yii::t('bot', 'More possibilities and less limits when using of the Bot and Website') ?>.</li>
              <li><?= Yii::t('bot', 'Passive income, such as dividends from the profits of the Bot and Website, which is distributed proportionally among all users in accordance with the value of their Ratings') ?>.</li>
          </ul>
        </div>
      </div>
  </div>
</section>

<section class="page-section" id="donate">
  <div class="container">
    <div class="row">
      <div class="col-lg-12 text-center">
        <h2 class="section-heading text-uppercase"><?= Yii::t('app', 'Donation & Crowdfunding') ?></h2>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <p class="text-muted"><?= Yii::t('app', 'Your donations increase your Rating in our community') ?>.</p>
      </div>
      <div class="col-md-12">
        <p class="text-muted"><?= Yii::t('app', 'Please send all donation offers to') ?> <a href="mailto:hello@opensourcewebsite.org">hello@opensourcewebsite.org</a>.</p>
      </div>
    </div>
  </div>
</section>

<section class="page-section" id="contribution">
  <div class="container">
    <div class="row">
      <div class="col-lg-12 text-center">
        <h2 class="section-heading text-uppercase"><?= Yii::t('app', 'Contribution') ?></h2>
      </div>
    </div>
      <div class="row">
        <div class="col-md-12">
          <p class="text-muted"><?= Yii::t('app', 'Your contributions increase your Rating in our community') ?>.</p>
        </div>
        <div class="col-md-12">
          <p class="text-muted"><?= Yii::t('app', 'We accepts volunteers and interns who demonstrate appropriate skills and express a strong interest in one or more of the following areas') ?>: <?= Yii::t('app', 'open source development, open data, editorial and translation work, UI/UX web design, social media and communications') ?>.</p>
        </div>
        <div class="col-md-12">
              <li><?= Html::a('Source Code', ExternalLink::getGithubLink()) ?></li>
              <li><?= Html::a('How to Contribute', 'https://opensource.guide/how-to-contribute/') ?></li>
              <li><?= Html::a('Contributing Guidelines', ExternalLink::getGithubContributionLink()) ?></li>
              <li><?= Html::a('Code of Conduct', ExternalLink::getGithubCodeOfConductLink()) ?></li>
          </ul>
        </div>
      </div>
  </div>
</section>

<section class="page-section" id="sponsors">
  <div class="container">
    <div class="row">
      <div class="col-lg-12 text-center">
        <h2 class="section-heading text-uppercase"><?= Yii::t('app', 'Partners') ?></h2>
      </div>
    </div>
      <div class="row">
        <div class="col-md-12">
            <div class="sponsors__list">
              <a href="https://m.do.co/c/4d16b1d56809" title="DigitalOcean.com" rel="nofollow noreferrer noopener" target="_blank">
                <img src="https://opensource.nyc3.cdn.digitaloceanspaces.com/attribution/assets/SVG/DO_Logo_horizontal_blue.svg" alt="DigitalOcean logo">
              </a>
              <a href="https://moqups.com" title="Moqups.com" rel="nofollow noreferrer noopener" target="_blank">
                <img src="https://landing.moqups.com/img/logo@2x.png" alt="Moqups.com">
              </a>
            </div>
        </div>
      </div>
  </div>
</section>

<section class="page-section" id="join">
  <div class="container">
    <div class="row">
      <div class="col-lg-12 text-center">
        <h2 class="section-heading text-uppercase"></h2>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <p align="center"><?= Html::a(Yii::t('app', 'Join us'), Yii::$app->urlManager->createUrl(['site/login']), ['class' => 'btn btn-primary btn-xl text-uppercase js-scroll-trigger']) ?></p>
      </div>
    </div>
  </div>
</section>
