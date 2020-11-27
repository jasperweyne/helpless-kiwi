# Contributing to Helpless Kiwi

Thank you for taking the time to contribute! Helpless Kiwi is a free and open
source project, which means that everyone may and is encouraged to contribute to
it. This document outlines a set of guidelines for contributing to the project.
These guidelines are not set in stone; use your best judgement, and if you feel
they're incomplete or otherwise lacking, please feel free to propose changes or
additions by opening an issue.

## Index

* [Introduction](#introduction)
* [Pull requests](#pull-requests)
* [Directory structure](#directory-structure)

## Introduction

Welcome to the project! By contributing to or by participating in the project,
you agree to follow our [Code of Conduct](CODE_OF_CONDUCT.md).

Please note that this project is licensed under the Apache license version 2.0.
Therefore, your contributions shall be under the Apache license version 2.0
unless explicitly stated otherwise. For more information, refer to the [license](LICENSE).

## Pull requests

Thank you for your efforts in developing Kiwi, they're valued immensely. Before
starting a pull request, always make sure it's related to an issue that is
linked to an open project. This way, the team has agreed with the intent of your
PR, before work on actual code has been started, which reduces the chances your
pull request will be rejected.

Kiwi is collaborative project, developed by different people with different
opinions. To stimulate a healthy environment for collaboration, some basic rules
should guide your decisions:

* Make everybody happier, especially those responsible for maintaining the
  project.
* Prevent some of the 'Oops' moments.
* Increase the general level of good will on planet Earth.

This project has been written using the Symfony framework. In general, it's
recommended to follow their [best practices](https://symfony.com/doc/current/best_practices.html).

Kiwi uses a rolling release cycle, to simplify the development process.
Therefore, all new pull requests must be created from the develop branch. Please
make sure  your local git repository is up-to-date before creating a fork or new
branch, as this reduces the chance of merge conflicts later on. Currently, the
following branches are in use:

| Branch    |                           |
| --------- | ------------------------- |
| develop   | Active development branch |
| master    | Branch that matches the latest release. Only the develop branch may be merged to this branch |
| feature/* | Work-in-progress branches, always forked from develop |

We have some custom git hooks, for running our code style fixer and confirming
all tests pass. These hooks are located in the .hooks directory. When running
`composer install`, they auto-install. However, you can activate them by running
`git config --local core.hooksPath .hooks` as well.

We follow the PSR-12 code style, please [review](https://www.php-fig.org/psr/psr-12/)
these before starting a PR. Note that the git hooks run the code style fixer,
which should catch some (but not all!) issues before performing a commit.
Please note that when a pull request contains code style fixes without
functional modifications on (parts of) the codebase, these pull requests shall
be rejected.

Lastly, consider the next few rules when committing files:

1. Do not commit multiple files and dump all messages in one commit. If you
   modified several unrelated files, commit each group separately and provide a
   nice commit message for each one.

2. Do write your commit message in such a way that it makes sense even without
   the corresponding diff. One should be able to look at it, and immediately
   know what was modified. Definitely include the function name in the message
   as shown below.
   
## Directory structure

Helpless Kiwi consists of multiple folders, each with a prior intended use. When
adding new files, please review the directory structure description to get an
indication where it might be placed best. When additional clarity is required,
please refer to the best practices for the Symfony Framework, as this project is
built upon it.

```bash
<helpless-kiwi>/
 ├─ .git/                           # Git configuration and source directory
 └─ .github/                        # Github specific configuration
    ├─ ISSUE_TEMPLATE/              # Markdown templates for new issues
    └─ workflows/main.yml           # Checks and auto-generator of releases
 ├─ .hooks/                         # The git hooks for this project (installed on composer install)
 └─ assets/                         # Static assets served in runtime 
    ├─ image/                       # Images and other static, non-interactive content
    ├─ script/                      # Javascript files
    └─ style/                       # Css and scss files
 ├─ bin/                            # Project binaries, in general no code should live here
 └─ config/                         # Symfony Framework configuration, no runtime configuration should be placed here
    ├─ packages/                    # Configuration for individual packages and libraries
    ├─ routes/                      # Autowiring of controller routes, in general should not be modified 
    ├─ services.yaml                # Project overrides for default/non-existent Symfony autowiring
    └─ ...                          # Bootstrapping files, in general should not be modified
 ├─ migrations/                     # Database migrations between releases
 ├─ node_modules/                   # Third-party Javascript/Scss dependencies, generated by yarn
 └─ public/                         # The web root directory (in releases, it is moved into the public_html folder)
    ├─ build/                       # Static content compiled from assets/ folder
    ├─ uploads/                     # Dynamic (user-uploaded) files
    ├─ index.php                    # Main entrypoint for all routes 
    └─ ...                          # Additional root files, in general should not be modified
 └─ src/                            # The project’s core (PHP) code
    ├─ Command/                     # Custom commands, executable by bin/console
    ├─ Controller/                  # Web facing routes, configured through annotations 
    ├─ Entity/                      # Models & database structure, configured through annotations
    ├─ Form/                        # Code for generating HTML forms and parsing POST data
    ├─ Repository/                  # Custom database queries
    ├─ Security/                    # Authentication code
    ├─ */...                        # Additional helper classes
    └─ Kernel.php                   # Symfony Kernel file, in general should not be modified
 ├─ tests/                          # Automatic tests (e.g. Unit tests)
 └─ templates/                      # Twig templates for HTML generation
    ├─ email/                       # E-mail templates (should not extend templates/layout.html)
    ├─ layout.html                  # Base HTML template for website, all other templates should extend it (or a child template)
    └─ ...                          # Web-facing templates
 ├─ var/                            # Runtime generated files (cache, logs, etc.)
 └─ vendor/                         # Third-party PHP dependencies, generated by composer
```

Thank you for contributing to Helpless Kiwi!
