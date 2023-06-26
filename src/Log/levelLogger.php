<?php

namespace Mateodioev\TgHandler\Log;

trait levelLogger
{
    /**
     * System is unusable.
     *
     * @param string|\Stringable $message
     * @param array  $context
     *
     * @return void
     */
    public function emergency(string|\Stringable $message, array $context = []): void
    {
        $this->log(Logger::EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string|\Stringable $message
     * @param array  $context
     *
     * @return void
     */
    public function alert(string|\Stringable $message, array $context = []): void
    {
        $this->log(Logger::ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string|\Stringable $message
     * @param array  $context
     *
     * @return void
     */
    public function critical(string|\Stringable $message, array $context = []): void
    {
        $this->log(Logger::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string|\Stringable $message
     * @param array  $context
     *
     * @return void
     */
    public function error(string|\Stringable $message, array $context = []): void
    {
        $this->log(Logger::ERROR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string|\Stringable $message
     * @param array  $context
     *
     * @return void
     */
    public function warning(string|\Stringable $message, array $context = []): void
    {
        $this->log(Logger::WARNING, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string|\Stringable $message
     * @param array  $context
     *
     * @return void
     */
    public function notice(string|\Stringable $message, array $context = []): void
    {
        $this->log(Logger::NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string|\Stringable $message
     * @param array  $context
     *
     * @return void
     */
    public function info(string|\Stringable $message, array $context = []): void
    {
        $this->log(Logger::INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string|\Stringable $message
     * @param array  $context
     *
     * @return void
     */
    public function debug(string|\Stringable $message, array $context = []): void
    {
        $this->log(Logger::DEBUG, $message, $context);
    }
}