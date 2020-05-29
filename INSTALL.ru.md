# Установка

[English version](INSTALL.md)

Пожалуйста, прочитайте наше [Руководство контрибьютора](CONTRIBUTING.ru.md).

## Веб-сайт

#### C использованием Docker (простой способ)

- Сделайте копию файла `config/params.dist.php` и переименуйте его в `config/params.php`
- Сделайте копию файла `config/web-local.dist.php` и переименуйте его в `config/web-local.php`
- Сделайте копию файла `.env.docker.dist` и переименуйте его в `.env`
- Установите [Docker](https://www.docker.com)
- Запустите `docker-compose up -d`
- Запустите `docker-compose exec php composer install`
- Запустите `docker-compose exec php ./yii migrate`

Сайт будет доступен по адресу http://localhost:8000.

[Adminer](https://www.adminer.org) будет доступен по адресу http://localhost:8080.

#### Без использования Docker (продвинутый способ)

- Сделайте копию файла `config/params.dist.php` и переименуйте его в `config/params.php`
- Сделайте копию файла `config/web-local.dist.php` и переименуйте его в `config/web-local.php`
- Сделайте копию файла `.env.dist` и переименуйте его в `.env`
- Установите правильные значения в файле `.env` для вашего окружения
- Установите [MySQL 8.X](https://www.mysql.com):
  - Создайте новую базу данных MySQL InnoDB ("opensourcewebsite" по умолчанию), с кодировкой "utf8mb4_0900_ai_ci" для вашего окружения
- Установите [веб-сервер Nginx](https://nginx.org) или [веб-сервер Apache](https://httpd.apache.org):
  - Установите корневую папку веб-сервера на `web`
- Установите [PHP 7.4.X](https://www.php.net)
- Установите [XDebug](https://xdebug.org)
- Установите [Composer](https://getcomposer.org)
- Запустите `php composer.phar install`
- Запустите `php yii migrate`

## Фикстуры

Фикстуры используются для загрузки "фейкового" (не реального) набора данных в базу данных, который может быть использован для тестирования или поможет вам в предоставлении некоторой интересной информации пока вы разрабатываете своё приложение.

После загрузки фикстур будут доступны [учётные данные пользователей](tests/fixtures/data/user.php).

Внимание! После загрузки фикстур все текущие записи базы данных будут удалены!

Загрузка фикстур:
```
php yii fixture/load "*"
```

Выгрузка фикстур:
```
php yii fixture/unload "*"
```

### Генератор данных

Контроллер генератора наследуется от `\yii\console\controllers\FixtureController`. Так что эта часть документации - [Загрузка фикстур](https://www.yiiframework.com/doc/guide/2.0/ru/test-fixtures##zagruzka-fikstur) - может быть использована для понимания синтаксиса команд.

Новые генераторы для моделей могут быть добавлены в папку `modules\dataGenerator\components\generators`.

Показать справочную информацию:
```
php yii dataGenerator/default/load -h
```

#### Использование

Сгенерировать все доступные модели с задержкой в 2 секунды:
```
php yii dataGenerator "*"
```

Сгенерировать 5 `User` и 5 `Contact` моделей:
```
php yii dataGenerator "User, Contact" --limit=5
```

Сгенерировать `User` и `Contact` модели с задержкой в 5 секунд:
```
php yii dataGenerator "User, Contact" --interval=5
```

Сгенерировать все модели кроме `Contact` с задержкой в 5 секунд:
```
php yii dataGenerator "*, -Contact" --interval=5
```

## Тесты

#### Настройка

- Сделайте копию файла `.env.test.dist` и переименуйте его в `.env.test`
- Установите правильные значения в файле `.env.test` для тестового окружения
- Создайте новую базу данных MySQL InnoDB ("opensourcewebsite_test" по умолчанию) с кодировкой "utf8mb4_0900_ai_ci" для тестового окружения

#### Использование

- Запустите `php tests/bin/yii migrate`
- Запустите `php vendor/bin/codecept run` or `php vendor/bin/codecept run --coverage --coverage-xml --coverage-html`

## Telegram-бот

Мы рекомендуем использовать [ngrok - безопасные интроспективные туннели к localhost](https://ngrok.com), для локальной разработки и тестирования Telegram-ботов. Telegram webhooks требуют ваш публичный URL с HTTPS. ngrok - это инструмент, который сделает ваше локальное окружение доступным в Интернете.

- Используйте [Telegram BotFather](https://t.me/BotFather) для создания нового бота и получения его токена.
- Установите `baseUrl` в `params.php` для вашего открытого URL с HTTPS.
- Для избежания проблем с Telegram (например, при блокировке), используйте бесплатные анонимные прокси ([список 1](https://www.firexproxy.com/en), [список 2](https://mtpro.xyz/socks5)) и установите `telegramProxy` в `params.php`.
- Создайте новую запись в таблице `bot` в базе данных MySQL, заполнив `status` = 0, или используйте консольную команду для добавления нового бота:
```
php yii bot/add ТОКЕН
```

Включить Telegram webhooks для всех ботов с `status` = 0:
```
php yii bot/enable-all
```

Отключить Telegram webhooks для всех ботов с `status` = 1:
```
php yii bot/disable-all
```
