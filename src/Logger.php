<?php

/*
 * This file is part of StyleCI.
 *
 * (c) Cachet HQ <support@cachethq.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace StyleCI\Bugsnag;

use Bugsnag_Client as Bugsnag;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Psr\Log\LoggerInterface;

/**
 * This is the logger class.
 *
 * @author Graham Campbell <graham@cachethq.io>
 */
class Logger implements LoggerInterface
{
    /**
     * The bugsnag client instance.
     *
     * @var \Bugsnag_Client
     */
    protected $bugsnag;

    /**
     * Create a new logger instance.
     *
     * @param \Bugsnag_Client $bugsnag
     *
     * @return void
     */
    public function __construct(Bugsnag $bugsnag)
    {
        $this->bugsnag = $bugsnag;
    }

    /**
     * Log an emergency message to the logs.
     *
     * @param mixed $message
     * @param array $context
     *
     * @return void
     */
    public function emergency($message, array $context = [])
    {
        $this->log('emergency', $message, $context);
    }

    /**
     * Log an alert message to the logs.
     *
     * @param mixed $message
     * @param array $context
     *
     * @return void
     */
    public function alert($message, array $context = [])
    {
        $this->log('alert', $message, $context);
    }

    /**
     * Log a critical message to the logs.
     *
     * @param mixed $message
     * @param array $context
     *
     * @return void
     */
    public function critical($message, array $context = [])
    {
        $this->log('critical', $message, $context);
    }

    /**
     * Log an error message to the logs.
     *
     * @param mixed $message
     * @param array $context
     *
     * @return void
     */
    public function error($message, array $context = [])
    {
        $this->log('error', $message, $context);
    }

    /**
     * Log a warning message to the logs.
     *
     * @param mixed $message
     * @param array $context
     *
     * @return void
     */
    public function warning($message, array $context = [])
    {
        $this->log('warning', $message, $context);
    }

    /**
     * Log a notice to the logs.
     *
     * @param mixed $message
     * @param array $context
     *
     * @return void
     */
    public function notice($message, array $context = [])
    {
        $this->log('notice', $message, $context);
    }

    /**
     * Log an informational message to the logs.
     *
     * @param mixed $message
     * @param array $context
     *
     * @return void
     */
    public function info($message, array $context = [])
    {
        $this->log('info', $message, $context);
    }

    /**
     * Log a debug message to the logs.
     *
     * @param mixed $message
     * @param array $context
     *
     * @return void
     */
    public function debug($message, array $context = [])
    {
        $this->log('debug', $message, $context);
    }

    /**
     * Log a message to the logs.
     *
     * @param string $level
     * @param mixed  $message
     * @param array  $context
     *
     * @return void
     */
    public function log($level, $message, array $context = [])
    {
        $severity = $this->getSeverity($level);

        if ($message instanceof Exception) {
            $this->bugsnag->notifyException($message, array_except($context, ['title']), $severity);
        } else {
            $msg = $this->formatMessage($message);
            $title = array_get($context, 'title', str_limit((string) $msg));
            $this->bugsnag->notifyError($title, $msg, array_except($context, ['title']), $severity);
        }
    }

    /**
     * Get the severity for the logger.
     *
     * @param string $level
     *
     * @return string
     */
    protected function getSeverity($level)
    {
        switch ($level) {
            case 'emergency':
            case 'alert':
                return 'fatal';
            case 'critical':
            case 'error':
                return 'error';
            case 'warning':
            case 'notice':
                return 'warning';
            case 'info':
            case 'debug':
                return 'info';
        }
    }

    /**
     * Format the message for the logger.
     *
     * @param mixed $message
     *
     * @return string
     */
    protected function formatMessage($message)
    {
        if (is_array($message)) {
            return var_export($message, true);
        } elseif ($message instanceof Jsonable) {
            return $message->toJson();
        } elseif ($message instanceof Arrayable) {
            return var_export($message->toArray(), true);
        }

        return $message;
    }
}
