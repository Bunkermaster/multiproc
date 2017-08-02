<?php

namespace Servant;

use Exception\NoUidSpecifiedException;
use Exception\ProcessIdNotFoundException;

/**
 * Class Thread (static)
 * @author Yann Le Scouarnec <bunkermaster@gmail.com>
 * @package Servant
 */
class Thread
{
    private static $pid = null;
    private static $outputFile = null;
    private static $pidFile = null;
    private static $uniqueId = null;

    /**
     * Thread init.
     */
    public static function init()
    {
        self::check();
    }

    /**
     * @throws NoUidSpecifiedException
     * @throws ProcessIdNotFoundException
     */
    private static function check()
    {
        if (!isset(getopt('u:hp:')['u']) || false === self::$uniqueId = getopt('u:hp:')) {
            throw new NoUidSpecifiedException(__FILE__." was called without a UID in --uid CLI option.");
        }
        if (false === self::$pid = getmypid()) {
            throw new ProcessIdNotFoundException(__FILE__.' was unable to get its process ID.');
        }
    }

    /**
     * @return null
     */
    public static function getPid()
    {
        return self::$pid;
    }

    /**
     * @return null
     */
    public static function getOutputFile()
    {
        return self::$outputFile;
    }

    /**
     * @return null
     */
    public static function getPidFile()
    {
        return self::$pidFile;
    }

    /**
     * @return null
     */
    public static function getUniqueId()
    {
        return self::$uniqueId;
    }

}