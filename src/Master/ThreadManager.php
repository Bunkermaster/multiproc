<?php

namespace Bunkermaster\Multiproc\Master;

use const Bunkermaster\Multiproc\Config\{
    DEFAULT_TIME_OUT,
    OPTION_FLAG_TIMEOUT,
    OUTPUT_FILE_EXTENSION,
    PID_FILE_CREATION_TIME_OUT,
    PROCESS_ID_FILE_EXTENSION,
    TEMP_FILE_PREFIX,
    OPTION_FLAG_UID
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
    private $startTime = null; // set on construct
    private $endTime = null; // calculated from $startTime + $timeout
    private $script = null; // set on construct
    private $timeout = null; // int set on construct
    private $args = null; // array set on construct
    private $commFile = null; // set on construct
    private $processIdFile = null; // set on construct
    private $processId = null; // int process id set on construct
    private $uniqueId = null; // thread unique ID
    private $output = null; // thread output

    private static $threadList = [];
    private static $threadLog = [];
    private static $threadLogEnabled = false;

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
        self::$threadList[$this->uniqueId] = $this;
        // log start
        $this->script = $script;
        if (!file_exists($script)) {
            throw new ScriptNotFoundException('Script requested "'.$script.'"was not found on filesystem');
        }
        $this->timeout = $timeout;
        $this->args = $args;
        // temporary file names generation
        $tempResultFile = new TempFilesManager(TEMP_FILE_PREFIX.$this->uniqueId.OUTPUT_FILE_EXTENSION);
        $this->commFile = $tempResultFile->getFileName();
        $execLimit = $this->startTime + $this->timeout;
        $commandLine = 'php '.$this->script.
            ' -'.OPTION_FLAG_UID.$this->uniqueId.
            ' -'.OPTION_FLAG_TIMEOUT.$execLimit.
            ' > /dev/null 2>/dev/null &';
        exec($commandLine);
        $pidFile = new TempFilesManager(TempFileNameGenerator::getPidFileName($this->uniqueId));
        $this->processIdFile = $pidFile->getFileName();
        // flag for absence of pid file
        $noPidFile = true;
        // check the pid file presence for a full second then proceed onto better things
        while (microtime(true) < $this->startTime + PID_FILE_CREATION_TIME_OUT) {
            usleep(1000);
            if (file_exists($this->processIdFile)) {
                $this->processId = file_get_contents($pidFile->getFileName());
                $noPidFile = false;
                break;
            }
        }
        if ($noPidFile) {
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
            return $this->output;
        }
        $this->endTime = microtime(true);
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
        // remove current thread from static thread list
        unset(self::$threadList[$this->uniqueId]);
    }

    /**
     * adds events to thread log
     * @param string $uniqueId
     * @param string $message
     * @return void
     */
    public static function log(string $uniqueId, string $message) : void
    {
        if (self::$threadLogEnabled) {
            self::$threadLog[$uniqueId][microtime(true)] = $message;
        }
    }

    /**
     * toggle the log activity
     * @param bool $flag
     */
    public static function toggleThreadLog(bool $flag)
    {
        self::$threadLogEnabled = flag;
    }
}
