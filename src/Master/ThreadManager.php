<?php

namespace Bunkermaster\Multiproc\Master;

use const Bunkermaster\Multiproc\Config\{
    DEFAULT_TIME_OUT, OPTION_FLAG_TIMEOUT, PID_FILE_CREATION_TIME_OUT, OPTION_FLAG_UID, SESSION_THREAD_LOG
};
use Bunkermaster\Multiproc\Exception\ScriptNotFoundException;
use Bunkermaster\Multiproc\Helper\TempFileNameGenerator;
use Bunkermaster\Multiproc\Helper\TempFilesManager;

/**
 * Class ThreadManager
 * @author Yann Le Scouarnec <bunkermaster@gmail.com>
 * @package Master
 */
class ThreadManager
{
    // set on construct
    private $startTime = null;
    // calculated from $startTime + $timeout
    private $endTime = null;
    // set on construct
    private $script = null;
    // int set on construct
    private $timeout = null;
    // array set on construct
    private $args = null;
    // set on construct
    private $commFile = null;
    // set on construct
    private $processIdFile = null;
    // int process id set on construct
    private $processId = null;
    // thread unique ID
    private $uniqueId = null;
    // thread output
    private $output = null;

    // thread log and list singleton
    private static $threadLog = null;

    /**
     * Thread constructor.
     * @param string $script script to execute in new CLI thread
     * @param int $timeout in seconds
     * @param array $args script arguments
     * @throws ScriptNotFoundException script not found
     * @throws \Exception
     */
    public function __construct(string $script, int $timeout = DEFAULT_TIME_OUT, array $args = [])
    {
        $this->startTime = microtime(true);
        $this->uniqueId = uniqid();
        // add current thread to static thread list
        self::checkLogInstance();
        self::$threadLog->addThreadList($this->uniqueId, $this);
        // log start
        $this->script = $script;
        if (!file_exists($script)) {
            throw new ScriptNotFoundException('Script requested "'.$script.'"was not found on filesystem');
        }
        $this->timeout = $timeout;
        $this->args = $args;
        // temporary file names generation
        $tempResultFile = new TempFilesManager(TempFileNameGenerator::getResultFileName($this->uniqueId));
        self::log($this->uniqueId, "Temporary result file is : ".$tempResultFile->getFileName());
        $this->commFile = $tempResultFile->getFileName();
        $execLimit = $this->startTime + $this->timeout;
        $commandLine = 'php '.$this->script.
            ' -'.OPTION_FLAG_UID.$this->uniqueId.
            ' -'.OPTION_FLAG_TIMEOUT.$execLimit.
            ' > /dev/null 2>/dev/null &';
        self::log($this->uniqueId, "Command : ".$commandLine);
        exec($commandLine);
        // get pid file reference
        $pidFile = new TempFilesManager(TempFileNameGenerator::getPidFileName($this->uniqueId));
        self::log($this->uniqueId, "Temporary process file : ".$pidFile->getFileName());
        $this->processIdFile = $pidFile->getFileName();
        // flag for absence of pid file
        $noPidFile = true;
        // check the pid file presence for a full second then proceed onto better things
        while (microtime(true) < $this->startTime + PID_FILE_CREATION_TIME_OUT) {
            usleep(1000);
            if (file_exists($this->processIdFile)) {
                $this->processId = file_get_contents($pidFile->getFileName());
                self::log($this->uniqueId, "PID file found.");
                $noPidFile = false;
                break;
            }
        }
        if ($noPidFile) {
            self::log($this->uniqueId, "No PID file found after initial wait time.");
            // @todo Exception if process id file is not present
            throw new \Exception('process id file is not present ('.$this->processIdFile.')');
        }
    }

    /**
     *  retourne null si script pas fini, une chaine avec le résultat du traitement dans tout autre cas
     * @return null|string
     * @throws \Exception
     */
    public function result(): ?string
    {
        if (!is_null($this->output)) {
            return $this->output;
        }
        if (file_exists($this->commFile)) {
            $this->output = file_get_contents($this->commFile);
            // @fixme y'a de la merde ici!
            // cleanup
            $this->cleanupCommFile();
            $this->cleanupPidFile();
            // log results
            self::log($this->uniqueId, "Result : ".PHP_EOL.$this->output);
            $this->endTime = microtime(true);
            return $this->output;
        }
        return null;
    }

    /**
     * @throws \Exception
     */
    public function cleanupCommFile() : void
    {
        if (false === unlink($this->commFile)) {
            // @todo Exception if the results file could not be deleted
            throw new \Exception('The results file could not be deleted');
        }
    }

    /**
     * Delete the pid file when terminating the script execution
     * @throws \Exception
     */
    public function cleanupPidFile() : void
    {
        if (false === unlink($this->processIdFile)) {
            // @todo Exception if the process ID file could not be deleted
            throw new \Exception('the process ID file could not be deleted');
        }
    }

    /**
     * kill thread and clean $commFile
     * @return bool
     */
    public function terminate(): bool
    {
        $this->cleanupCommFile();
        $this->cleanupPidFile();
        $this->endTime = microtime(true);
        return posix_kill($this->processId, SIGINT);
    }

    /**
     * @return bool|null|string
     */
    public function getProcessId()
    {
        return $this->processId;
    }

    /**
     * @return null|string
     */
    public function getUniqueId()
    {
        return $this->uniqueId;
    }

    public function __destruct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE && $_SESSION['']) {
            // remove current thread from static thread list
            self::log($this->uniqueId, "Killed");
            self::$threadLog->removeThread($this->uniqueId);
        }
    }

    /**
     * adds events to thread log
     * @param string $uniqueId
     * @param string $message
     * @return void
     */
    public static function log(string $uniqueId, string $message) : void
    {
        self::checkLogInstance();
        if (self::$threadLog->isThreadLogEnabled()) {
            list($microtime, $timestamp) = explode(' ', microtime());
            $timestamp = $timestamp . str_pad(substr($microtime, 2), 6, '0', STR_PAD_LEFT);
            self::$threadLog
                ->addThreadLog($uniqueId, $timestamp, $message)
                ->addThreadLogChrono($uniqueId, $timestamp, $message)
            ;
        }
    }

    /**
     * toggle the log activity
     * @param bool $flag
     */
    public static function toggleThreadLog(bool $flag)
    {
        self::checkLogInstance();
        self::$threadLog->setThreadLogEnabled($flag);
    }

    /**
     * stores the ThreadLog instance in self::threadLog
     */
    public static function checkLogInstance() : void
    {
        if (is_null(self::$threadLog)) {
            if (isset($_SESSION[SESSION_THREAD_LOG])) {
                self::$threadLog = unserialize($_SESSION[SESSION_THREAD_LOG]);
            } else {
                self::$threadLog = ThreadLog::getInstance();
            }
        }
    }

    /**
     * output the log display header
     */
    public static function logHeader()
    {
        echo PHP_EOL;
        echo "#################################################################".PHP_EOL;
        echo "THREADS LOG".PHP_EOL;
        echo "#################################################################".PHP_EOL;
        if (!self::$threadLog->isThreadLogEnabled()) {
            echo "Logs are disabled".PHP_EOL;
        }
    }

    /**
     * ouputs threads log
     */
    public static function showAllLogs()
    {
        self::checkLogInstance();
        self::logHeader();
        foreach (self::$threadLog->getThreadLog() as $threadId => $thread) {
            self::showLog($threadId);
        }
    }

    /**
     * ouputs threads log by chronological order
     */
    public static function showAllLogsChrono() : void
    {
        self::logHeader();
        foreach (self::$threadLog->getThreadLogChrono() as $timestamp => $message) {
            echo $timestamp." : ".$message.PHP_EOL;
        }
    }

    /**
     * @param string $threadId
     * @throws \Exception
     */
    public static function showLog(string $threadId) : void
    {
        echo "THREAD ID : ".$threadId.PHP_EOL;
        if (is_null(self::$threadLog->getThreadLog($threadId))) {
            // @todo manage Exception for thread not found in thread list
            throw new \Exception("Thread $threadId not found");
        }
        foreach (self::$threadLog->getThreadLog($threadId) as $timestamp => $entry) {
            echo $threadId." : ".$timestamp." : ".$entry.PHP_EOL;
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function __sleep()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION[SESSION_THREAD_LOG] = serialize(self::$threadLog);
        } else {
            // @todo create more explicit Exception for 'Session not active, cannot wake up the Thread logger'
            throw new \Exception('Session not active, cannot put the Thread logger to sleep');
        }
        return [
            'startTime',
            'endTime',
            'script',
            'timeout',
            'args',
            'commFile',
            'processIdFile',
            'processId',
            'uniqueId',
            'output',
        ];
    }

    /**
     * @throws \Exception
     */
    public function __wakeup()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            if (is_null(self::$threadLog)) {
//                self::$threadLog = unserialize($_SESSION['bunkermaster_multiproc_threadLog']);
            }
        } else {
            // @todo create more explicit Exception for 'Session not active, cannot wake up the Thread logger'
            throw new \Exception('Session not active, cannot wake up the Thread logger');
        }
    }

    /**
     * @return ThreadLog
     */
    public static function getThreadLog() : ThreadLog
    {
        return self::$threadLog;
    }
}
