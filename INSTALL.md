# Installation

[Русская версия](INSTALL.ru.md)

Please read through our [Contribution Guidelines](CONTRIBUTING.md).

## Website

#### Using Docker (easy way)

- Copy file `config/params.dist.php` to `config/params.php`
- Copy file `config/web-local.dist.php` to `config/web-local.php`
- Copy file `.env.docker.dist` to `.env`
- Install [Docker](https://www.docker.com)
- Run `docker-compose up -d`
- Run `docker-compose exec php composer install`
- Run `docker-compose exec php ./yii migrate`

The website can be accessed at http://localhost:8000.

[Adminer](https://www.adminer.org) can be accessed at http://localhost:8080.

#### Without Docker (advanced way)

- Copy file `config/params.dist.php` to `config/params.php`
- Copy file `config/web-local.dist.php` to `config/web-local.php`
- Copy file `.env.dist` to `.env`
- Set correct values in `.env` file for your environment
- Install [MySQL 8.X](https://www.mysql.com):
  - Create a new MySQL InnoDB database ("opensourcewebsite" by default), with "utf8mb4_0900_ai_ci" collation for your environment
- Install [Nginx web server](https://nginx.org) or [Apache web server](https://httpd.apache.org):
  - Setup your web server root folder to `web`
- Install [PHP 7.4.X](https://www.php.net)
- Install [XDebug](https://xdebug.org)
- Install [Composer](https://getcomposer.org)
- Run `php composer.phar install`
- Run `php yii migrate`

## Fixtures

Fixtures are used to load a "fake" set of data into a database that can then be used for testing or to help give you some interesting data while you're developing your application.

After loading fixtures, will be available this [user login credentials](tests/fixtures/data/user.php).

Attention! When loading fixtures, all current records from the database will be deleted!

Loading fixtures:
```
php yii fixture/load "*"
```

Unloading fixtures:
```
php yii fixture/unload "*"
```

### Data Generator

The generation controller extends from `\yii\console\controllers\FixtureController`. So this part of documentation - [Loading fixtures](https://www.yiiframework.com/doc/guide/2.0/en/test-fixtures#loading-fixtures) - can be used to understand command syntax.

New generators for models can be added to the folder `modules\dataGenerator\components\generators`.

Show help information:
```
php yii dataGenerator/default/load -h
```

#### Usage

Generates all available models with a delay of 2 seconds:
```
php yii dataGenerator "*"
```

Generates 5 `User` and 5 `Contact` models:
```
php yii dataGenerator "User, Contact" --limit=5
```

Generates `User` and `Contact` models with a delay of 5 seconds:
```
php yii dataGenerator "User, Contact" --interval=5
```

Generates all models except `Contact` with a delay of 5 seconds:
```
php yii dataGenerator "*, -Contact" --interval=5
```

## Tests

#### Setup

- Copy file `.env.test.dist` to `.env.test`
- Set correct values in `.env.test` file for test environment
- Create a new MySQL InnoDB database ("opensourcewebsite_test" by default) with an "utf8mb4_0900_ai_ci" collation for test environment

#### Usage

- Run `php tests/bin/yii migrate`
- Run `php vendor/bin/codecept run` or `php vendor/bin/codecept run --coverage --coverage-xml --coverage-html`

## Telegram bot

We recommend use [ngrok - secure introspectable tunnels to localhost](https://ngrok.com), for local development and testing of Telegram bots. Telegram webhooks require your public URL with HTTPS. ngrok is a tool that exposes your local environment to the world.

- Use [Telegram BotFather](https://t.me/BotFather) to create new bot and get a bot token.
- Set `baseUrl` in `params.php` for your public URL with HTTPS.
- In case of connection problems to Telegram, use free anonymous proxy ([list 1](https://www.firexproxy.com/en), [list 2](https://mtpro.xyz/socks5)) to set `telegramProxy` in `params.php`.
- Create a new record in `bot` table in MySQL database, with `status` = 0, or use console command:
```
php yii bot/add [BOT TOKEN]
```

Enable Telegram webhooks for all bots with `status` = 0:
```
php yii bot/enable-all
```

Disable Telegram webhooks for all bots with `status` = 1:
```
php yii bot/disable-all
```
