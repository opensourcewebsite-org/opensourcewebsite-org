# Contributing Guidelines

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

- `master` is the latest, deployed version

## Contribute to the core code or bug fixes

### Your First Code Contribution

Start by looking through these issues:

- [Beginner issues](https://github.com/opensourcewebsite-org/opensourcewebsite-org/issues?q=is%3Aopen+is%3Aissue+label%3A%22good+first+issue%22+sort%3Acomments-desc) - issues which should only require a few lines of code, and a test or two. Issues are sorted by total number of comments. While not perfect, number of comments is a reasonable proxy for impact a given change will have.
- TODO issues - find comments with keyword `TODO` in the source code, with a description of a issue, and suggestions to resolve it.

## Contribute/translate to documentations or messages

You can help improve documentations/translations by making them more coherent, consistent, or readable, adding missing information, correcting factual errors, fixing typos.

Please read how [Internationalization (I18N)](https://www.yiiframework.com/doc/guide/2.0/en/tutorial-i18n) works.

[All translations](https://github.com/opensourcewebsite-org/opensourcewebsite-org/tree/master/messages) are in source files.

To do so, make changes to source files. Then open a pull request to apply your changes to `master` branch.

To help our CI servers you should add `[ci skip]` to your documentation commit message to skip build on that commit. Please remember to use it for commits containing only documentation changes.

## Style Guides

### Git Commit Messages

- Include an issue number to the beginning of the first line (if applicable)

Example `#234 YOUR_COMMIT_NAME`

- Use the present tense ("Add feature" not "Added feature")
- Use the imperative mood ("Move cursor to..." not "Moves cursor to...")
- In case changing only texts or documentations include `[ci skip]` to the end of the first line
- Limit the first line to 72 characters or less
- Reference issues and pull requests liberally after the first line

### Documentation Style Guide

All `*.md` files must adhere to [Markdown Syntax](https://www.markdownguide.org/basic-syntax/).

### PHP Style Guide

PHP Code MUST adhere to [Yii 2 Web Framework Coding Standard Style](https://github.com/yiisoft/yii2-coding-standards), [PHP Standards Recommendations](https://www.php-fig.org/psr/), [Clean Code PHP](https://github.com/jupeter/clean-code-php).

Recommended IDE:
  - [Atom](https://atom.io)
    - [Atom package for Yii Framework 2](https://atom.io/packages/atom-yii2)
    - [Atom package for EditorConfig](https://atom.io/packages/editorconfig)
    - [Atom package for PHP Linter](https://atom.io/packages/linter-php)
    - [IDE-PHP package](https://atom.io/packages/ide-php)
    - [Atom-Beautify package](https://atom.io/packages/atom-beautify)
      - [PHP-CS-Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer). The beautifier uses `.php_cs` file.
        - Go to "File > Settings > Packages > atom-beautify > Settings > PHP". To automatically beautify PHP code on file save toggle `Beautify On Save` option and select `PHP-CS-Fixer` as Default Beautifier.
        - Go to "File > Settings > Packages > atom-beautify > Settings > Executable > PHP-CS-Fixer". Add Binary/Script Path like `ABSOLUTE_PATH_TO_PROJECT_DIR/vendor/bin/php-cs-fixer`.
  - [PhpStorm](https://www.jetbrains.com/phpstorm/)
    - [PHP-CS-Fixer](https://www.jetbrains.com/help/phpstorm/using-php-cs-fixer.html)
    - [SonarLint for PhpStorm](https://www.sonarlint.org/intellij). To automatically check a code style and formatting, enable the settings in the commit window `Before commit > Perform SonarLint analysis`.
    - Yii 2 code styles for PhpStorm. [Download the file](https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/yii2.xml) and import to "Settings > Editor > Code Style > PHP > Import Scheme > Intellij IDEA code style XLM".
  - [Eclipse](https://www.eclipse.org)
  	- [SonarLint for Eclipse](https://www.sonarlint.org/eclipse)
  - [VS Code](https://code.visualstudio.com)
    - [PHP-CS-Fixer](https://github.com/junstyle/vscode-php-cs-fixer)
  - [Sublime Text](https://www.sublimetext.com)
    - [PHP-CS-Fixer](https://github.com/benmatselby/sublime-phpcs)

#### Yii 2 migration files

Before to create a migration files use [wwwsqldesigner](https://github.com/ondras/wwwsqldesigner) to prototype your changes for the database. For example you can use https://ondras.zarovi.cz/sql/demo/?keyword=default with any keyword and share the link with other contributors.

To upgrade data in the database, create a migration whose name starts with `upgrade_`. To upgrade the data in the migration, the `safeUp()` method is used, it is forbidden to use data access through the models, only through DAO (yii\db\Command). Use of such migrations is necessary for existing databases, and for all new such migrations will be deleted. In `down()` and `safeDown()`, only deletion of objects of the database structure (tables, fields, keys, indexes) is allowed.

Do not use variables like `$tableName` and `$tableOptions`.

To specify a primary key, use `$this->primaryKey()->unsigned()`. Be sure to use `unsigned()` for those columns where possible.

To add a column with integer values between 0-255, use `$this->tinyInteger()->unsigned()`.

To add a column with integer values in the range 0-65535, use `$this->smallInteger()->unsigned()`.

To add a column with date values in most cases, use the data type `$this->integer()->unsigned()`. Exceptions - if the column will be actively used in mysql requests as a date.

Usually, database tables are named in the singular for listing any objects. For example `user`, but not `users`.

### JavaScript Style Guide

JavaScript Code MUST adhere to [JavaScript Standard Style](https://standardjs.com).

Recommended IDE:
  - [Atom](https://atom.io)
  - [PhpStorm](https://www.jetbrains.com/phpstorm/)
  - [Eclipse](https://www.eclipse.org)
  - [VS Code](https://code.visualstudio.com)
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

In any case when `composer.json` file is updated, add `composer.json` and `composer.lock` files to the same commit.

Each package must contain specific version. Don't use `*` and `@dev` versions.
