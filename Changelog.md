
n.n.n / 2024-04-04
==================

  * add: more examples
  * feat: use gc
  * fix: version

v5.3.0 / 2023-12-09
===================

  * update docs
  * add: command /usage
  * add: declare strict_types

v5.3-beta / 2023-12-08
======================

  * feat: run commands in parallel
  * add method onInvalidFilters
  * add fallback command

v5.2.1 / 2023-12-08
===================

  * fix: filter name
  * rename: FilterMessageSticker to FilterMessageMediaSticker
  * fixed: filter message media

v5.2.0 / 2023-11-20
===================

  * add: Param disableStateCheck
  * Add webhook example file
  * Force the use of logger in the creation of the bot

v5.1.5 / 2023-11-08
===================

  * minor changes

v5.1.4 / 2023-11-03
===================

  * Add ordered_imports rule
  * Add debug message log:

v5.1.3.1 / 2023-11-01
=====================

  * Update install command

v5.1.3 / 2023-11-01
===================

  * add FilterNot

v5.1.2 / 2023-11-01
===================

  * add method PhpNativeStream::getFileName
  * add filter tests
  * fixed some cs-fixer linter errors

v5.1.1 / 2023-10-27
===================

  * Add php-cs-fixer
  * Update docs

v5.1 / 2023-10-09
=================

  * Add filter message media
  * update to mateodioev/tgbot v4.2 stable
  * Update examples and docs
  * add methods EventInterface::api EventInterface::ctx
  * add BotException as base excepcion class

v5.0.1 / 2023-09-10
===================

  * Fixed not registered event in EventStorage

v4.2.2 / 2023-12-01
===================

  * fixed: find unknown events in method EventStorage::resolve

v5.0.0 / 2023-09-10
===================

  * Add event storage
  * Update to mateodioev/tgbot v4.1.0

v4.2 / 2023-09-09
=================

  * Update to mateodioev/tgbot v3.6
  * The methods of the Context class were accelerated

v4.1.4 / 2023-08-09
===================

  * Use STDOUT resource in TerminalStream
  * Change default stream logger to TerminalStream
  * Minor changes

v4.1.3.1 / 2023-08-03
=====================

  * fixed log level in stream loggers

v4.1.3 / 2023-08-03
===================

  * Add log level to streams

v4.1.2.1 / 2023-08-02
=====================

  * update psr-4 name

v4.1.2 / 2023-08-02
===================

  * Add logical filters

v4.1.1 / 2023-08-02
===================

  * rename class

v4.1.0 / 2023-08-02
===================

  * add Filters as attributes
  * minimum version of php 8.1

v4.0.9 / 2023-07-29
===================

  * upps

v4.0.8 / 2023-07-29
===================

  * Add containers

v4.0.7 / 2023-07-28
===================

  * Fixed bug in Context::eventType

v4.0.6 / 2023-07-28
===================

  * Ignore directories examples and tests
  * Add method Bot::findExceptionHandler
  * Validate errors in long polling

v4.0.5 / 2023-06-30
===================

  * Upps, fixed

v4.0.4 / 2023-06-29
===================

  * Fixed token validation

v4.0.3 / 2023-06-26
===================

  * add RunState class
  * add BotConfig class

v4.0.2 / 2023-06-25
===================

  * discarded changes:
  * refactor code
  * add TemporaryEvent

v4.0.1 / 2023-06-25
===================

  * add database

v4.0.0-beta / 2023-06-25
========================

  * Update readme
  * add conversation format
  * add support for conversations

v3.5.0 / 2023-06-25
===================

  * update regex
  * update docs
  * add support for params in command:
  * add Db

v3.4.05 / 2023-06-08
====================

  * fixed regex in case of line break

v3.4.04 / 2023-06-08
====================

  * implementations of isValid has been simplefied
  * fixed case sensitive param

v3.4.03 / 2023-05-15
====================

  * fixed long events
  * Update Logger.php

v3.4.02 / 2023-05-09
====================

  * use method getReduced
  * support "All" types
  * add tests

v3.4.0 / 2023-04-18
===================

  * update docs
  * add log levels
  * add string formatter

v3.3.1 / 2023-04-08
===================

  * update to new api

v3.3.0 / 2023-04-05
===================

  * add message event example
  * fixed event namespace
  * change method on to onEvent
  * add legacy methods
  * add event support
  * add events
  * save event type
  * add method eventType
  * add method getUser
  * add missing arguments

v3.2.2 / 2023-04-01
===================

  * _
  * add message command from closures
  * change default logger

v3.2.1 / 2023-04-01
===================

  * typo
  * add method CommandInterface::isValid
  * remove bot token

v3.2.0 / 2023-04-01
===================

  * now full async
  * check bot token in longPolling

v3.1.3 / 2023-03-17
===================

  * Update middlewares.php

v3.1.1 / 2023-03-13
===================

  * make executeCommand public

v3.1 / 2023-03-12
=================

  * update docs
  * add async support

v3.0-beta / 2023-03-08
======================

  * update docs
  * add support for stopCommand
  * add middleware support

v2.3 / 2023-02-19
=================

  * delete invalid token
  * add callback_query handler
  * Refactor regex pattern in CallbackCommand
  * use null safe operator
  * remove invalid bot token

v2.2 / 2023-02-16
=================

  * update examples
  * add exception handlers

v2.1.2 / 2023-02-16
===================

  * add method getPayload

v2.1.1 / 2023-02-12
===================

  * Unused dependency removed

v2.1 / 2023-02-11
=================

  * add bulk stream and php native and terminal stream

v2.0 / 2023-01-31
=================

  * update examples
  * get logger from commands
  * add psr/log
  * add loggers

v2.0-beta / 2023-01-30
======================

  * update docs
  * update docs
  * delete examples
  * minor changes
  * add getters helpers
  * update mateodioev/tgbot to v3.0
  * handler
  * commands api
  * move Updates to Context class
  * refactor init

v1.1.3 / 2022-12-30
===================

  * Duplicate handles

v1.1.2 / 2022-10-21
===================

  * update mateodioev/tgbot
  * Delete composer.lock

v1.1.1 / 2022-09-30
===================

  * Merge branch 'main' of github.com:Mateodioev/telegram-bot-handler
  * updated dependencies

v1.1 / 2022-09-25
=================

  * Update Runner.php
  * dependencies
  * run

v1.0 / 2022-08-22
=================

  * doc
  * Merge pull request #1 from Mateodioev/add-license-1
  * Create LICENSE
  * examples use
  * first commit
