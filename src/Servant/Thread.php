<?php

namespace Bunkermaster\Multiproc\Servant;

use const Bunkermaster\Multiproc\Config\DEFAULT_TIME_OUT;
use const Bunkermaster\Multiproc\Config\OPTION_FLAG_TIMEOUT;
use const Bunkermaster\Multiproc\Config\OUTPUT_FILE_EXTENSION;
use const Bunkermaster\Multiproc\Config\OPTION_FLAG_UID;
use const Bunkermaster\Multiproc\Config\PROCESS_ID_FILE_EXTENSION;
use const Bunkermaster\Multiproc\Config\TEMP_FILE_PREFIX;
use Bunkermaster\Multiproc\Exception\NoUidSpecifiedException;
use Bunkermaster\Multiproc\Exception\ProcessIdNotFoundException;
use Bunkermaster\Multiproc\Helper\TempFileNameGenerator;
use Bunkermaster\Multiproc\Helper\TempFilesManager;

/**
 * Class Thread (static)
 * @author Yann Le Scouarnec <bunkermaster@gmail.com>
 * @package Servant
 */
class Thread
{
    /** @var null|int $processId current script process id */
    private static $processId = null;
    /** @var null|string $output execution output string */
    private static $output = null;
    /** @var null|string $outputFile execution output file */
    private static $outputFile = null;
    /** @var null|string $processIdFile process ID file name */
    private static $processIdFile = null;
    /** @var null|string $uniqueId process unique ID */
    private static $uniqueId = null;
    /** @var null|CleanUp $cleanUpObj end of script management file */
    private static $cleanUpObj = null;
    /** @var null|float $timeout timeout micro timestamp */
    private static $timeout = null;
    /** @var null|array $arguments thread arguments */
    private static $arguments = null;

    /**
     * Thread init.
     * @param array $argv
     * @throws NoUidSpecifiedException
     * @throws ProcessIdNotFoundException
     */
    public static function init($argv = []): void
    {
        // output buffer capture for output file
        ob_start();
        // check call context
        self::check();
        // create process id file
        self::$processIdFile = TempFileNameGenerator::getPidFileName(self::$uniqueId);
        new TempFilesManager(self::$processIdFile, self::$processId);
        // construct output file name
        self::$outputFile = TempFileNameGenerator::getResultFileName(self::$uniqueId);
        self::$cleanUpObj = new CleanUp();
        self::$arguments = array_slice($argv, 3);
    }

    /**
     * @throws NoUidSpecifiedException
     * @throws ProcessIdNotFoundException
     */
    private static function check(): void
    {
        if (!isset(getopt(OPTION_FLAG_UID.':hp:')[OPTION_FLAG_UID])
            || false === self::$uniqueId = getopt('u:hp:')[OPTION_FLAG_UID]) {
            throw new NoUidSpecifiedException(__FILE__." was called without a UID in --uid CLI option.");
        }
        if (isset(getopt(OPTION_FLAG_TIMEOUT.':hp:')[OPTION_FLAG_TIMEOUT])) {
            self::$timeout = getopt(OPTION_FLAG_TIMEOUT.':hp:')[OPTION_FLAG_TIMEOUT];
        } else {
            self::$timeout = microtime(true) + (float) DEFAULT_TIME_OUT;
        }
        if (false === self::$processId = getmypid()) {
            throw new ProcessIdNotFoundException(__FILE__.' was unable to get its process ID.');
        }
    }

    /**
     * kills current script if timeout
     * @return void
     */
    public static function checkTimeout(): void
    {
        if (microtime(true) > self::getTimeout()) {
            self::$output = json_encode(["An error occured, timeout was reached",
                microtime(true) - self::getTimeout(),
                ob_get_clean()
                ]);
            exit;
        }
    }

    /**
     * @return int|null
     */
    public static function getProcessId(): ?int
    {
        return self::$processId;
    }

    /**
     * @return string|null
     */
    public static function getOutputFile(): ?string
    {
        return self::$outputFile;
    }

    /**
     * @return string|null
     */
    public static function getProcessIdFile(): ?string
    {
        return self::$processIdFile;
    }

    /**
     * @return string|null
     */
    public static function getUniqueId(): ?string
    {
        return self::$uniqueId;
    }

    /**
     * @param null|string $output
     */
    public static function setOutput(string $output): void
    {
        self::$output = $output;
    }

    /**
     * @return string|null
     */
    public static function getOutput(): ?string
    {
        return self::$output;
    }

    /**
     * @return float|null
     */
    public static function getTimeout(): ?float
    {
        return self::$timeout;
    }

    /**
     * @param int $index
     * @return string|null argument value
     */
    public static function getArgument(int $index): ?string
    {
        if (!isset(self::$arguments[$index])) {
            return null;
        }

        return self::$arguments[$index];
    }

    /**
     * @return array|null
     */
    public static function getArguments(): ?array
    {
        return self::$arguments;
    }


}
