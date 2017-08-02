<?php

namespace Servant;

use const Config\{
    OPTION_FLAG_TIMEOUT,
    OUTPUT_FILE_EXTENSION,
    OPTION_FLAG_UID,
    PROCESS_ID_FILE_EXTENSION,
    TEMP_FILE_PREFIX
};
use Exception\{
    NoUidSpecifiedException,
    ProcessIdNotFoundException
};
use Helper\TempFilesManager;

/**
 * Class Thread (static)
 * @author Yann Le Scouarnec <bunkermaster@gmail.com>
 * @package Servant
 */
class Thread
{
    /** @var null|int current script process id */
    private static $processId = null;
    /** @var null|string execution output string */
    private static $output = null;
    /** @var null|string execution output file */
    private static $outputFile = null;
    /** @var null|string process ID file name */
    private static $processIdFile = null;
    /** @var null|string process unique ID */
    private static $uniqueId = null;
    /** @var null|CleanUp end of script management file */
    private static $cleanUpObj = null;
    /** @var null|float timeout micro timestamp */
    private static $timeout = null;

    /**
     * Thread init.
     */
    public static function init()
    {
        ob_start();
        // check call context
        self::check();
        // create process id file
        self::$processIdFile = TEMP_FILE_PREFIX.self::$uniqueId.PROCESS_ID_FILE_EXTENSION;
        new TempFilesManager(self::$processIdFile, self::$processId);
        // construct output file name
        self::$outputFile = TEMP_FILE_PREFIX.self::$uniqueId.OUTPUT_FILE_EXTENSION;
        self::$cleanUpObj = new CleanUp();
    }

    /**
     * @throws NoUidSpecifiedException
     * @throws ProcessIdNotFoundException
     */
    private static function check()
    {
        if (!isset(getopt(OPTION_FLAG_UID.':hp:')[OPTION_FLAG_UID])
            || false === self::$uniqueId = getopt('u:hp:')['u']) {
            throw new NoUidSpecifiedException(__FILE__." was called without a UID in --uid CLI option.");
        }
        if (isset(getopt(OPTION_FLAG_TIMEOUT.':hp:')[OPTION_FLAG_TIMEOUT])
            && self::$timeout = getopt(OPTION_FLAG_TIMEOUT.':hp:')[OPTION_FLAG_TIMEOUT]
        ) {
            throw new NoUidSpecifiedException(__FILE__." was called without a UID in --uid CLI option.");
        }
        if (false === self::$processId = getmypid()) {
            throw new ProcessIdNotFoundException(__FILE__.' was unable to get its process ID.');
        }
    }

    /**
     * @return int|null
     */
    public static function getProcessId() : ?int
    {
        return self::$processId;
    }

    /**
     * @return string|null
     */
    public static function getOutputFile() : ?string
    {
        return self::$outputFile;
    }

    /**
     * @return string|null
     */
    public static function getProcessIdFile() : ?string
    {
        return self::$processIdFile;
    }

    /**
     * @return string|null
     */
    public static function getUniqueId() : ?string
    {
        return self::$uniqueId;
    }

    /**
     * @param null string $output
     */
    public static function setOutput(string $output) : void
    {
        self::$output = $output;
    }

    /**
     * @return string|null
     */
    public static function getOutput() : ?string
    {
        return self::$output;
    }

    /**
     * @return null
     */
    public static function getTimeout()
    {
        return self::$timeout;
    }
}
