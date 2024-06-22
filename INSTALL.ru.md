# Установка

[English version](INSTALL.md)

Пожалуйста, прочитайте наше [Руководство контрибьютора](CONTRIBUTING.ru.md).

## Веб-сайт

#### C использованием Docker (простой способ)

- Сделайте копию файла `.env.docker.dist` и переименуйте его в `.env`.
- Установите [Docker](https://www.docker.com).
- Запустите `docker-compose up -d`.
- Запустите `docker-compose exec php composer install`.
- Запустите `docker-compose exec php ./yii migrate`.

Сайт будет доступен по адресу http://localhost:8000.

[Adminer](https://www.adminer.org) будет доступен по адресу http://localhost:8080.

#### Без использования Docker (продвинутый способ)

- Сделайте копию файла `.env.dist` и переименуйте его в `.env`.
- Установите правильные значения в файле `.env` для вашего окружения.
- Установите [MySQL 8.X](https://www.mysql.com):
  - Создайте новую базу данных MySQL InnoDB ("opensourcewebsite" по умолчанию), с кодировкой "utf8mb4_0900_ai_ci" для вашего окружения.
  - Отключите `sql_mode=ONLY_FULL_GROUP_BY`.
- Установите [веб-сервер Nginx](https://nginx.org) или [веб-сервер Apache](https://httpd.apache.org):
  - Установите корневую папку веб-сервера на `web`.
- Установите [PHP 7.4.X](https://www.php.net).
- Установите [XDebug](https://xdebug.org).
- Установите [Composer](https://getcomposer.org).
- Запустите `php composer.phar install`.
- Запустите `php yii migrate`.

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

## Отладка

### Советы

Посмотреть данные об отладке можно здесь: <localhost:8000/debug/>.
(Если вы используете Docker, нужно изменить [эту строку](https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/config/web.php#L167) в `config/web.php`).

Используйте эти методы класса `Yii` для логирования сообщений различных уровней важности:

- [`Yii::debug()`](https://www.yiiframework.com/doc/api/2.0/yii-baseyii#debug()-detail)
- [`Yii::error()`](https://www.yiiframework.com/doc/api/2.0/yii-baseyii#error()-detail)
- [`Yii::warning()`](https://www.yiiframework.com/doc/api/2.0/yii-baseyii#warning()-detail)
- [`Yii::info()`](https://www.yiiframework.com/doc/api/2.0/yii-baseyii#info()-detail)

Логи можно посмотреть в файле `runtime/logs/web.log`, или здесь: <http://localhost:8000/debug/default/view?panel=log>.

Чтобы активировать пошаговую отладку с помощью [Xdebug](https://xdebug.org/) в вашей среде разработки нужно:

1. Установить следующие переменные окружения в `.env` файле:

    ```dotenv
    PHP_ENABLE_XDEBUG=1
    XDEBUG_CONFIG="client_host=172.17.0.1 client_port=9005 start_with_request=yes idekey=PHPSTORM log_level=1 log=/app/xdebug.log remote_enable=true remote_autostart=true discover_client_host=true remote_log=/app/remote.xdebug.log"
    XDEBUG_MODE=develop,debug
    ```
1. Задать порт отладки для Xdebug в вашей среде разработки равным `9005`. ([File | Settings | PHP | Debug](jetbrains://PhpStorm/settings?name=PHP--Debug) для PHPStorm).
1. Запустить `docker-compose up -d`.

P.S. Тестировалось только в PHPStorm.

## Тесты

#### Настройка

- Сделайте копию файла `.env.test.dist` и переименуйте его в `.env.test`
- Установите правильные значения в файле `.env.test` для тестового окружения
- Создайте новую базу данных MySQL InnoDB ("opensourcewebsite_test" по умолчанию) с кодировкой "utf8mb4_0900_ai_ci" для тестового окружения

#### Использование

- Запустите `php tests/bin/yii migrate`
- Запустите `php vendor/bin/codecept run` or `php vendor/bin/codecept run --coverage --coverage-xml --coverage-html`

## Telegram-бот

Telegram Bot API:

- https://core.telegram.org/bots/api

Мы рекомендуем использовать [ngrok - безопасные интроспективные туннели к localhost](https://ngrok.com), для локальной разработки и тестирования Telegram-ботов. Telegram webhooks требуют ваш публичный URL с HTTPS. ngrok - это инструмент, который сделает ваше локальное окружение доступным в Интернете.

Также вы можете воспользоваться [Localtunnel](https://localtunnel.me) и [Cloudflare Tunnel](https://www.cloudflare.com/products/tunnel/) для того, чтобы принимать Telegram webhooks с помощью вашего локального сервера.

- Используйте [Telegram BotFather](https://t.me/BotFather) для создания нового бота и получения его токена.
  - Перейдите в "Bot Settings > Group Admin Rights" и активируйте все права для вашего бота.
  - Перейдите в "Bot Settings > Channel Admin Rights" и активируйте все права для вашего бота.
- Установите `PARAM_BASE_URL` в `.env` для вашего открытого URL с HTTPS.
- (опционально) Для избежания проблем с Telegram (например, при блокировке), используйте бесплатные анонимные прокси ([список 1](https://www.firexproxy.com/en), [список 2](https://mtpro.xyz/socks5)) и установите `PARAM_BOT_PROXY` в `.env`.
- Установите `PARAM_BOT_USERNAME` и `PARAM_BOT_TOKEN` в `.env` для вашего бота.
- (опционально) Установите `PARAM_BOT_OSW_LOGS_GROUP_ID` в `.env` для ID вашей группы с логами ошибок.

Активировать Telegram webhook для бота:
```
php yii telegram-bot/enable-webhook
```

Деактивировать Telegram webhook для бота:
```
php yii telegram-bot/disable-webhook
```
