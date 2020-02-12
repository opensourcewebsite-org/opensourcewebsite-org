# Contributing Guidelines

First off, thanks for taking the time to contribute!

The following is a set of guidelines for contributing to OpenSourceWebsite, which are hosted in the [OpenSourceWebsite Organization](https://github.com/opensourcewebsite-org) on GitHub. These are mostly guidelines, not rules. Use your best judgment, and feel free to propose changes to this document in a pull request.

This project and everyone participating in it is governed by the [Code of Conduct](CODE_OF_CONDUCT.md).

## Request a new feature, give us feedback or start a design discussion

- **Ensure the feature was not already reported** by searching on GitHub under [Issues](https://github.com/opensourcewebsite-org/opensourcewebsite-org/issues). If it has and the issue is still open, add a comment to the existing issue instead of opening a new one.

- If you're unable to find an open issue addressing the feature, [open a new one](hhttps://github.com/opensourcewebsite-org/opensourcewebsite-org/issues/new). When you are creating an enhancement suggestion, please include as many details as possible. Fill in [the template](ISSUE_TEMPLATE.md), including the steps that you imagine you would take if the feature you're requesting existed.

#### What is a (Good) feature request

- **Use a clear and descriptive title** for the issue to identify the suggestion.
- **Provide a step-by-step description of the suggested enhancement** in as many details as possible.
- **Provide specific examples to demonstrate the steps**. Include copy/pasteable snippets which you use in those examples, as [Markdown code](https://guides.github.com/features/mastering-markdown/).
- **Describe the current behavior** and **explain which behavior you expected to see instead** and why.
- **Include screenshots and animated GIFs** which help you demonstrate the steps or point out the part of Website which the suggestion is related to. You can use [this tool](https://www.cockos.com/licecap/) to record GIFs on macOS and Windows, and [this tool](https://github.com/colinkeenan/silentcast) or [this tool](https://github.com/GNOME/byzanz) on Linux.
- **Explain why this enhancement would be useful** to most Website users.
- **List some other websites where this enhancement exists.**

## Submit a bug report

- **Ensure the bug was not already reported** by searching on GitHub under [Issues](https://github.com/opensourcewebsite-org/opensourcewebsite-org/issues). If it has and the issue is still open, add a comment to the existing issue instead of opening a new one. If you find a closed issue that seems like it is the same thing that you're experiencing, open a new issue and include a link to the original issue in the body of your new one.

- If you're unable to find an open issue addressing the problem, [open a new one](hhttps://github.com/opensourcewebsite-org/opensourcewebsite-org/issues/new). When you are creating a bug report, please include as many details as possible. Fill out [the required template](ISSUE_TEMPLATE.md), the information it asks for helps us resolve issues faster.

#### What is a (Good) bug report

- **Use a clear and descriptive title** for the issue to identify the problem.
- **Describe the exact steps which reproduce the problem** in as many details as possible. When listing steps, **don't just say what you did, but explain how you did it**.
- **Provide specific examples to demonstrate the steps**. Include links to files or GitHub/GitLab projects, or copy/pasteable snippets, which you use in those examples. If you're providing snippets in the issue, use [Markdown code](https://guides.github.com/features/mastering-markdown/).
- **Describe the behavior you observed after following the steps** and point out what exactly is the problem with that behavior.
- **Explain which behavior you expected to see instead and why.**
- **Include screenshots and animated GIFs** which show you following the described steps and clearly demonstrate the problem. You can use [this tool](https://www.cockos.com/licecap/) to record GIFs on macOS and Windows, and [this tool](https://github.com/colinkeenan/silentcast) or [this tool](https://github.com/GNOME/byzanz) on Linux.
- **If the problem wasn't triggered by a specific action**, describe what you were doing before the problem happened and share more information using the guidelines below.

## Contribute to the core code or fix bugs

#### Getting Started

When contributing to this repository, please first discuss the change you wish to make via issue, email, or any other method with the core team before making a change.

- Make sure you have a [GitHub account](https://github.com/login).
- Submit a GitHub issue for your issue if one does not already exist.
  - A issue is not necessary for trivial changes.
- Create a new branch (preferred, if it is available) or [fork](https://help.github.com/en/articles/working-with-forks) the repository on GitHub.
- Make your change. Add tests for your change. Make the tests pass.
- Create a [pull request](https://help.github.com/en/articles/creating-a-pull-request-from-a-fork).

##### Tips and tricks for using the Git

- [GitHub Cheat Sheet](https://github.com/tiimgreen/github-cheat-sheet)
- [git-tips](https://github.com/git-tips/tips)

#### Your First Code Contribution

Unsure where to begin contributing to OpenSourceWebsite? You can start by looking through these `beginner` and `help-wanted` issues:

* [Beginner issues](beginner) - issues which should only require a few lines of code, and a test or two.
* [Help wanted issues](help-wanted) - issues which should be a bit more involved than `beginner` issues.

Both issue lists are sorted by total number of comments. While not perfect, number of comments is a reasonable proxy for impact a given change will have.

#### Pull request process

- Fill in [the required template](PULL_REQUEST_TEMPLATE.md).
- Do not include issue numbers in the pull request title.
- Ensure the pull request description clearly describes the problem and solution. Include the relevant issue number if applicable.
- Pull requests that do not solve an existing issue are essentially un-prioritized–don't expect these to be addressed quickly.
- Try not to pollute your pull request with unintended changes–keep them simple and small.
- Try to share which browsers your code has been tested in before submitting a pull request.
- Include screenshots and animated GIFs in your pull request whenever possible.
- Follow the [JavaScript](#javascript-styleguide) and [PHP](#php-styleguide) styleguides.
- Document new code based on the [Documentation Styleguide](#documentation-styleguide).
- End all files with a newline.
- [Avoid platform-dependent code](https://flight-manual.atom.io/hacking-atom/sections/cross-platform-compatibility/).

#### Making Changes

#### Writing translatable code

#### Making Trivial Changes

#### Submitting Changes

#### Revert Policy

#### Key branches

- `master` is the latest, deployed version

## Contribute/translate to documentations or messages

You can help improve documentations/translations by making them more coherent, consistent, or readable, adding missing information, correcting factual errors, fixing typos.

To do so, make changes to source files. Then open a pull request to apply your changes to master branch.

To help our CI servers you should add `[ci skip]` to your documentation commit message to skip build on that commit. Please remember to use it for commits containing only documentation changes.

## Style Guides

#### Git Commit Messages

- Use the present tense ("Add feature" not "Added feature")
- Use the imperative mood ("Move cursor to..." not "Moves cursor to...")
- Limit the first line to 72 characters or less
- Reference issues and pull requests liberally after the first line
- When only changing documentation, include `[ci skip]` in the commit title
- When there is a issue, include issue number in the commit title (for example: #234 YOUR_COMMIT_NAME).

#### Documentation Style Guide

All \*.md files must adhere to [Markdown Syntax](https://www.markdownguide.org/basic-syntax/)

#### PHP Style Guide

PHP Code MUST adhere to [Yii 2 Web Framework Coding Standard Style](https://github.com/yiisoft/yii2-coding-standards) and [PHP Standards Recommendations](https://www.php-fig.org/psr/).

Recommended IDE:
  * [Atom](https://atom.io)
    * [Atom package for Yii Framework 2](https://atom.io/packages/atom-yii2)
    * [Atom package for EditorConfig](https://atom.io/packages/editorconfig)
	* [Atom package for PHP Linter](https://atom.io/packages/linter-php)
  * [PhpStorm](https://www.jetbrains.com/phpstorm/)
    * [SonarLint for PhpStorm](https://www.sonarlint.org/intellij). To automatically check a code style and formatting, enable the settings in the commit window "Before commit > Perform SonarLint analysis".
	* Yii 2 code styles for import to PhpStorm - [download](https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/yii2.xml). Save the file and import to "Settings > Editor > Code Style > PHP > Import Scheme > Intellij IDEA code style XLM".
  * [Eclipse](https://www.eclipse.org)
	* [SonarLint for Eclipse](https://www.sonarlint.org/eclipse)

#### JavaScript Style Guide

JavaScript Code MUST adhere to [JavaScript Standard Style](https://standardjs.com).

Recommended IDE:
  * [Atom](https://atom.io)
  * [PhpStorm](https://www.jetbrains.com/phpstorm/)
  * [Eclipse](https://www.eclipse.org)

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

#### Composer

In any case when `composer.json` file is updated, add `composer.json` and `composer.lock` files to the same commit.

#### Yii 2 migration files

Before to create a migration files use [wwwsqldesigner](https://github.com/ondras/wwwsqldesigner) to prototype your changes for the database. For example you can use https://ondras.zarovi.cz/sql/demo/?keyword=default with any keyword and share the link with other contributors.

To upgrade data in the database, create a migration whose name starts with `upgrade_`. To upgrade the data in the migration, the `safeUp()` method is used, it is forbidden to use data access through the models, only through DAO (yii\db\Command). Use of such migrations is necessary for existing databases, and for all new such migrations will be deleted. In `down()` and `safeDown()`, only deletion of objects of the database structure (tables, fields, keys, indexes) is allowed.

Do not use variables like `$tableName` and `$tableOptions`.

To specify a primary key, use `$this->primaryKey()->unsigned()`. Be sure to use `unsigned()` for those columns where possible.

To add a column with integer values between 0-255, use `$this->tinyInteger()->unsigned()`.

To add a column with integer values in the range 0-65535, use `$this->smallInteger()->unsigned()`.

To add a column with date values in most cases, you need to use the data type `$this->integer()->unsigned()`. Exceptions - if the column will be actively used in mysql requests as a date.

Usually, database tables are named in the singular for listing any objects. For example `user`, but not `users`.
