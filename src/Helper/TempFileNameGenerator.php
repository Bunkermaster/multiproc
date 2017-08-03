<?php

namespace Bunkermaster\Multiproc\Helper;

use const Bunkermaster\Multiproc\Config\{
    OUTPUT_FILE_EXTENSION,
    PROCESS_ID_FILE_EXTENSION,
    TEMP_FILE_PREFIX
};

/**
 * Class TempFileNameGenerator
 * @author Yann Le Scouarnec <bunkermaster@gmail.com>
 * @package Bunkermaster\Multiproc\Helper
 */
class TempFileNameGenerator
{
    /**
     * returns the pid file name
     * @param string $uniqueId
     * @return string pid file name
     */
    public static function getPidFileName(string $uniqueId) : string
    {
        return TEMP_FILE_PREFIX.$uniqueId.PROCESS_ID_FILE_EXTENSION;
    }

    /**
     * returns the result file name
     * @param string $uniqueId
     * @return string result file name
     */
    public static function getResultFileName(string $uniqueId) : string
    {
        return TEMP_FILE_PREFIX.$uniqueId.OUTPUT_FILE_EXTENSION;
    }
}
