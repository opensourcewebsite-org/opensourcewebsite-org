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

#### Without docker

- Create a new MySQL database with an "utf8mb4_unicode_ci" collation
- Setup your web-server root folder to `web`
- Install php and composer
- Run `php composer.phar install`
- Run `php yii migrate`
