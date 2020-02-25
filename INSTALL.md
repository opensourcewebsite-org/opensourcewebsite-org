# Installation

## General setup

- Copy file `.env.dist` to `.env` in the root directory
- Set correct values in `.env` for your environment
- Copy `config/params.dist.php` to `config/params.php`
- Copy `config/web-local.dist.php` to `config/web-local.php`

#### Using Docker

- Set `DB_HOST=db` in `.env` file
- Run `docker-compose up -d` from root directory
- Run `docker-compose exec php composer install`
- Run `docker-compose exec php ./yii migrate`

Web-server can be accessed at http://localhost:8000

#### Without Docker

- Install [MySQL +5.7.X](https://www.mysql.com)
- Create a new MySQL database with an "utf8mb4_unicode_ci" collation
- Setup your web-server root folder to `web`    
- Install [PHP +7.2.X](https://www.php.net)
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

## Telegram bots

We recommend use [ngrok - secure introspectable tunnels to localhost](https://ngrok.com), for local development and testing of Telegram bots. Telegram webhooks require your URL to be public and secure (HTTPS). ngrok is a tool that exposes your local environment to the world.

- Use [Telegram BotFather](https://t.me/BotFather) to create new bot and get a bot token.
- Set `baseUrl` in `params.php` for your ngrok https url.
- In case of connection problems to Telegram, use [free anonymous proxy][https://www.firexproxy.com/en] to set `telegramProxy` in `params.php`.
- Create a new record in `bot` table in MySQL database, with `status` = 0, or use console command to add new bot:
```
php yii bot/add TOKEN
```

Enable Telegram webhooks for all bots with `status` = 0:
```
php yii bot/enable-all
```

Disable Telegram webhooks for all bots with `status` = 1:
```
php yii bot/disable-all
```
