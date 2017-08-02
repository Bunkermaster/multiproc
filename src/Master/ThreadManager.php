<?php

namespace Master;

use const Config\{
    DEFAULT_TIME_OUT,
    OPTION_FLAG_TIMEOUT,
    OUTPUT_FILE_EXTENSION,
    PID_FILE_CREATION_TIME_OUT,
    PROCESS_ID_FILE_EXTENSION,
    TEMP_FILE_PREFIX,
    OPTION_FLAG_UID
};
use Exception\ScriptNotFoundException;
use Helper\TempFilesManager;

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
        $this->script = $script;
        if (!file_exists($script)) {
            throw new ScriptNotFoundException('Script requested "'.$script.'"was not found on filesystem');
        }
        $this->timeout = $timeout;
        $this->args = $args;
        $this->uniqueId = uniqid();
        $tempResultFile = new TempFilesManager(TEMP_FILE_PREFIX.$this->uniqueId.OUTPUT_FILE_EXTENSION);
        $this->commFile = $tempResultFile->getFileName();
        $execLimit = $this->startTime + $this->timeout;
        $commandLine = 'php '.$this->script.
            ' -'.OPTION_FLAG_UID.$this->uniqueId.
            ' -'.OPTION_FLAG_TIMEOUT.$execLimit.
            ' > /dev/null 2>/dev/null &';
        exec($commandLine);
        $pidFile = new TempFilesManager(TEMP_FILE_PREFIX.$this->uniqueId.PROCESS_ID_FILE_EXTENSION);
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
        if (file_exists($this->commFile)) {
            $content = file_get_contents($this->commFile);
            // cleanup
            $this->cleanupCommFile();
            return $content;
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
     * kill thread and clean $commFile
     * @return bool
     */
    public function terminate(): bool
    {
        $this->cleanupCommFile();
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
}
