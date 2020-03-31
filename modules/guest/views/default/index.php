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
        <p class="text-muted">OpenSourceWebsite (OSW) - <?= Yii::t('app', 'online community managed by users using electronic voting and modifying source code') ?>.</p>
      </div>
      <div class="col-md-12">
        <p class="text-muted"><?= Yii::t('app', 'The Website is totally free, and it gives to everyone the possibility to influence in a lot of different ways its development') ?>. <?= Yii::t('app', 'Through the use of e-Vote system, each user has the possibility to vote electronically for the features of the Website, with high levels of anonymity, safety and the congruity of personal preferences') ?>.</p>
      </div>
      <div class="col-md-12">
        <p class="text-muted"><?= Yii::t('app', 'The Website exists and succeeds because of the commitments and contributions of our community') ?>. <?= Yii::t('app', 'We would like to thank everyone who participates in the development and growth of the Website') ?>. <?= Yii::t('app', 'We had greatly appreciate input from the community on this') ?>.</p>
      </div>
    </div>
  </div>
</section>

<section class="bg-light page-section" id="portfolio">
  <div class="container">
    <div class="row">
      <div class="col-lg-12 text-center">
        <h2 class="section-heading text-uppercase"><?= Yii::t('app', 'Our Mission & Vision') ?></h2>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <p class="text-muted"><?= Yii::t('app', 'We want the Web to be even better') ?>. <?= Yii::t('app', 'We want more people using it for more things') ?>. <?= Yii::t('app', 'We want it to continue to drive creativity, education and economic growth') ?>. <?= Yii::t('app', 'And we want to empower people to help shape the Web as they move more of their lives online') ?>.
      </div>
    </div>
  </div>
</section>

<section class="page-section" id="about">
  <div class="container">
    <div class="row">
      <div class="col-lg-12 text-center">
        <h2 class="section-heading text-uppercase"><?= Yii::t('app', 'Our Principles & Values') ?></h2>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <p>
          <?= Html::a('Access to public information', 'https://en.wikipedia.org/wiki/Access_to_public_information') ?>,
          <?= Html::a('Active citizenship', 'https://en.wikipedia.org/wiki/Active_citizenship') ?>,
          <?= Html::a('Activism', 'https://en.wikipedia.org/wiki/Activism') ?>,
          <?= Html::a('Civil liberties', 'https://en.wikipedia.org/wiki/Civil_liberties') ?>,
          <?= Html::a('Collaborative e-democracy', 'https://en.wikipedia.org/wiki/Collaborative_e-democracy') ?>,
          <?= Html::a('Collaborative innovation network', 'https://en.wikipedia.org/wiki/Collaborative_innovation_network') ?>,
          <?= Html::a('Collective intelligence', 'https://en.wikipedia.org/wiki/Collective_intelligence') ?>,
          <?= Html::a('Crowdcasting', 'https://en.wikipedia.org/wiki/Crowdcasting') ?>,
          <?= Html::a('Crowdfixing', 'https://en.wikipedia.org/wiki/Crowdfixing') ?>,
          <?= Html::a('Crowdsensing', 'https://en.wikipedia.org/wiki/Crowdsensing') ?>,
          <?= Html::a('Crowdsourcing', 'https://en.wikipedia.org/wiki/Crowdsourcing') ?>,
          <?= Html::a('Data activism', 'https://en.wikipedia.org/wiki/Data_activism') ?>,
          <?= Html::a('Digital collaboration', 'https://en.wikipedia.org/wiki/Digital_collaboration') ?>,
          <?= Html::a('Digital nomad', 'https://en.wikipedia.org/wiki/Digital_nomad') ?>,
          <?= Html::a('Direct democracy', 'https://en.wikipedia.org/wiki/Direct_democracy') ?>,
          <?= Html::a('E-democracy', 'https://en.wikipedia.org/wiki/E-democracy') ?>,
          <?= Html::a('E-government', 'https://en.wikipedia.org/wiki/E-government') ?>,
          <?= Html::a('E-participation', 'https://en.wikipedia.org/wiki/E-participation') ?>,
          <?= Html::a('Electronic voting', 'https://en.wikipedia.org/wiki/Electronic_voting') ?>,
          <?= Html::a('Free software movement', 'https://en.wikipedia.org/wiki/Free_software_movement') ?>,
          <?= Html::a('Freedom of information', 'https://en.wikipedia.org/wiki/Freedom_of_information') ?>,
          <?= Html::a('Global citizenship', 'https://en.wikipedia.org/wiki/Global_citizenship') ?>,
          <?= Html::a('Global nomad', 'https://en.wikipedia.org/wiki/Global_nomad') ?>,
          <?= Html::a('Human rights', 'https://en.wikipedia.org/wiki/Human_rights') ?>,
          <?= Html::a('Internet activism', 'https://en.wikipedia.org/wiki/Internet_activism') ?>,
          <?= Html::a('Libertarianism', 'https://en.wikipedia.org/wiki/Libertarianism') ?>
          <?= Html::a('Mass collaboration', 'https://en.wikipedia.org/wiki/Mass_collaboration') ?>,
          <?= Html::a('Online social movement', 'https://en.wikipedia.org/wiki/Online_social_movement') ?>,
          <?= Html::a('Open access', 'https://en.wikipedia.org/wiki/Open_access') ?>,
          <?= Html::a('Open content', 'https://en.wikipedia.org/wiki/Open_content') ?>,
          <?= Html::a('Open data', 'https://en.wikipedia.org/wiki/Open_data') ?>,
          <?= Html::a('Open education', 'https://en.wikipedia.org/wiki/Open_education') ?>,
          <?= Html::a('Open educational resources', 'https://en.wikipedia.org/wiki/Open_educational_resources') ?>,
          <?= Html::a('Open government', 'https://en.wikipedia.org/wiki/Open_government') ?>,
          <?= Html::a('Open innovation', 'https://en.wikipedia.org/wiki/Open_innovation') ?>,
          <?= Html::a('Open knowledge', 'https://en.wikipedia.org/wiki/Open_knowledge') ?>,
          <?= Html::a('Open research', 'https://en.wikipedia.org/wiki/Open_research') ?>,
          <?= Html::a('Open science', 'https://en.wikipedia.org/wiki/Open_science') ?>,
          <?= Html::a('Open science data', 'https://en.wikipedia.org/wiki/Open_science_data') ?>,
          <?= Html::a('Open source', 'https://en.wikipedia.org/wiki/Open_source') ?>,
          <?= Html::a('Open-source appropriate technology', 'https://en.wikipedia.org/wiki/Open-source_appropriate_technology') ?>,
          <?= Html::a('Open-source governance', 'https://en.wikipedia.org/wiki/Open-source_governance') ?>,
          <?= Html::a('Open-source hardware', 'https://en.wikipedia.org/wiki/Open-source_hardware') ?>,
          <?= Html::a('Open-source-software movement', 'https://en.wikipedia.org/wiki/Open-source-software_movement') ?>,
          <?= Html::a('Openness', 'https://en.wikipedia.org/wiki/Openness') ?>,
          <?= Html::a('Participatory democracy', 'https://en.wikipedia.org/wiki/Participatory_democracy') ?>,
          <?= Html::a('Participatory monitoring', 'https://en.wikipedia.org/wiki/Participatory_monitoring') ?>,
          <?= Html::a('Posthumanism', 'https://en.wikipedia.org/wiki/Posthumanism') ?>,
          <?= Html::a('Public participation', 'https://en.wikipedia.org/wiki/Public_participation') ?>,
          <?= Html::a('Privacy', 'https://en.wikipedia.org/wiki/Privacy') ?>,
          <?= Html::a('Radical transparency', 'https://en.wikipedia.org/wiki/Radical_transparency') ?>,
          <?= Html::a('Reputation system', 'https://en.wikipedia.org/wiki/Reputation_system') ?>,
          <?= Html::a('Self-governance', 'https://en.wikipedia.org/wiki/Self-governance') ?>,
          <?= Html::a('Social collaboration', 'https://en.wikipedia.org/wiki/Social_collaboration') ?>,
          <?= Html::a('Social web', 'https://en.wikipedia.org/wiki/Social_web') ?>,
          <?= Html::a('Transhumanism', 'https://en.wikipedia.org/wiki/Transhumanism') ?>,
          <?= Html::a('Transparency', 'https://en.wikipedia.org/wiki/Transparency_(behavior)') ?>,
          <?= Html::a('Virtual volunteering', 'https://en.wikipedia.org/wiki/Virtual_volunteering') ?>,
          <?= Html::a('Web science', 'https://en.wikipedia.org/wiki/Web_science') ?>,
          <?= Html::a('Wisdom of the crowd', 'https://en.wikipedia.org/wiki/Wisdom_of_the_crowd') ?>.
        </p>
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
          <p class="text-muted"><?= Yii::t('app', 'User Rating provides') ?>:</p>
        </div>
        <div class="col-md-12">
          <ul>
              <li><?= Yii::t('app', 'The power of user vote when voting for new features or changing existing features of the Website') ?>.</li>
              <li><?= Yii::t('app', 'More possibilities and less limits when using of the Website') ?>.</li>
              <li><?= Yii::t('app', 'Passive income, such as dividends from the profits of the Website, which is distributed proportionally among all users in accordance with the value of their User Ratings') ?>.</li>
          </ul>
        </div>
        <div class="col-md-12">
          <p class="text-muted"><?= Yii::t('app', 'All new users, who have joined the Website through your referral link, become your referrals.') ?></p>
        </div>
        <div class="col-md-12">
          <ul>
              <li><?= Yii::t('app', 'You get rewards from your referrals for their purchases on the Website and websites of our partner companies, and from offline partners using a discount card') ?>.</li>
              <li><?= Yii::t('app', 'Multi-level loyalty program, you get rewards from multiple referral levels, not only from first level') ?>.</li>
              <li><?= Yii::t('app', 'User community of the Website decides what conditions will be in loyalty program. You can participate in discuss process and vote for the conditions') ?>.</li>
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
        <p class="text-muted"><?= Yii::t('app', 'Your donations increase your User Rating on the Website') ?>.</p>
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
        <h2 class="section-heading text-uppercase"><?= Yii::t('app', 'Contribution') ?></h2>
      </div>
    </div>
      <div class="row">
        <div class="col-md-12">
          <p class="text-muted"><?= Yii::t('app', 'Your contributions increase your User Rating on the Website') ?>.</p>
        </div>
        <div class="col-md-12">
          <p class="text-muted"><?= Yii::t('app', 'We accepts volunteers and interns who demonstrate appropriate skills and express a strong interest in one or more of the following areas') ?>: open source development, open data, editorial and translation work, UI/UX web design, social media and communications.</p>
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
            <?= Html::a('Semaphore.com', 'https://semaphoreci.com/opensourcewebsite-org/opensourcewebsite-org') ?>,
            <?= Html::a('SonarCloud.io', 'https://sonarcloud.io/dashboard?id=opensourcewebsite-org') ?>,
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
        <p align="center"><?= Html::a(Yii::t('app', 'Join us and be part of the digital future') . '!', Yii::$app->urlManager->createUrl(['site/login']), ['class' => 'btn btn-primary btn-xl text-uppercase js-scroll-trigger']) ?></p>
      </div>
    </div>
  </div>
</section>
