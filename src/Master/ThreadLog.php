<?php

namespace Bunkermaster\Multiproc\Master;

/**
 * Class ThreadLog
 * @author Yann Le Scouarnec <bunkermaster@gmail.com>
 * @package Bunkermaster\Multiproc\Master
 */
class ThreadLog
{
    private $threadList = [];
    private $threadLog = [];
    private $threadLogChrono = [];
    private $threadLogEnabled = false;

    private static $me = null;

    /**
     * ThreadLog constructor.
     */
    private function __construct()
    {
        if (is_null(self::$me)) {
            self::$me = $this;
        }
    }

    /**
     * Instanciation method for singleton
     * @return ThreadLog
     */
    public static function getInstance()
    {
        if (is_null(self::$me)) {
            self::$me = new self();
        }
        return self::$me;
    }

    /**
     * @param string|null $uniqueId
     * @return array|ThreadManager
     */
    public function getThreadList(string $uniqueId = null)
    {
        if (isset($this->threadList[$uniqueId])) {

            return $this->threadList[$uniqueId];
        } else {

            return $this->threadList;
        }
    }

    /**
     * @param string $threadId
     * @param ThreadManager $threadList
     * @return ThreadLog
     */
    public function addThreadList(string $threadId, ThreadManager $threadList) : self
    {
        $this->threadList[$threadId] = $threadList;

        return $this;
    }

    /**
     * @param string|null $uniqueId
     * @param int|null $timestamp
     * @return array
     */
    public function getThreadLog(string $uniqueId = null, int $timestamp = null): ?array
    {
        if (is_null($uniqueId)) {

            return $this->threadLog;
        }
        // return log elements designated by uniqueId
        if (is_null($timestamp)) {
            if (isset($this->threadLog[$uniqueId])) {

                return $this->threadLog[$uniqueId];
            } else {

                return null;
            }
        }
        // return log element designated by uniqueId and timestamp if exists
        if (isset($this->threadLog[$uniqueId][$timestamp])) {

            return $this->threadLog[$uniqueId][$timestamp];
        } else {

            return null;
        }
    }

    /**
     * @param string $uniqueId
     * @param int $timestamp
     * @param string $message
     * @return ThreadLog
     */
    public function addThreadLog(string $uniqueId, int $timestamp, string $message) : self
    {
        $this->threadLog[$uniqueId][$timestamp] = $message;

        return $this;
    }

    /**
     * @return array
     */
    public function getThreadLogChrono(): array
    {

        return $this->threadLogChrono;
    }

    /**
     * @param string $uniqueId
     * @param int $timestamp
     * @param string $message
     * @return ThreadLog
     */
    public function addThreadLogChrono(string $uniqueId, int $timestamp, string $message) : self
    {
        $this->threadLogChrono[$timestamp][$uniqueId] = $message;

        return $this;
    }

    /**
     * @return bool
     */
    public function isThreadLogEnabled(): bool
    {

        return $this->threadLogEnabled;
    }

    /**
     * @param bool $threadLogEnabled
     * @return $this
     */
    public function setThreadLogEnabled($threadLogEnabled) : self
    {
        $this->threadLogEnabled = $threadLogEnabled;

        return $this;
    }

    /**
     * @param string $uniqueId
     * @return null|bool
     */
    public function removeThread(string $uniqueId) : ?bool
    {
        if (isset($this->threadList[$uniqueId])) {
            unset($this->threadList[$uniqueId]);

            return true;
        } else {

            return null;
        }
    }
}
