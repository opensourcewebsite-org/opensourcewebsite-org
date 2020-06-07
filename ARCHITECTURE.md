# Architecture

[Русская версия](ARCHITECTURE.ru.md)

## Website

## Telegram Bot

## Core

- working with `float` in PHP you **MUST** always use [BC Math Functions](https://www.php.net/manual/en/ref.bc.php).
    - For handy comparison of two floats use `\app\helpers\Number`.
    - [Why it so important?](https://stackoverflow.com/questions/3148937/compare-floats-in-php)
    - If DB table has decimal column - add trait `\app\models\traits\FloatAttributeTrait` into `ActiveRecord`.
