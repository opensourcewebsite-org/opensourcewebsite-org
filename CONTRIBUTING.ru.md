# Руководство контрибьютора

[English version](CONTRIBUTING.md)

Прежде всего, спасибо, что нашли время внести вклад!

Ваш вклад увеличивает ваш Рейтинг в нашем сообществе.

Пожалуйста, прочитайте наше [Руководство по архитектуре](ARCHITECTURE.ru.md) и [Инструкцию по установке](INSTALL.ru.md).

## Начнём

При внесении вклада в этот репозиторий, прежде чем вносить изменения, сначала обсудите изменения, которые вы хотите внести, при помощи issue, электронной почты или любым другим способом, с основной командой.

- Удостоверьтесь, что у вас есть [аккаунт GitHub](https://github.com/login).
- Отправьте GitHub issue для вашей проблемы если таковой еще нет.
  - Issue не нужно для незначительных изменений.
- Сделайте [fork](https://help.github.com/en/articles/working-with-forks) репозитория на GitHub.
    - [Настройка удалённого репозитория для fork](https://help.github.com/en/github/collaborating-with-issues-and-pull-requests/configuring-a-remote-for-a-fork)
    - [Синхронизация fork](https://help.github.com/en/github/collaborating-with-issues-and-pull-requests/syncing-a-fork)
      - `git fetch upstream`
      - `git checkout master`
      - `git merge upstream/master`
    - [Слияние (merge) upstream-репозитория в ваш fork](https://help.github.com/en/github/collaborating-with-issues-and-pull-requests/merging-an-upstream-repository-into-your-fork)
      - `git checkout master`
      - `git pull upstream master`
      - Сделать commit слияния (merge)
      - `git push origin master`
- При работе над issue, создайте новую ветку (branch) от `master` названную номером issue или другим именем. Назовите ветку `issue/<issue-number>` или `issue/<custom-name>`. Например, `issue/22` при работе над issue #22.
- Внесите изменения.
  - Следуйте [Руководству по стилю](#style-guides).
  - [Избегайте платформозависимого кода](https://flight-manual.atom.io/hacking-atom/sections/cross-platform-compatibility/).
  -   Добавьте тесты если ваши изменения содержат новые, тестируемые поведения.
  - Сделайте тесты проходимыми (успешными).
- Создайте [pull request](https://help.github.com/en/articles/creating-a-pull-request-from-a-fork) к репозиторию.

### Советы и рекомендации по использованию Git

- [GitHub Cheat Sheet](https://github.com/tiimgreen/github-cheat-sheet)
- [git-tips](https://github.com/git-tips/tips)

### Ключевые ветки

- `master` - последняя, развёртываемая версия.

## Вклад в основной код или исправление ошибок

### Ваш первый вклад в код

Начните с просмотра этих issues:

- [Beginner issues](https://github.com/opensourcewebsite-org/opensourcewebsite-org/issues?q=is%3Aopen+is%3Aissue+label%3A%22good+first+issue%22+sort%3Acomments-desc) - issues которые требуют несколько строк кода, и один или два теста. Issues отсортированы по общему количеству комментариев.Хотя количество комментариев не является идеальным, оно является разумным показателем воздействия, которое окажет данное изменение.
- TODO issues - найдите комментарии, которые начинаются со слова `TODO` в исходном коде, с описанием проблемы, а также с рекомендациями по ее исправлению.

## Вклад/перевод документации или сообщений

Вы можете помочь улучшить документацию/переводы, сделав их более понятными, последовательными или читаемыми, добавив недостающую информацию, исправив ошибки и опечатки.

Чтобы помочь нашим CI-серверам, добавьте `[ci skip]` к вашему commit-сообщению для документации, чтобы пропустить сборку на этом коммите. Пожалуйста, не забудьте использовать это для коммитов, содержащих только изменения документации.

Пожалуйста, прочитайте как работает [Интернационализация (I18N)](https://www.yiiframework.com/doc/guide/2.0/en/tutorial-i18n) и как найти [не переведенные тексты](https://www.yiiframework.com/doc/guide/2.0/en/tutorial-i18n#message-command) в [исходных файлах переводов](https://github.com/opensourcewebsite-org/opensourcewebsite-org/tree/master/messages).

## Руководство по стилям кодирования

### Commit-сообщения Git

- Включает номер issue в начале первой строки (если возможно). Например, `#234 YOUR_COMMIT_NAME`.
- Используйте настоящее время ("Add feature", не "Added feature").
- Используйте повелительное наклонение ("Move cursor to...", не "Moves cursor to...").
- В случае изменения только текста или документации, добавьте `[ci skip]` в конец первой строки
- Ограничьте первую строку в 72 символа или меньше.
- Подробно опишите issues и pull requests после первой строки.

### Руководство стиля документации

Все `*.md` файлы должны придерживаться [Markdown-синтаксис](https://www.markdownguide.org/basic-syntax/).

### Руководство стиля PHP

PHP код ДОЛЖЕН придерживаться [Yii 2 Web Framework Coding Standard Style](https://github.com/yiisoft/yii2-coding-standards), [PHP Standards Recommendations](https://www.php-fig.org/psr/), [Clean Code PHP](https://github.com/jupeter/clean-code-php).

Рекомендуемые IDE:
  - [VS Code](https://code.visualstudio.com)
    - [EditorConfig](https://marketplace.visualstudio.com/items?itemName=EditorConfig.EditorConfig)
    - [PHP](https://marketplace.visualstudio.com/items?itemName=DEVSENSE.phptools-vscode)
    - [PHP-CS-Fixer](https://marketplace.visualstudio.com/items?itemName=junstyle.php-cs-fixer)
      - Unix-like:
        - Перейдите в "Code > Settings > Extensions > php cs fixer > Extension Settings". Для автоматического запуска beautify для PHP-кода при сохранении файла, перейдите к `PHP-cs-fixer: Onsave` в включите чекбокс `Execute PHP CS Fixer on save`.
        - Перейдите в "Code > Settings > Extensions > php cs fixer > Extension Settings". Добавьте `PHP-cs-fixer: Executable Path`, например `${workspaceRoot}/vendor/bin/php-cs-fixer`.
      - Windows:
        - Перейдите в "Code > Settings > Extensions > php cs fixer > Windows Extension Settings". Добавьте `PHP-cs-fixer: Windows Executable Path`, например `${workspaceRoot}/vendor/bin/php-cs-fixer.bat`.
    - [PHP Debug](https://marketplace.visualstudio.com/items?itemName=xdebug.php-debug)
    - [PHP Extension Pack](https://marketplace.visualstudio.com/items?itemName=xdebug.php-pack)
    - [PHP IntelliSense](https://marketplace.visualstudio.com/items?itemName=bmewburn.vscode-intelephense-client)
    - [CodeGPT: Chat & AI Agents](https://marketplace.visualstudio.com/items?itemName=DanielSanMedium.dscodegpt)
      - Установите [Ollama](https://ollama.com/download).
      - Запустите `ollama pull llama3:8b`.
    - [GitHub Copilot](https://marketplace.visualstudio.com/items?itemName=GitHub.copilot)
    - [GitHub Copilot Chat](https://marketplace.visualstudio.com/items?itemName=GitHub.copilot-chat)
    - [GitHub Repositories](https://marketplace.visualstudio.com/items?itemName=GitHub.remotehub)
    - [Open In GitHub](https://marketplace.visualstudio.com/items?itemName=sysoev.vscode-open-in-github)
    - [SonarLint](https://marketplace.visualstudio.com/items?itemName=SonarSource.sonarlint-vscode)
    - [TODO Highlight](https://marketplace.visualstudio.com/items?itemName=wayou.vscode-todo-highlight)
  - [PhpStorm](https://www.jetbrains.com/phpstorm/)
    - [PHP-CS-Fixer](https://www.jetbrains.com/help/phpstorm/using-php-cs-fixer.html)
    - [SonarLint](https://www.sonarlint.org/intellij). Для автоматической проверки стиля кода и его форматирования, включите настройки в окне коммита `Before commit > Perform SonarLint analysis`.
    - Yii 2 code styles для PhpStorm. [Скачайте файл](https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/yii2.xml) и импортируйте в "Settings > Editor > Code Style > PHP > Import Scheme > Intellij IDEA code style XLM".
  - [Eclipse](https://www.eclipse.org)
  	- [SonarLint](https://www.sonarlint.org/eclipse)
  - [Sublime Text](https://www.sublimetext.com)
    - [PHP-CS-Fixer](https://github.com/benmatselby/sublime-phpcs)

#### Файлы миграции Yii 2

https://www.yiiframework.com/doc/api/2.0/yii-db-migration

Перед созданием файлов миграции, используйте [wwwsqldesigner](https://github.com/ondras/wwwsqldesigner) для создания прототипа изменений для базы данных. Например, вы можете использовать https://ondras.zarovi.cz/sql/demo/?keyword=default с любым ключевым словом и поделиться ссылкой с другими контрибьюторами.

##### safeUp()

https://www.yiiframework.com/doc/api/2.0/yii-db-migration#safeUp()-detail

- Называйте таблицы базы данных в единственном числе, чтобы перечислить любые объекты. Например, `user`, но не` users`.
- Не используйте переменные типа `$tableName` и `$tableOptions`.
- Не используйте комментарии базы данных.
- Добавьте первичный ключ в каждую новую таблицу. [Почему это важно?](https://federico-razzoli.com/primary-key-in-innodb)

Cтолбцы:
- Первичный ключ с целым числом - используйте `$this->primaryKey()->unsigned()`.
- Целочисленные значения в диапазоне 0-255 - используйте `$this->tinyInteger()->unsigned()`.
- Целочисленные значения в диапазоне 0-65535 - используйте `$this->smallInteger()->unsigned()`.
- Целочисленные значения выше 65535 - используйте `$this->integer()->unsigned()`.
- Значения datetime или timeshtamp - используйте `$this->integer()->unsigned()`.
- Не указывайте длину (length) для столбцов с целочисленными значениями.
- Значения с плавающей точкой, где необходима точность, например деньги или координаты - используйте `$this->decimal($precision, $scale)->unsigned()`. Избегайте использования типа float для столбцов без явной необходимости, так как данный тип является не точным.

##### safeDown()

https://www.yiiframework.com/doc/api/2.0/yii-db-migration#safeDown()-detail

Используйте только для удаления объектов структуры базы данных (таблиц, полей, ключей, индексов). После создания новой миграции, проверьте можно ли откатить ее и применить снова.

##### Апгрейд (upgrade) данных в базе данных

Для апгрейда (upgrade) данных в базе данных, создайте миграцию, название которой начинается на `upgrade_`. Используйте доступ к данным только через DAO (yii\db\Command), а не через модели. Эти миграции необходимы для существующих баз данных.

### Руководство стиля JavaScript

JavaScript код ДОЛЖЕН придерживаться [JavaScript Standard Style](https://standardjs.com).

Рекомендуемые IDE:
  - [VS Code](https://code.visualstudio.com)
  - [PhpStorm](https://www.jetbrains.com/phpstorm/)
  - [Eclipse](https://www.eclipse.org)
  - [Sublime Text](https://www.sublimetext.com)

- Предпочтительнее использовать spread-оператор (`{...anotherObj}`) вместо `Object.assign()`
- `export`-выражение где это возможно

```javascript
  // Используйте это:
  export default class ClassName {

  }

  // А не это:
  class ClassName {

  }
  export default ClassName
```

### Composer

https://getcomposer.org/doc/04-schema.md

Во всех случаях, когда файл `composer.json` обновлен, добавьте файлы `composer.json` и `composer.lock` в один и тот же коммит.

Каждый подключаемый пакет должен содержать определенную версию. Не используйте версии `*` и `@dev`.

### Принципы и рекомендации по программированию

- [KISS principle (keep it simple, stupid)](https://en.wikipedia.org/wiki/KISS_principle)
- [Don't repeat yourself (DRY)](https://en.wikipedia.org/wiki/Don%27t_repeat_yourself)
- [You aren't gonna need it (YAGNI)](https://en.wikipedia.org/wiki/You_aren%27t_gonna_need_it)
- [Worse is better](https://en.wikipedia.org/wiki/Worse_is_better)
- [SOLID](https://en.wikipedia.org/wiki/SOLID)
- [GRASP](https://en.wikipedia.org/wiki/GRASP_(object-oriented_design))
