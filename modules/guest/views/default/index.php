<?php

/* @var $this yii\web\View */

use yii\helpers\Html;

?>
<header class="masthead">
  <div class="container">
    <div class="intro-text">
      <div class="intro-heading">Open Source Website</div>
      <div class="intro-lead-in"><?= Yii::t('app', 'online community') ?></div>
      <a class="btn btn-primary btn-xl text-uppercase js-scroll-trigger" href="#services"><?= Yii::t('app', 'Tell Me More') ?></a>
    </div>
  </div>
</header>

<section class="page-section" id="services">
  <div class="container">
    <div class="row">
      <div class="col-lg-12 text-center">
        <h2 class="section-heading text-uppercase"><?= Yii::t('app', 'How It Works') ?></h2>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <p class="text-muted">OpenSourceWebsite (OSW) - <?= Yii::t('common', 'online community managed by users using electronic voting and modifying source code') ?>.</p>
      </div>
    </div>
  </div>
</section>

<section class="bg-light page-section" id="team">
  <div class="container">
    <div class="row">
      <div class="col-lg-12 text-center">
        <h2 class="section-heading text-uppercase"><?= Yii::t('app', 'Features & Plans') ?></h2>
      </div>
    </div>
    <div class="row">
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
                <li>Custom database and storage</li>
                <li>OAuth</li>
                <li>API documentation service</li>
                <li>Open Data Aggregator</li>
                <li>API services directory</li>
                <li>Medicine and Health</li>
                <li>Sport and Fitness</li>
                <li>Inventory and reviews of books and films</li>
                <li>Local exchange of fiat and electronic currencies</li>
                <li>Crowdfunding</li>
            </ul>
        </div>
    </div>
  </div>
</section>

<section class="page-section" id="about">
  <div class="container">
    <div class="row">
      <div class="col-lg-12 text-center">
        <h2 class="section-heading text-uppercase"><?= Yii::t('app', 'Membership & Loyalty Program') ?></h2>
      </div>
    </div>
      <div class="row">
        <div class="col-md-12">
          <p class="text-muted"><?= Yii::t('app', 'Rating provides') ?>:</p>
        </div>
        <div class="col-md-12">
          <ul>
              <li><?= Yii::t('bot', 'The power of user vote when voting for new features or changing existing features of the Bot and Website') ?>.</li>
              <li><?= Yii::t('bot', 'More possibilities and less limits when using of the Bot and Website') ?>.</li>
              <li><?= Yii::t('bot', 'Passive income, such as dividends from the profits of the Bot and Website, which is distributed proportionally among all users in accordance with the value of their Ratings') ?>.</li>
          </ul>
        </div>
        <div class="col-md-12">
          <p class="text-muted"><?= Yii::t('bot', 'All new users, who have joined the Bot or Website through your referral link, become your referrals') ?>.</p>
        </div>
        <div class="col-md-12">
          <ul>
              <li><?= Yii::t('bot', 'You get rewards from your referrals for their purchases on the Website and websites of our partner companies, and from offline partners using a discount card') ?>.</li>
              <li><?= Yii::t('bot', 'Multi-level loyalty program, you get rewards from multiple referral levels, not only from first level') ?>.</li>
              <li><?= Yii::t('bot', 'User community decides what conditions will be in loyalty program') ?>. <?= Yii::t('bot', 'You can participate in discuss process and vote for the conditions') ?>.</li>
          </ul>
        </div>
        <div class="col-md-12">
          <p class="text-muted"><?= Yii::t('app', 'Soon the loyalty program will be significantly increased and new bonuses will be added') ?>.</p>
        </div>
      </div>
  </div>
</section>

<section class="bg-light page-section" id="portfolio">
  <div class="container">
    <div class="row">
      <div class="col-lg-12 text-center">
        <h2 class="section-heading text-uppercase"><?= Yii::t('app', 'Donate & Crowdfunding') ?></h2>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <p class="text-muted"><?= Yii::t('app', 'Your donations increase your Rating in our community') ?>.</p>
      </div>
      <div class="col-md-12">
        <ul>
            <li>Processing with <?= Html::a('Open Collective', 'https://opencollective.com/opensourcewebsite') ?></li>
            <li>Processing with <?= Html::a('Yandex.Money', 'https://money.yandex.ru/to/4100111248401133') ?></li>
            <li>Processing credit cards and PayPal with <?= Html::a('PayPal.Me', 'https://paypal.me/opensourcewebsite') ?></li>
            <li>Processing 50+ cryptocurrencies with <?= Html::a('CoinGate.com', 'https://coingate.com/pay/opensourcewebsite') ?></li>
            <li>Processing 4 cryptocurrencies with <?= Html::a('Coinbase.com', 'https://commerce.coinbase.com/checkout/e89005ec-c8c2-47c1-9ca4-b1deb9992794') ?></li>
        </ul>
      </div>
    </div>
  </div>
</section>

<section class="page-section" id="about">
  <div class="container">
    <div class="row">
      <div class="col-lg-12 text-center">
        <h2 class="section-heading text-uppercase"><?= Yii::t('common', 'Contribution') ?></h2>
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
          <ul>
              <li><?= Html::a('How to Contribute', 'https://opensource.guide/how-to-contribute/') ?></li>
              <li><?= Html::a('Contributing Guidelines', 'https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/CONTRIBUTING.md') ?></li>
              <li><?= Html::a('Code of Conduct', 'https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/CODE_OF_CONDUCT.md') ?></li>
          </ul>
        </div>
      </div>
  </div>
</section>

<section class="bg-light page-section" id="portfolio">
  <div class="container">
    <div class="row">
      <div class="col-lg-12 text-center">
        <h2 class="section-heading text-uppercase"><?= Yii::t('app', 'Technologies We Use') ?></h2>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <p>
          <?= Html::a('AdminLTE 3 (Bootstrap Theme)', 'https://github.com/ColorlibHQ/AdminLTE') ?>,
          <?= Html::a('Bootstrap 4', 'https://en.wikipedia.org/wiki/Bootstrap_(front-end_framework)') ?>,
          <?= Html::a('Composer', 'https://en.wikipedia.org/wiki/Composer_(software)') ?>,
          <?= Html::a('CSS 3', 'https://en.wikipedia.org/wiki/Cascading_Style_Sheets') ?>,
          <?= Html::a('Font Awesome 5', 'https://en.wikipedia.org/wiki/Font_Awesome') ?>,
          <?= Html::a('Git', 'https://en.wikipedia.org/wiki/Git') ?>,
          <?= Html::a('HTML 5', 'https://en.wikipedia.org/wiki/HTML') ?>,
          <?= Html::a('HTTP/2', 'https://en.wikipedia.org/wiki/HTTP/2') ?>,
          <?= Html::a('JavaScript', 'https://en.wikipedia.org/wiki/JavaScript') ?>,
          <?= Html::a('Let\'s Encrypt', 'https://en.wikipedia.org/wiki/Let%27s_Encrypt') ?>,
          <?= Html::a('MySQL 5', 'https://en.wikipedia.org/wiki/MySQL') ?>,
          <?= Html::a('Nginx', 'https://en.wikipedia.org/wiki/Nginx') ?>,
          <?= Html::a('OpenAPI', 'https://en.wikipedia.org/wiki/OpenAPI_Specification') ?>,
          <?= Html::a('PHP 7', 'https://en.wikipedia.org/wiki/PHP') ?>,
          <?= Html::a('React.js JavaScript Framework', 'https://en.wikipedia.org/wiki/React_(web_framework)') ?>,
          <?= Html::a('Start Bootstrap - Agency (Bootstrap Theme)', 'https://github.com/BlackrockDigital/startbootstrap-agency') ?>,
          <?= Html::a('Ubuntu', 'https://en.wikipedia.org/wiki/Ubuntu') ?>,
          <?= Html::a('Vue.js 2 JavaScript Framework', 'https://en.wikipedia.org/wiki/Vue.js') ?>,
          <?= Html::a('Yii 2 PHP Framework', 'https://en.wikipedia.org/wiki/Yii') ?>.
        </p>
      </div>
    </div>
  </div>
</section>

<section class="page-section" id="about">
  <div class="container">
    <div class="row">
      <div class="col-lg-12 text-center">
        <h2 class="section-heading text-uppercase"><?= Yii::t('app', 'Web Services We Use') ?></h2>
      </div>
    </div>
      <div class="row">
        <div class="col-md-12">
          <p>
            <?= Html::a('Codacy.com', 'https://app.codacy.com/project/opensourcewebsite-org/opensourcewebsite-org/dashboard') ?>,
            <?= Html::a('Codebeat.co', 'https://codebeat.co/projects/github-com-opensourcewebsite-org-opensourcewebsite-org-master') ?>,
            <?= Html::a('CodeClimate.com', 'https://codeclimate.com/github/opensourcewebsite-org/opensourcewebsite-org') ?>,
            <?= Html::a('Codecov.io', 'https://codecov.io/gh/opensourcewebsite-org/opensourcewebsite-org') ?>,
            <?= Html::a('Cloudflare.com', 'https://cloudflare.com') ?>,
            <?= Html::a('Coveralls.io', 'https://coveralls.io/github/opensourcewebsite-org/opensourcewebsite-org') ?>,
            <?= Html::a('DigitalOcean.com', 'https://m.do.co/c/4d16b1d56809') ?>,
            <?= Html::a('GitHub.com', 'https://github.com/opensourcewebsite-org/opensourcewebsite-org') ?>,
            <?= Html::a('Moqups.com', 'https://app.moqups.com/LMtjCISodJ/view/page/ad2542407') ?>,
            <?= Html::a('Semaphore.com', 'https://opensourcewebsite.semaphoreci.com/projects/opensourcewebsite-org') ?>,
            <?= Html::a('SonarCloud.io', 'https://sonarcloud.io/dashboard?id=opensourcewebsite-org') ?>,
            <?= Html::a('Telegram.org', 'https://telegram.org') ?>,
            <?= Html::a('Travis-CI.org', 'https://travis-ci.org/opensourcewebsite-org/opensourcewebsite-org') ?>.
          </p>
        </div>
      </div>
  </div>
</section>

<section class="bg-light page-section" id="portfolio">
  <div class="container">
    <div class="row">
      <div class="col-lg-12 text-center">
        <h2 class="section-heading text-uppercase"></h2>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <p align="center"><?= Html::a(Yii::t('common', 'Join us and be part of the digital future') . '!', Yii::$app->urlManager->createUrl(['site/login']), ['class' => 'btn btn-primary btn-xl text-uppercase js-scroll-trigger']) ?></p>
      </div>
    </div>
  </div>
</section>
