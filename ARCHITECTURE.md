# Architecture

[Русская версия](ARCHITECTURE.ru.md)

## Website

## Telegram Bot

The bot deletes previous messages in private chat that are not related to the current request. In this case, the `editMessageTextOrSendMessage` command is used to display the message. If you need to send several messages (for example, display a text message "Your geolocation", then send a message with a geolocation, and then a message with a keyboard for editing or return), then you must first call the editMessageTextOrSendMessage command, and then sendLocation and sendMessage.

The bot deletes its previous messages in a private chat with a user that are not related to the current request, and deletes all user messages. In this case, the command `editMessageTextOrSendMessage` is used to display the bot message. When you need to send several messages (for example, first display the text message “Your Location”, then send a message with geolocation, and then a message with a keyboard), you should first call the command `editMessageTextOrSendMessage`, and then the commands `sendLocation` and `sendMessage`.

## Core

- working with `float` in PHP you **MUST** always use [BC Math Functions](https://www.php.net/manual/en/ref.bc.php).
    - For handy comparison of two floats use `\app\helpers\Number`.
    - [Why it so important?](https://stackoverflow.com/questions/3148937/compare-floats-in-php)
