# Contribution Guidelines

[Русская версия](CONTRIBUTING.ru.md)

First off, thanks for taking the time to contribute!

Your contributions increase your Rating in our community.

Please read through our [Architecture Overview](ARCHITECTURE.md) and [Installation Instructions](INSTALL.md).

## Getting Started

When contributing to this repository, please first discuss the change you wish to make via issue, email, or any other method with the core team before making a change.

- Make sure you have a [GitHub account](https://github.com/login).
- Submit a GitHub issue for your issue if one does not already exist.
  - A issue is not necessary for trivial changes.
- [Fork](https://help.github.com/en/articles/working-with-forks) the repository on GitHub.
    - [Configuring a remote for a fork](https://help.github.com/en/github/collaborating-with-issues-and-pull-requests/configuring-a-remote-for-a-fork)
    - [Syncing a fork](https://help.github.com/en/github/collaborating-with-issues-and-pull-requests/syncing-a-fork)
      - `git fetch upstream`
      - `git checkout master`
      - `git merge upstream/master`
    - [Merging an upstream repository into your fork](https://help.github.com/en/github/collaborating-with-issues-and-pull-requests/merging-an-upstream-repository-into-your-fork)
      - `git checkout master`
      - `git pull upstream master`
      - Commit the merge
      - `git push origin master`
- When working on an issue, create a new branch from `master` named for issue number or custom name. Name the branch `issue/<issue-number>` or `issue/<custom-name>`. For example `issue/22` for fixing issue #22.
- Make your changes.
  - Follow the [Style Guides](#style-guides).
  - [Avoid platform-dependent code](https://flight-manual.atom.io/hacking-atom/sections/cross-platform-compatibility/).
  - Add tests if your changes contains new, testable behavior.
  - Make the tests pass.
- Create a [pull request](https://help.github.com/en/articles/creating-a-pull-request-from-a-fork) to the repository.

### Tips and tricks for using the Git

- [GitHub Cheat Sheet](https://github.com/tiimgreen/github-cheat-sheet)
- [git-tips](https://github.com/git-tips/tips)

### Key branches

- `master` is the latest, deployed version.

## Contribute to the core code or bug fixes

### Your First Code Contribution

Start by looking through these issues:

- [Beginner issues](https://github.com/opensourcewebsite-org/opensourcewebsite-org/issues?q=is%3Aopen+is%3Aissue+label%3A%22good+first+issue%22+sort%3Acomments-desc) - issues which should only require a few lines of code, and a test or two. Issues are sorted by total number of comments. While not perfect, number of comments is a reasonable proxy for impact a given change will have.
- TODO issues - find comments with keyword `TODO` in the source code, with a description of a issue, and suggestions to resolve it.

## Contribute/translate to documentations or messages

You can help improve documentations/translations by making them more coherent, consistent, or readable, adding missing information, correcting factual errors, fixing typos.

To help our CI servers add `[ci skip]` to your documentation commit message to skip build on that commit. Please remember to use it for commits containing only documentation changes.

Please read how [Internationalization (I18N)](https://www.yiiframework.com/doc/guide/2.0/en/tutorial-i18n) works and how to [find not translated texts](https://www.yiiframework.com/doc/guide/2.0/en/tutorial-i18n#message-command) in [translation source files](https://github.com/opensourcewebsite-org/opensourcewebsite-org/tree/master/messages).

## Yii Gii

Using Gii to auto-generate code is simply a matter of entering the right information per the instructions shown on the Gii Web pages.

- https://www.yiiframework.com/doc/guide/2.0/en/start-gii

## Cloud AI Code Helpers

- https://chatgpt.com
- https://gemini.google.com
- https://claude.ai
- https://chat.reka.ai
- https://www.perplexity.ai

## Style Guides

### Git Commit Messages

- Include an issue number to the beginning of the first line (if applicable). Example `#234 YOUR_COMMIT_NAME`.
- Use the present tense ("Add feature" not "Added feature").
- Use the imperative mood ("Move cursor to..." not "Moves cursor to...").
- In case changing only texts or documentations include `[ci skip]` to the end of the first line.
- Limit the first line to 72 characters or less.
- Reference issues and pull requests liberally after the first line.

### Documentation Style Guide

All `*.md` files must adhere to [Markdown Syntax](https://www.markdownguide.org/basic-syntax/).

### PHP Style Guide

PHP Code MUST adhere to [Yii 2 Web Framework Coding Standard Style](https://github.com/yiisoft/yii2-coding-standards), [PHP Standards Recommendations](https://www.php-fig.org/psr/), [Clean Code PHP](https://github.com/jupeter/clean-code-php).

Recommended IDE:
  - [VS Code](https://code.visualstudio.com)
    - [EditorConfig](https://marketplace.visualstudio.com/items?itemName=EditorConfig.EditorConfig)
    - [PHP](https://marketplace.visualstudio.com/items?itemName=DEVSENSE.phptools-vscode)
    - [PHP-CS-Fixer](https://marketplace.visualstudio.com/items?itemName=junstyle.php-cs-fixer)
      - Unix like:
        - Go to "Code > Settings > Extensions > php cs fixer > Extension Settings". To automatically beautify PHP code on file go to `PHP-cs-fixer: Onsave` and turn on `Execute PHP CS Fixer on save` checkbox.
        - Go to "Code > Settings > Extensions > php cs fixer > Extension Settings". Add `PHP-cs-fixer: Executable Path` like `${workspaceRoot}/vendor/bin/php-cs-fixer`.
      - Windows:
        - Go to "Code > Settings > Extensions > php cs fixer > Extension Settings". Add `PHP-cs-fixer: Windows Executable Path` like `${workspaceRoot}/vendor/bin/php-cs-fixer.bat`.
    - [PHP Debug](https://marketplace.visualstudio.com/items?itemName=xdebug.php-debug)
    - [PHP Extension Pack](https://marketplace.visualstudio.com/items?itemName=xdebug.php-pack)
    - [PHP IntelliSense](https://marketplace.visualstudio.com/items?itemName=bmewburn.vscode-intelephense-client)
    - [CodeGPT: Chat & AI Agents](https://marketplace.visualstudio.com/items?itemName=DanielSanMedium.dscodegpt)
      - Install [Ollama](https://ollama.com/download).
      - Run `ollama pull llama3:8b`.
    - [GitHub Copilot](https://marketplace.visualstudio.com/items?itemName=GitHub.copilot)
    - [GitHub Copilot Chat](https://marketplace.visualstudio.com/items?itemName=GitHub.copilot-chat)
    - [GitHub Repositories](https://marketplace.visualstudio.com/items?itemName=GitHub.remotehub)
    - [Open In GitHub](https://marketplace.visualstudio.com/items?itemName=sysoev.vscode-open-in-github)
    - [SonarLint](https://marketplace.visualstudio.com/items?itemName=SonarSource.sonarlint-vscode)
    - [TODO Highlight](https://marketplace.visualstudio.com/items?itemName=wayou.vscode-todo-highlight)
  - [Cursor](https://cursor.sh)
  - [Project IDX](https://idx.google.com)
  - [PhpStorm](https://www.jetbrains.com/phpstorm/)
    - [PHP-CS-Fixer](https://www.jetbrains.com/help/phpstorm/using-php-cs-fixer.html)
    - [SonarLint](https://www.sonarlint.org/intellij). To automatically check a code style and formatting, enable the settings in the commit window `Before commit > Perform SonarLint analysis`.
    - Yii 2 code styles for PhpStorm. [Download the file](https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/yii2.xml) and import to "Settings > Editor > Code Style > PHP > Import Scheme > Intellij IDEA code style XLM".
  - [Eclipse](https://www.eclipse.org)
  	- [SonarLint](https://www.sonarlint.org/eclipse)
  - [Sublime Text](https://www.sublimetext.com)
    - [PHP-CS-Fixer](https://github.com/benmatselby/sublime-phpcs)

#### Yii 2 migration files

https://www.yiiframework.com/doc/api/2.0/yii-db-migration

Before to create a migration files use [wwwsqldesigner](https://github.com/ondras/wwwsqldesigner) to prototype your changes for the database. For example you can use https://ondras.zarovi.cz/sql/demo/?keyword=default with any keyword and share the link with other contributors.

##### safeUp()

https://www.yiiframework.com/doc/api/2.0/yii-db-migration#safeUp()-detail

- Name database tables in the singular to list any objects. For example, `user`, but not` users`.
- Do not use variables like `$tableName` and `$tableOptions`.
- Do not use database comments.
- Add primary key to each new table. [Why it so important?](https://federico-razzoli.com/primary-key-in-innodb)

Columns:
- Primary key with integer - use `$this->primaryKey()->unsigned()`.
- Integer values between 0-255 - use `$this->tinyInteger()->unsigned()`.
- Integer values between 0-65535 - use `$this->smallInteger()->unsigned()`.
- Integer values above 65535 - use `$this->integer()->unsigned()`.
- Datetime or timeshtamp values - use `$this->integer()->unsigned()`.
- Do not specify a length for columns with integer values.
- Floating-point values where precision is needed, such as money or coordinates - use `$this->decimal($precision, $scale)->unsigned()`. Avoid using float columns without explicit necessity, as this type is not exact.

##### safeDown()

https://www.yiiframework.com/doc/api/2.0/yii-db-migration#safeDown()-detail

Use only for deletion of objects of the database structure (tables, fields, keys, indexes). After creating a new migration, check if it can be rolled back and applied again.

##### Upgrade data in the database

To upgrade data in the database, create a migration whose name starts with `upgrade_`. Use data access only through DAO (yii\db\Command), not through the models. These migrations are required for existing databases.

### JavaScript Style Guide

JavaScript Code MUST adhere to [JavaScript Standard Style](https://standardjs.com).

Recommended IDE:
  - [VS Code](https://code.visualstudio.com)
  - [PhpStorm](https://www.jetbrains.com/phpstorm/)
  - [Eclipse](https://www.eclipse.org)
  - [Sublime Text](https://www.sublimetext.com)

- Prefer the object spread operator (`{...anotherObj}`) to `Object.assign()`
- Inline `export`s with expressions whenever possible

```javascript
  // Use this:
  export default class ClassName {

  }

  // Instead of:
  class ClassName {

  }
  export default ClassName
```

### Composer

https://getcomposer.org/doc/04-schema.md

In all cases when `composer.json` file is updated, add ` composer.json` and `composer.lock` files to the same commit.

Each package must contain specific version. Don't use `*` and `@dev` versions.

### Programming principles and recommendations

- [KISS principle (keep it simple, stupid)](https://en.wikipedia.org/wiki/KISS_principle)
- [Don't repeat yourself (DRY)](https://en.wikipedia.org/wiki/Don%27t_repeat_yourself)
- [You aren't gonna need it (YAGNI)](https://en.wikipedia.org/wiki/You_aren%27t_gonna_need_it)
- [Worse is better](https://en.wikipedia.org/wiki/Worse_is_better)
- [SOLID](https://en.wikipedia.org/wiki/SOLID)
- [GRASP](https://en.wikipedia.org/wiki/GRASP_(object-oriented_design))
