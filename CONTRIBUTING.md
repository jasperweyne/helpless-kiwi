# Contributing to Helpless Kiwi

Thank you for taking the time to contribute! Helpless Kiwi is a free and open
source project, which means that everyone may and is encouraged to contribute to
it. This document outlines a set of guidelines for contributing to the project.
These guidelines are not set in stone; use your best judgement, and if you feel
they're incomplete or otherwise lacking, please feel free to propose changes or
additions by opening an issue.

## Index

* [Introduction](#introduction)
* [Issues and bugs](#issues-and-bugs)
* [Feature requests](#feature-requests)
* [Pull requests](#pull-requests)
* [Commits](#commits)
* [Writing tests](#writing-tests)
* [Directory structure](#directory-structure)

## Introduction

Welcome to the project! By contributing to or by participating in the project,
you agree to follow our [Code of Conduct](CODE_OF_CONDUCT.md).

Please note that this project is licensed under the Apache license version 2.0.
Therefore, your contributions shall be under the Apache license version 2.0
unless explicitly stated otherwise. For more information, refer to the [license](LICENSE).

When writing code or otherwise modifying the contents of the project, it's
strongly advised to have an installation ready of the project. You can either
install the project locally (please refer to the [installation guide](INSTALL.md)), 
or use Gitpod, which deploys a IDE in your browser, containing all necessary
tools and dependencies for you to work on this project.

[![Open in Gitpod](https://gitpod.io/button/open-in-gitpod.svg)](https://gitpod.io/#https://github.com/jasperweyne/helpless-kiwi)

## Issues and bugs

Bugs reports are always welcome! They may be filed through our issue tracker.

Issues in the issue tracker should consist of a human-readable, descriptive
title of the problem. Ideally, the title should serve as a reminder of the bug
description when a project maintainer has read the issue before. Generally
speaking, titles should only contain alphanumerical characters.

Issue descriptions should follow their respective template, these are inserted
automatically when opening a new issue. 

## Feature requests

Feature requests may be submitted in the form of an issue as well, please read
the [issue guidelines](#issues-and-bugs). Feature requests that motivate how
their addition would be valuable to all users of Kiwi will receive priority of
the team. Generally speaking, all feature requests should have some form of
applicability to the majority of users and will likely be rejected otherwise.
This is to make sure the project is kept maintainable. Please note that for
these reasons, a design reconsideration of a (new) featured is preferred over
hiding it behind a config option.

Since this is free and open source software, this project may be used freely and
without any expectation of contribution from you, the user. However, the time
available for the maintenance and development of the project is limited.
Therefore, even if a potential feature would be universally deemed highly
valuable, an implementation by the team should not be expected. Ideally, your
feature request should be accompanied by the commitment to implement a pull
request it yourself when it's accepted, if you're able to.

## Pull requests

Thank you for your efforts in developing Kiwi, they're valued immensely. Kiwi
welcomes pull requests to add tests, fix bugs and to implement features. Before
starting a pull request, always make sure it's related to an issue that is
linked to an open project. This way, the team has agreed with the intent of your
PR, before work on actual code has been started, which reduces the chances your
pull request will be rejected.

To begin working on a pull request, fork the repository and develop your PR,
while keeping the guidelines below in mind. The core team members are added as
collaborators to the main repository, but PRs coming from any source will
receive the attention of the team. Repository access is therefore not necessary
and should not be asked for.

Your pull request must contain adequate testing for the methods you've editted.
Note that this restriction only applies to code in the src directory. If a
method you've modified doesn't have a test case yet, please add a basic unit
test. Make sure the behaviours you've changed are tested properly. A unit test
may use other classes freely during testing, as these are assumed to be tested
with their own unit tests.

Kiwi is collaborative project, developed by different people with different
opinions. To stimulate a healthy environment for collaboration, some basic rules
should guide your decisions:

* Make everybody happier, especially those responsible for maintaining the
  project.
* Prevent some of the 'Oops' moments.
* Increase the general level of good will on planet Earth.

Kiwi uses a rolling release cycle, to simplify the development process.
Therefore, all new pull requests must be created from the develop branch. Please
make sure your local git repository is up-to-date before creating a fork or new
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

## Commits

This project has been written using the Symfony framework. In general, it's
recommended to follow their [best practices](https://symfony.com/doc/current/best_practices.html).

We follow the PSR-12 code style, please [review](https://www.php-fig.org/psr/psr-12/)
these before starting a PR. Note that the git hooks run the code style fixer,
which should catch some (but not all!) issues before performing a commit.
Please note that when a pull request contains code style fixes without
functional modifications on (parts of) the codebase, these pull requests shall
be rejected. Fixes are done by the core team when appropriate to avoid causing
too many unnecessary conflicts between branches and pull requests.

Lastly, consider the next few rules when committing files:

1. Do not commit multiple files and dump all messages in one commit. If you
   modified several unrelated files, commit each group separately and provide a
   nice commit message for each one.

2. Do write your commit message in such a way that it makes sense even without
   the corresponding diff. One should be able to look at it, and immediately
   know what was modified. Definitely include the function name in the message
   as shown below.
   
The format of the commit message is simple:

    <approx. 50 characters title>\n
    \n
    <description, multiple lines>
    \n

All lines in a commit message should have a maximum line length of 72 characters
(unless citing text, e.g. a stack trace). Please refer to issue IDs in your
commit messages when applicable, particularly when fixing bugs. Issue IDs should
be prefixed by `#`. An example:

    Fixed duplicate registrations for activities (#157)
    
    When registering a participant as and admin or organiser, there was no
    check if this person was registered already. I've added this check in
    the (..) and (..) files. The registration will now be refused and a
    warning will be generated, informing the organiser.

## Writing tests

We love tests! Kiwi is a big project, and code coverage improvements help the
stability and maintainability of the project. Please refer to the
[Symfony Testing documentation](https://symfony.com/doc/current/testing.html)
for more information and guidance on how to write your tests. Note that when
running tests, the pdo_sqlite extension must be installed if the database is not
configured locally.

Three types of tests are included in Kiwi: functional tests, integration tests
and unit tests. Functional tests are reserved for testing controllers and
commands, and test the full integration of the software from a user-like
interface.

For all other classes in src, unit tests and optionally integration tests should
be written as well. Unit tests are independent of the behaviour of dependencies,
while integration tests should explicitly test whether the code correctly
interacts with its dependencies.

These tests should test "expected" behaviour. In practice, this means the happy
path. However, if a sad path is expected (eg. an exception is a normal type of
result), than this should be tested as well. Multiple asserts may be placed in a
test, but these asserts should be all semantically related to each other, and a
message must be added to indicate the problem. It is encourage to structure test
code to an Arrange/Act/Assert structure.

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
 └─ tests/                          # Automatic tests (e.g. Unit tests)
    ├─ Functional/                  # Tests the fully-integrated functionality of routes (Controllers) and commands
    └─ Helper/                      # Helper code for running any type of test tests
       └─ Database/                 # Contains the database fixtures used during testing
    └─ Integration/                 # Integration tests of classes and methods, with their dependencies attached
    └─ Unit/                        # Unit tests of individual classes and methods (dependencies should be mocked)
 └─ templates/                      # Twig templates for HTML generation
    ├─ email/                       # E-mail templates (should not extend templates/layout.html)
    ├─ layout.html                  # Base HTML template for website, all other templates should extend it (or a child template)
    └─ ...                          # Web-facing templates
 ├─ var/                            # Runtime generated files (cache, logs, etc.)
 └─ vendor/                         # Third-party PHP dependencies, generated by composer
```

Thank you for contributing to Helpless Kiwi!
