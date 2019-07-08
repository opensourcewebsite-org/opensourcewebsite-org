# Installing

## General setup

- Copy file `.env.dist` to `.env` in the root directory
- Set correct values in `.env` for your environment
- Copy `config/params.dist.php` to `config/params.php`
- Copy `config/web-local.dist.php` to `config/web-local.php`

#### Using Docker

- set `DB_HOST=db` in `.env` file
- run `docker-compose up -d` from root directory
- run `docker-compose exec php composer install`
- run `docker-compose exec php ./yii migrate`

Web-server can be accessed at http://localhost:8000

#### Without docker
- setup your web-server root folder to `web`
- install php and composer
- run `php composer.phar install`
- run `php yii migrate`
