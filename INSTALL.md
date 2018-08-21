###General setup

- Copy file `.env.dist` to `.env` in the root directory
- Set correct values in `.env` for your environment

#### Using Docker
- set `DB_HOST=db` in `.env` file
- run `docker-compose up -d` from root directory
- run `docker-compose exec php composer install`
- run `docker-compose exec php ./yii migrate`

#### Without docker
- install php and composer
- run `php composer.phar install`
- run `php yii migrate`